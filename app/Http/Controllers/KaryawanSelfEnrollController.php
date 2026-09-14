<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KaryawanSelfEnrollController extends Controller
{
    public function index()
    {
        $karyawan = Karyawan::where('email', Auth::user()->email)->first();
        return view('karyawan.daftar-wajah', compact('karyawan'));
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string',
            'photo'      => 'required|string',
        ]);

        $karyawan = Karyawan::where('email', Auth::user()->email)->first();

        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan.']);
        }
        if ($karyawan->face_descriptors) {
            return response()->json([
                'success' => false,
                'message' => 'Wajah sudah terdaftar. Tidak bisa mendaftar ulang.'
            ], 403);
        }

        // Simpan descriptor wajah
        $descriptors = json_decode($request->descriptor, true);
        $karyawan->face_descriptors = json_encode($descriptors);

        // Simpan foto
        $photoData = $request->photo;
        $photoData = str_replace('data:image/jpeg;base64,', '', $photoData);
        $photoData = str_replace(' ', '+', $photoData);
        $photoName = 'faces/' . $karyawan->nik . '.jpg';
        Storage::disk('public')->put($photoName, base64_decode($photoData));
        $karyawan->foto = $photoName;

        $saved = $karyawan->save();

        if (!$saved) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan ke database.']);
        }

        return response()->json(['success' => true, 'message' => 'Wajah berhasil didaftarkan!']);
    }
}