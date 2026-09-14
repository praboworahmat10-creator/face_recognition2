<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();

            // Identitas utama
            $table->string('nik', 20)->unique()->comment('Nomor Induk Karyawan');
            $table->string('nama', 100);
            $table->string('email', 100)->nullable()->unique();
            $table->string('password');
            $table->rememberToken();
            
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();

            // Karyawan Info
            $table->string('divisi', 100);
            $table->string('jabatan', 100);
            $table->enum('status_karyawan', ['aktif', 'cuti', 'resign', 'suspend'])->default('aktif');

            // Foto & Face Recognition
            $table->string('foto')->nullable()->comment('Path foto profil');
            $table->longText('face_descriptors')->nullable()->comment('JSON array of face descriptor vectors 128-dim');
            $table->text('face_photos')->nullable()->comment('JSON array of face photo paths');
            $table->timestamp('face_enrolled_at')->nullable();
            $table->tinyInteger('face_sample_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['divisi', 'jabatan']);
            $table->index('status_karyawan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
