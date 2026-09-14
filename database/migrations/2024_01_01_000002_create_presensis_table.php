<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Karyawan
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->string('nik', 20);
            $table->string('nama', 100);

            // Waktu dan Lokasi
            $table->timestamp('waktu_datang')->nullable();
            $table->timestamp('waktu_pulang')->nullable();
            
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Status Presensi
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha'])->default('alpha');
            $table->string('keterangan')->nullable();
            
            // Bukti Izin/Sakit/Foto Face ID
            $table->string('bukti')->nullable()->comment('Path file bukti/surat/snapshot');

            // Izin/Sakit Approval
            $table->enum('status_izin', ['pending', 'approved', 'rejected'])->nullable();
            $table->timestamp('izin_approved_at')->nullable();
            $table->foreignId('izin_approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('izin_reject_reason')->nullable();

            // Face Recognition Metrik
            $table->decimal('face_confidence', 5, 2)->nullable()->comment('Tingkat kecocokan wajah (0-100%)');
            $table->decimal('distance_meter', 8, 2)->nullable()->comment('Jarak presensi dari pusat absensi (meter)');

            $table->timestamps();

            // Index
            $table->index(['karyawan_id', 'waktu_datang']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
