<?php

return [
    // Nominal usulan otomatis saat admin membuat slip gaji.
    // Dipakai sebagai saran di form, admin tetap bisa mengubah nilainya.
    'uang_kehadiran_per_hari'   => env('GAJI_UANG_KEHADIRAN', 25000),
    'transport_makan_per_hari'  => env('GAJI_TRANSPORT_MAKAN', 30000),
    'potongan_alpa_per_hari'    => env('GAJI_POTONGAN_ALPA', 100000),

    // Identitas perusahaan pada slip gaji cetak.
    'perusahaan' => [
        'nama'    => 'KOPPOSINDO',
        'subnama' => 'Koperasi Pegawai Pos Indonesia',
        'alamat'  => 'Jl. Banda No. 30, Bandung 40115, Jawa Barat',
        'telepon' => '(022) 4231575',
        'email'   => 'sekretariat@kopposindo.co.id',
    ],

    // Pejabat penanda tangan slip gaji.
    'penandatangan' => [
        'jabatan' => 'Manajer Keuangan & SDM',
        'nama'    => '.........................',
    ],
];
