<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Karyawan;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin User ──
        User::create([
            'name'     => 'Admin Koperasi',
            'email'    => 'admin@koperasipos.id',
            'password' => Hash::make('admin123'),
            'role'     => 'admin',
        ]);

        // ── Sample Karyawan ──
        $karyawanData = [
            [
                'nik'     => '2024001',
                'nama'    => 'Budi Santoso',
                'email'   => 'budi@koperasipos.id',
                'divisi'  => 'Operasional',
                'jabatan' => 'Staff',
            ],
            [
                'nik'     => '2024002',
                'nama'    => 'Siti Rahayu',
                'email'   => 'siti@koperasipos.id',
                'divisi'  => 'Keuangan',
                'jabatan' => 'Bendahara',
            ],
            [
                'nik'     => '2024003',
                'nama'    => 'Ahmad Fauzi',
                'email'   => 'ahmad@koperasipos.id',
                'divisi'  => 'IT',
                'jabatan' => 'Programmer',
            ],
        ];

        foreach ($karyawanData as $k) {
            // User login
            User::create([
                'name'     => $k['nama'],
                'email'    => $k['email'],
                'password' => Hash::make('karya123'),
                'role'     => 'karyawan',
            ]);

            // Data karyawan
            Karyawan::create([
                'nik'      => $k['nik'],
                'nama'     => $k['nama'],
                'email'    => $k['email'],
                'password' => Hash::make('karya123'),
                'divisi'   => $k['divisi'],
                'jabatan'  => $k['jabatan'],
                'status_karyawan' => 'aktif',
            ]);
        }
    }
}