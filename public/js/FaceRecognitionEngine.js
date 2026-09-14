/**
 * FaceRecognitionEngine
 * Ekstraksi fitur CNN, matching multi-descriptor (rata-rata 5 sampel),
 * dan liveness detection.
 */
class FaceRecognitionEngine {
    constructor(modelPath = '/models', options = {}) {
        this.modelPath = modelPath;
        this.isReady = false;
        this.registeredFaces = [];
        this.matchThreshold = options.matchThreshold ?? 0.55;
        this.matchMargin = options.matchMargin ?? 0.08;
        this.debugEuclidean = options.debugEuclidean ?? false;
    }

    async initialize() {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(this.modelPath),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(this.modelPath),
            faceapi.nets.faceRecognitionNet.loadFromUri(this.modelPath)
        ]);
        this.isReady = true;
    }

    /**
     * Normalisasi daftar descriptor dari API (1 atau 5 sampel)
     */
    _parseDescriptorList(data) {
        const raw = data.descriptors;
        if (!raw || !Array.isArray(raw)) return [];

        if (Array.isArray(raw[0])) {
            return raw.map(d => new Float32Array(d));
        }
        if (typeof raw[0] === 'number') {
            return [new Float32Array(raw)];
        }
        return [];
    }

    /**
     * Rata-rata vektor (centroid) dari N descriptor enroll
     */
    averageDescriptors(descriptors) {
        if (!descriptors.length) return null;

        const len = descriptors[0].length;
        const avg = new Float32Array(len);

        for (const desc of descriptors) {
            for (let i = 0; i < len; i++) avg[i] += desc[i];
        }
        for (let i = 0; i < len; i++) avg[i] /= descriptors.length;

        return avg;
    }

    /**
     * @param {Array} databaseData Data dari server
     */
    setRegisteredFaces(databaseData) {
        this.registeredFaces = databaseData.map(data => {
            const descriptors = this._parseDescriptorList(data);
            const centroid = this.averageDescriptors(descriptors);
            return {
                id: data.nik,
                descriptors,
                centroid,
                sampleCount: descriptors.length
            };
        });
    }

    async scanFace(videoElement) {
        if (!this.isReady) throw new Error('Engine belum siap');
        const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 416, scoreThreshold: 0.6 });
        return await faceapi.detectAllFaces(videoElement, opts).withFaceLandmarks(true).withFaceDescriptors();
    }

    /**
     * Jarak minimum ke wajah terdaftar: bandingkan live vs setiap sampel + centroid
     */
    distanceToRegisteredFace(liveDescriptor, face) {
        let minDist = Infinity;

        for (const desc of face.descriptors) {
            minDist = Math.min(minDist, this.euclideanDistance(liveDescriptor, desc, { log: false }));
        }

        if (face.centroid) {
            minDist = Math.min(minDist, this.euclideanDistance(liveDescriptor, face.centroid, { log: false }));
        }

        return minDist;
    }

    euclideanDistance(vecA, vecB, options = {}) {
        const shouldLog = options.log ?? this.debugEuclidean;

        if (vecA.length !== vecB.length) {
            console.error('Dimensi vektor tidak sama!');
            return Infinity;
        }

        let sum = 0;
        for (let i = 0; i < vecA.length; i++) {
            const diff = vecA[i] - vecB[i];
            sum += diff * diff;
        }

        const finalDistance = Math.sqrt(sum);

        if (shouldLog) {
            const terminalLogs = [
                '=== EUCLIDEAN DISTANCE ===',
                `[HASIL] Distance: ${finalDistance.toFixed(4)}`,
                '=========================='
            ].join('\n');
            console.log(terminalLogs);

            try {
                fetch('/presensi/log-euclidean', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ log_text: terminalLogs })
                }).catch(() => {});
            } catch (e) {}
        }

        return finalDistance;
    }

    /**
     * Matching pintar: multi-descriptor + margin antar kandidat
     * @returns {{ id, distance, margin, passed, secondBestDistance, sampleCount }}
     */
    findBestMatch(liveDescriptor, threshold = null, minMargin = null) {
        const maxDist = threshold ?? this.matchThreshold;
        const requiredMargin = minMargin ?? this.matchMargin;

        const ranked = this.registeredFaces
            .map(face => ({
                id: face.id,
                distance: this.distanceToRegisteredFace(liveDescriptor, face),
                sampleCount: face.sampleCount
            }))
            .sort((a, b) => a.distance - b.distance);

        if (!ranked.length) {
            return { id: 'unknown', distance: Infinity, margin: 0, passed: false, secondBestDistance: null, sampleCount: 0 };
        }

        const best = ranked[0];
        const second = ranked[1] ?? null;
        const margin = second ? second.distance - best.distance : Infinity;

        const withinThreshold = best.distance <= maxDist;
        const clearWinner = margin >= requiredMargin;
        const passed = withinThreshold && clearWinner;

        if (passed && this.debugEuclidean) {
            console.log(`[MATCH] NIK=${best.id} dist=${best.distance.toFixed(4)} margin=${margin.toFixed(4)} samples=${best.sampleCount}`);
        }

        return {
            id: passed ? best.id : 'unknown',
            distance: best.distance,
            margin,
            passed,
            secondBestDistance: second?.distance ?? null,
            sampleCount: best.sampleCount
        };
    }

    calculateAccuracy(distance, threshold = null) {
        const maxDist = threshold ?? this.matchThreshold;
        if (distance >= maxDist) return Math.max(0, Math.round((1 - distance) * 100));
        // Skala non-linear: jarak di bawah threshold dapat skor lebih tinggi
        const normalized = 1 - (distance / maxDist);
        return Math.min(100, Math.round(50 + normalized * 50));
    }

    // ═══════════════════════════════════════════════
    // LIVENESS DETECTION
    // ═══════════════════════════════════════════════

    initLiveness() {
        this.liveness = {
            landmarkHistory: [],
            earHistory: [],
            blinkCount: 0,
            blinkState: 'open',
            startTime: Date.now(),
            frameCount: 0,
            passed: false,
            score: 0,
            details: { movement: 0, blink: false, texture: 0 }
        };
    }

    calculateEAR(eyePoints) {
        const v1 = Math.hypot(eyePoints[1].x - eyePoints[5].x, eyePoints[1].y - eyePoints[5].y);
        const v2 = Math.hypot(eyePoints[2].x - eyePoints[4].x, eyePoints[2].y - eyePoints[4].y);
        const h = Math.hypot(eyePoints[0].x - eyePoints[3].x, eyePoints[0].y - eyePoints[3].y);
        if (h === 0) return 0.3;
        return (v1 + v2) / (2.0 * h);
    }

    detectBlink(landmarks) {
        if (!this.liveness) this.initLiveness();

        const leftEye = landmarks.slice(36, 42);
        const rightEye = landmarks.slice(42, 48);
        const ear = (this.calculateEAR(leftEye) + this.calculateEAR(rightEye)) / 2.0;

        this.liveness.earHistory.push(ear);
        if (this.liveness.earHistory.length > 30) this.liveness.earHistory.shift();

        const EAR_THRESHOLD = 0.26;
        let blinked = false;

        if (this.liveness.blinkState === 'open' && ear < EAR_THRESHOLD) {
            this.liveness.blinkState = 'closed';
        } else if (this.liveness.blinkState === 'closed' && ear >= EAR_THRESHOLD) {
            this.liveness.blinkState = 'open';
            this.liveness.blinkCount++;
            blinked = true;
        }

        return { ear, blinked, blinkCount: this.liveness.blinkCount };
    }

    checkMicroMovement(landmarks) {
        if (!this.liveness) this.initLiveness();

        const nose = landmarks[30];
        this.liveness.landmarkHistory.push({ x: nose.x, y: nose.y });

        if (this.liveness.landmarkHistory.length < 8) {
            return { stdDev: 0, passed: false, samples: this.liveness.landmarkHistory.length };
        }

        if (this.liveness.landmarkHistory.length > 20) this.liveness.landmarkHistory.shift();

        const history = this.liveness.landmarkHistory;
        const meanX = history.reduce((s, p) => s + p.x, 0) / history.length;
        const meanY = history.reduce((s, p) => s + p.y, 0) / history.length;
        const variance = history.reduce((s, p) => s + Math.pow(p.x - meanX, 2) + Math.pow(p.y - meanY, 2), 0) / history.length;
        const stdDev = Math.sqrt(variance);
        const MOVEMENT_THRESHOLD = 0.4;

        return { stdDev, passed: stdDev > MOVEMENT_THRESHOLD, samples: history.length };
    }

    analyzeTexture(video, box) {
        try {
            const canvas = document.createElement('canvas');
            const size = 64;
            canvas.width = size;
            canvas.height = size;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, box.x, box.y, box.width, box.height, 0, 0, size, size);

            const data = ctx.getImageData(0, 0, size, size).data;
            const grayValues = [];
            for (let i = 0; i < data.length; i += 4) {
                grayValues.push(0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]);
            }

            const mean = grayValues.reduce((s, v) => s + v, 0) / grayValues.length;
            const variance = grayValues.reduce((s, v) => s + Math.pow(v - mean, 2), 0) / grayValues.length;

            return { variance: Math.round(variance), passed: variance > 900 };
        } catch (e) {
            return { variance: 0, passed: true };
        }
    }

    checkLiveness(landmarks, video, faceBox) {
        if (!this.liveness) this.initLiveness();
        this.liveness.frameCount++;

        const movement = this.checkMicroMovement(landmarks);
        const blink = this.detectBlink(landmarks);

        let texture = this.liveness.details.textureResult || { variance: 0, passed: true };
        if (this.liveness.frameCount % 5 === 0 && faceBox) {
            texture = this.analyzeTexture(video, faceBox);
            this.liveness.details.textureResult = texture;
        }

        let score = 0;
        if (movement.passed) score += 40;
        if (blink.blinkCount >= 1) score += 40;
        if (texture.passed) score += 20;
        if (!movement.passed && movement.stdDev > 0.3) {
            score += Math.min(20, Math.round(movement.stdDev * 15));
        }

        const passed = score >= 60;
        let message = '';
        if (passed) message = '✓ Wajah asli terverifikasi';
        else if (movement.samples < 8) message = 'Menganalisis liveness... diam sejenak';
        else if (score < 60) message = 'Mohon Berkedip atau Gerakkan Kepala Sedikit';

        this.liveness.score = score;
        this.liveness.details = {
            movement: movement.stdDev,
            movementPassed: movement.passed,
            blink: blink.blinkCount >= 1,
            blinkCount: blink.blinkCount,
            ear: blink.ear,
            texture: texture.variance,
            texturePassed: texture.passed,
            textureResult: texture
        };

        if (passed && !this.liveness.passed) {
            this.liveness.passed = true;
        }

        return { passed, score, message, details: this.liveness.details, elapsed: Date.now() - this.liveness.startTime };
    }

    resetLiveness() {
        this.liveness = null;
    }
}
