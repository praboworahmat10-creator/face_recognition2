<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KaryawanSelfEnrollController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SlipGajiController as AdminSlipGajiController;

// ═══════════════════════════════════════
// ROOT
// ═══════════════════════════════════════
Route::get('/', function () {
    if (!Auth::check()) return redirect()->route('login');

    return match (Auth::user()->role) {
        'admin'  => redirect()->route('admin.dashboard'),
        default  => redirect()->route('presensi'),
    };
});

// ═══════════════════════════════════════
// AUTH — hanya untuk tamu (belum login)
// ═══════════════════════════════════════
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'forgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ═══════════════════════════════════════
// API — bisa diakses semua role yang login
// ═══════════════════════════════════════
Route::middleware('auth')->group(function () {
    Route::get('/api/karyawan/face-descriptors',
        [PresensiController::class, 'getFaceDescriptors'])->name('api.face-descriptors');
    Route::get('/api/karyawan/{nik}',
        [PresensiController::class, 'getMahasiswaByNim']);
});

// ═══════════════════════════════════════
// KARYAWAN ROUTES
// ═══════════════════════════════════════
Route::middleware(['auth', 'role:karyawan'])->group(function () {
    Route::get('/presensi',
        [PresensiController::class, 'index'])->name('presensi');
    Route::post('/presensi/izin',
        [PresensiController::class, 'izin'])->name('presensi.izin');
    Route::post('/presensi/sakit',
        [PresensiController::class, 'sakit'])->name('presensi.sakit');
    Route::post('/presensi/cuti',
        [PresensiController::class, 'cuti'])->name('presensi.cuti');
    Route::post('/presensi/simpan',
        [PresensiController::class, 'simpan'])->name('presensi.simpan');
    Route::post('/presensi/validasi-lokasi',
        [PresensiController::class, 'validasiLokasi'])->name('presensi.validasiLokasi');
    Route::get('/presensi/peta',
        [PresensiController::class, 'peta'])->name('presensi.peta');
    Route::get('/presensi/riwayat',
        [PresensiController::class, 'riwayat'])->name('presensi.riwayat');
    Route::post('/presensi/log-euclidean',
        [PresensiController::class, 'logEuclidean'])->name('presensi.logEuclidean');
        

    Route::get('/profil',
        [AuthController::class, 'profilForm'])->name('profil');
    Route::put('/profil/password',
        [AuthController::class, 'gantiPassword'])->name('profil.password');

    // Slip Gaji — hanya milik sendiri & berstatus published
    Route::get('/slip-gaji',
        [SlipGajiController::class, 'index'])->name('slip-gaji.index');
    Route::get('/slip-gaji/{id}',
        [SlipGajiController::class, 'show'])->whereNumber('id')->name('slip-gaji.show');
    Route::get('/slip-gaji/{id}/cetak',
        [SlipGajiController::class, 'cetak'])->whereNumber('id')->name('slip-gaji.cetak');
});

// ═══════════════════════════════════════
// ADMIN ROUTES
// ═══════════════════════════════════════
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard',
        [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/presensi',
        [PresensiController::class, 'rekap'])->name('presensi.rekap');
    Route::get('/presensi/bulanan',
        [PresensiController::class, 'rekapBulanan'])->name('presensi.rekap-bulanan');
    Route::get('/presensi/export',
        [PresensiController::class, 'export'])->name('presensi.export');
    Route::get('/presensi/bulanan/export',
        [PresensiController::class, 'exportBulanan'])->name('presensi.export-bulanan');

    Route::resource('karyawan', KaryawanController::class);

    Route::get('/karyawan/enroll-face/new',
        [KaryawanController::class, 'enrollFaceForm'])->name('karyawan.enroll-face.new');
    Route::get('/karyawan/{karyawan}/enroll-face',
        [KaryawanController::class, 'enrollFaceForm'])->name('karyawan.enroll-face.form');
    Route::post('/karyawan/enroll-face',
        [KaryawanController::class, 'enrollFace'])->name('karyawan.enroll-face');
    Route::delete('/karyawan/{karyawan}/delete-face',
        [KaryawanController::class, 'deleteFace'])->name('karyawan.delete-face');

    // ── SLIP GAJI ──
    // Helper auto-hitung rekap presensi untuk form create/edit
    Route::get('/slip-gaji/helper/hitung-presensi',
        [AdminSlipGajiController::class, 'hitungPresensi'])->name('slip-gaji.hitung-presensi');
    Route::get('/slip-gaji/{slipGaji}/cetak',
        [AdminSlipGajiController::class, 'cetak'])->name('slip-gaji.cetak');
    Route::post('/slip-gaji/{slipGaji}/publish',
        [AdminSlipGajiController::class, 'publish'])->name('slip-gaji.publish');
    Route::post('/slip-gaji/{slipGaji}/unpublish',
        [AdminSlipGajiController::class, 'unpublish'])->name('slip-gaji.unpublish');

    Route::resource('slip-gaji', AdminSlipGajiController::class)
        ->parameters(['slip-gaji' => 'slipGaji']);

    // Kelola Izin/Sakit (Pindahan dari Dosen)
    Route::get('/izin',                [DashboardController::class, 'izin'])->name('izin');
    Route::post('/izin/{id}/approve',  [DashboardController::class, 'approveIzin'])->name('izin.approve');
    Route::post('/izin/{id}/reject',   [DashboardController::class, 'rejectIzin'])->name('izin.reject');
});