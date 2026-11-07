<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slip_gajis', function (Blueprint $table) {
            $table->id();

            // Relasi ke Karyawan
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');

            // Periode penggajian
            $table->tinyInteger('bulan')->unsigned()->comment('1-12');
            $table->smallInteger('tahun')->unsigned();

            // Komponen Pendapatan
            $table->decimal('gaji_pokok', 15, 2)->default(0);
            $table->decimal('tunjangan_jabatan', 15, 2)->default(0);
            $table->decimal('tunjangan_kehadiran', 15, 2)->default(0);
            $table->decimal('tunjangan_transport_makan', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);

            // Komponen Potongan
            $table->decimal('potongan_bpjs', 15, 2)->default(0);
            $table->decimal('potongan_terlambat', 15, 2)->default(0);
            $table->decimal('potongan_lainnya', 15, 2)->default(0);

            // Rekap kehadiran pada periode tersebut
            $table->smallInteger('total_hadir')->unsigned()->default(0);
            $table->smallInteger('total_izin')->unsigned()->default(0);
            $table->smallInteger('total_sakit')->unsigned()->default(0);
            $table->smallInteger('total_cuti')->unsigned()->default(0);
            $table->smallInteger('total_alpa')->unsigned()->default(0);

            // Akumulasi
            $table->decimal('total_pendapatan', 15, 2)->default(0);
            $table->decimal('total_potongan', 15, 2)->default(0);
            $table->decimal('gaji_bersih', 15, 2)->default(0);

            // Status publikasi & catatan
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->text('catatan')->nullable();

            $table->timestamps();

            // Index
            $table->unique(['karyawan_id', 'bulan', 'tahun'], 'slip_gajis_periode_unique');
            $table->index(['tahun', 'bulan']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slip_gajis');
    }
};
