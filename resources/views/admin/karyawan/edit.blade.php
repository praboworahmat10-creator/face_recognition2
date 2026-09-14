@extends('layouts.admin')

@section('title', 'Edit Karyawan')
@section('page-title', 'Edit Karyawan')

@push('styles')
<style>
    .page-hd {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
    }
    .page-hd h2 { font-size:18px; font-weight:700; color:var(--text); letter-spacing:-0.3px; }
    .page-hd p  { font-size:12.5px; color:var(--text-3); margin-top:2px; }

    .btn {
        display: inline-flex; align-items: center; gap:6px;
        padding: 8px 16px; border-radius:9px;
        font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600;
        border:none; cursor:pointer; transition:all 0.15s; text-decoration:none;
    }
    .btn-primary { background:var(--primary); color:white; }
    .btn-primary:hover { background:#ea580c; }
    .btn-secondary { background:var(--surface-2); color:var(--text-2); border:1px solid var(--border); }
    .btn-secondary:hover { border-color:var(--primary); color:var(--primary); }

    .card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); box-shadow:var(--shadow); padding: 24px;
        max-width: 600px; margin: 0 auto;
    }

    .form-group {
        margin-bottom: 18px;
    }

    .form-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-2);
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        height: 38px;
        padding: 0 12px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 13.5px;
        outline: none;
        transition: border-color 0.15s;
        box-sizing: border-box;
    }
    .form-control:focus {
        border-color: var(--primary);
    }
    .form-control::placeholder {
        color: var(--text-3);
    }

    select.form-control {
        padding: 0 8px;
    }

    .text-danger {
        color: var(--rose);
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    .help-text {
        font-size: 11.5px;
        color: var(--text-3);
        margin-top: 4px;
        display: block;
    }
</style>
@endpush

@section('content')

<div class="page-hd">
    <div>
        <h2>Edit Karyawan</h2>
        <p>Perbarui informasi detail karyawan</p>
    </div>
    <div>
        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('admin.karyawan.update', $karyawan) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label" for="nik">NIK (Nomor Induk Karyawan)</label>
            <input type="text" id="nik" name="nik" class="form-control" placeholder="Contoh: NIK123456" value="{{ old('nik', $karyawan->nik) }}" required>
            @error('nik')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" class="form-control" placeholder="Contoh: Gio Rahmat" value="{{ old('nama', $karyawan->nama) }}" required>
            @error('nama')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="email">Alamat Email</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="Contoh: gio@koperasipos.com" value="{{ old('email', $karyawan->email) }}" required>
            @error('email')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="divisi">Divisi / Bagian</label>
            <input type="text" id="divisi" name="divisi" class="form-control" placeholder="Contoh: Keuangan, Operasional" value="{{ old('divisi', $karyawan->divisi) }}" required>
            @error('divisi')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="jabatan">Jabatan</label>
            <input type="text" id="jabatan" name="jabatan" class="form-control" placeholder="Contoh: Staff, Supervisor" value="{{ old('jabatan', $karyawan->jabatan) }}" required>
            @error('jabatan')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Password Akun (Opsional)</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="8-10 karakter" minlength="8" maxlength="10">
            <span class="help-text">Biarkan kosong untuk mempertahankan password lama (8-10 karakter jika diisi).</span>
            @error('password')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="status_karyawan">Status Karyawan</label>
            <select id="status_karyawan" name="status_karyawan" class="form-control">
                <option value="aktif" {{ old('status_karyawan', $karyawan->status_karyawan) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="cuti" {{ old('status_karyawan', $karyawan->status_karyawan) == 'cuti' ? 'selected' : '' }}>Cuti</option>
                <option value="resign" {{ old('status_karyawan', $karyawan->status_karyawan) == 'resign' ? 'selected' : '' }}>Resign</option>
                <option value="suspend" {{ old('status_karyawan', $karyawan->status_karyawan) == 'suspend' ? 'selected' : '' }}>Suspend</option>
            </select>
            @error('status_karyawan')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 8px;">
            <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Perbarui Karyawan
            </button>
        </div>
    </form>
</div>

@endsection
