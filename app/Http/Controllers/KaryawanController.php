<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    // ─────────────────────────────────────────────
    // INDEX — Daftar semua karyawan
    // ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $karyawans = Karyawan::query()
            ->when($request->search, fn($q,$s) => $q->search($s))
            ->when($request->divisi,  fn($q,$p) => $q->divisi($p))
            ->when($request->status, fn($q,$s) => $q->where('status_karyawan',$s))
            ->orderBy('nama')
            ->paginate(20)
            ->withQueryString();

        $totalEnrolled    = Karyawan::enrolled()->count();
        $totalAktif       = Karyawan::where('status_karyawan','aktif')->count();
        $totalBelumEnroll = Karyawan::whereNull('face_descriptors')->count();
        $divisis          = Karyawan::distinct()->pluck('divisi')->sort()->values();

        return view('admin.karyawan.index', compact(
            'karyawans','totalEnrolled','totalAktif','totalBelumEnroll','divisis'
        ));
    }

    // ─────────────────────────────────────────────
    // FORM ENROLL WAJAH
    // ─────────────────────────────────────────────

    public function enrollFaceForm(Request $request, ?Karyawan $karyawan = null)
    {
        if (!$karyawan && $request->has('karyawan_id')) {
            $karyawan = Karyawan::find($request->input('karyawan_id'));
        }
        return view('presensi.enroll-face', compact('karyawan'));
    }

    // ─────────────────────────────────────────────
    // SIMPAN DATA WAJAH (ENROLLMENT)
    // ─────────────────────────────────────────────

    public function enrollFace(Request $request)
    {
        $request->validate([
            'nik'      => 'required|string|max:20',
            'nama'     => 'required|string|max:100',
            'divisi'   => 'required|string',
            'captures' => 'required|array|min:5',
        ]);

        $karyawan = Karyawan::updateOrCreate(
            ['nik' => $request->input('nik')],
            [
                'nama'     => $request->input('nama'),
                'divisi'   => $request->input('divisi'),
                'jabatan'  => $request->input('jabatan', ''),
                'email'    => $request->input('email'),
                'password' => $request->input('password') ? bcrypt($request->input('password')) : bcrypt('password123'),
            ]
        );

        $photoPaths  = [];
        $descriptors = [];

        foreach ($request->input('captures') as $i => $capture) {
            if (empty($capture['dataURL'])) {
                continue;
            }

            $imageData = $capture['dataURL'];
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
            $imageData = base64_decode($imageData);

            $filename = 'face_' . $karyawan->nik . '_' . $i . '_' . Str::random(6) . '.jpg';
            $path     = 'faces/' . $karyawan->nik . '/' . $filename;

            Storage::disk('public')->put($path, $imageData);
            $photoPaths[] = $path;

            if (!empty($capture['descriptor'])) {
                $descriptors[] = $capture['descriptor'];
            }
        }

        if (!empty($photoPaths)) {
            $karyawan->foto = $photoPaths[0];
        }

        $karyawan->simpanFaceData($descriptors, $photoPaths);

        return response()->json([
            'success'      => true,
            'message'      => 'Wajah ' . $karyawan->nama . ' berhasil didaftarkan!',
            'karyawan_id'  => $karyawan->id,
            'photos_count' => count($photoPaths),
        ]);
    }

    // ─────────────────────────────────────────────
    // HAPUS DATA WAJAH
    // ─────────────────────────────────────────────

    public function deleteFace(Karyawan $karyawan)
    {
        $photos = json_decode($karyawan->face_photos ?? '[]', true);
        foreach ($photos as $path) {
            Storage::disk('public')->delete($path);
        }

        $karyawan->hapusFaceData();

        return back()->with('success', 'Data wajah ' . $karyawan->nama . ' berhasil dihapus.');
    }

    // ─────────────────────────────────────────────
    // CRUD STANDARD
    // ─────────────────────────────────────────────

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'             => 'required|unique:karyawans,nik|max:20',
            'nama'            => 'required|max:100',
            'email'           => 'nullable|email|unique:karyawans,email',
            'divisi'          => 'required',
            'jabatan'         => 'required',
            'password'        => 'required|min:8|max:10',
            'status_karyawan' => 'nullable|string|in:aktif,cuti,resign,suspend',
        ], [
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.max' => 'Password maksimal harus 10 karakter.',
        ]);

        $validated['status_karyawan'] = $validated['status_karyawan'] ?? 'aktif';
        $validated['password'] = bcrypt($validated['password']);

        Karyawan::create($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Karyawan $karyawan)
    {
        $statistik = $karyawan->statistikKehadiran(
            now()->month,
            now()->year
        );

        return view('admin.karyawan.show', compact('karyawan', 'statistik'));
    }

    public function edit(Karyawan $karyawan)
    {
        return view('admin.karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $rules = [
            'nik'             => 'required|max:20|unique:karyawans,nik,' . $karyawan->id,
            'nama'            => 'required|max:100',
            'email'           => 'nullable|email|unique:karyawans,email,' . $karyawan->id,
            'divisi'          => 'required',
            'jabatan'         => 'required',
            'status_karyawan' => 'required|string|in:aktif,cuti,resign,suspend',
        ];

        if ($request->filled('password')) {
            $rules['password'] = 'required|min:8|max:10';
        }

        $validated = $request->validate($rules, [
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.max' => 'Password maksimal harus 10 karakter.',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($request->password);
        }

        $karyawan->update($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $photos = json_decode($karyawan->face_photos ?? '[]', true);
        foreach ($photos as $path) {
            Storage::disk('public')->delete($path);
        }

        $karyawan->delete();

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil dihapus.');
    }
}