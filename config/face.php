<?php

return [
    /** Batas jarak Euclidean maksimum (0–1, semakin kecil semakin ketat) */
    'match_threshold' => 0.55,

    /** Selisih minimum jarak best vs second-best (cegah false match) */
    'match_margin' => 0.08,

    /** Skor deteksi wajah minimum (%) sebelum matching */
    'detection_min_confidence' => 60,
];
