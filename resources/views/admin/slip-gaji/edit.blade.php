@extends('layouts.admin')

@section('title', 'Edit Slip Gaji - ' . $slipGaji->karyawan->nama)
@section('page-title', 'Edit Slip Gaji')

@push('styles')
<style>
    .form-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 1.75rem;
        margin-bottom: 2rem;
    }
    .form-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-main);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-muted);
    }
    .form-control {
        width: 100%;
        padding: 0.65rem 0.85rem;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 0.875rem;
        color: var(--text-main);
        background: var(--surface);
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus {
        border-color: var(--primary);
        outline: none;
        box-shadow: 0 0 0 3px var(--primary-light);
    }
    .input-rupiah {
        position: relative;
    }
    .input-rupiah span {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-muted);
        pointer-events: none;
    }
    .input-rupiah input {
        padding-left: 38px;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
    }

    .rekap-banner {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 12px;
    }
    .rekap-item {
        text-align: center;
    }
    .rekap-item .r-val {
        font-size: 1.25rem;
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
        color: var(--text-main);
    }
    .rekap-item .r-lbl {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 600;
        margin-top: 2px;
    }

    .calc-card {
        background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
        color: #fff;
        border-radius: var(--radius-lg);
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .calc-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        opacity: 0.9;
    }
    .calc-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
    }
    .calc-total-val {
        font-size: 1.6rem;
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
    }
</style>
@endpush

@section('content')

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.2rem; font-weight: 700;">Edit Slip Gaji</h2>
        <p style="font-size: 0.825rem; color: var(--text-muted);">{{ $slipGaji->karyawan->nama }} — Periode {{ $slipGaji->periode }}</p>
    </div>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('admin.slip-gaji.show', $slipGaji) }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-eye"></i> Lihat Preview
        </a>
        <a href="{{ route('admin.slip-gaji.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@if (isset($errors) && $errors->any())
    <div style="background: var(--danger-light); color: var(--danger-dark); border: 1px solid var(--danger); border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
        <strong style="display: block; margin-bottom: 0.5rem;"><i class="fas fa-exclamation-triangle"></i> Terjadi kesalahan:</strong>
        <ul style="margin-left: 1.25rem;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('admin.slip-gaji.update', $slipGaji) }}" method="POST" id="formSlipGaji">
    @csrf
    @method('PUT')

    <div class="form-card">
        <div class="form-section-title">
            <i class="fas fa-user-circle"></i> Periode & Identitas Karyawan
        </div>

        <div class="form-grid">
            <div class="form-group" style="grid-column: span 2;">
                <label for="karyawan_id">Karyawan *</label>
                <select name="karyawan_id" id="karyawan_id" class="form-control" required onchange="fetchPresensi()">
                    @foreach ($karyawans as $k)
                        <option value="{{ $k->id }}" {{ old('karyawan_id', $slipGaji->karyawan_id) == $k->id ? 'selected' : '' }}>
                            {{ $k->nama }} (NIK: {{ $k->nik }}) — {{ $k->jabatan ?? '-' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="bulan">Bulan *</label>
                <select name="bulan" id="bulan" class="form-control" required onchange="fetchPresensi()">
                    @for ($b = 1; $b <= 12; $b++)
                        <option value="{{ $b }}" {{ old('bulan', $slipGaji->bulan) == $b ? 'selected' : '' }}>
                            {{ \App\Models\SlipGaji::NAMA_BULAN[$b] }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="form-group">
                <label for="tahun">Tahun *</label>
                <input type="number" name="tahun" id="tahun" class="form-control" value="{{ old('tahun', $slipGaji->tahun) }}" min="2000" max="2100" required onchange="fetchPresensi()">
            </div>
        </div>

        {{-- Banner Rekap Presensi Otomatis --}}
        <div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 0.5rem;">
                <span style="font-size: 0.75rem; font-weight:600; text-transform:uppercase; color:var(--text-muted);">
                    <i class="fas fa-calendar-check"></i> Rekap Presensi Periode Terpilih
                </span>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fetchPresensi()" style="font-size: 0.72rem; padding: 0.25rem 0.5rem;">
                    <i class="fas fa-sync-alt"></i> Sinkronkan Presensi
                </button>
            </div>
            <div class="rekap-banner">
                <div class="rekap-item">
                    <div class="r-val" id="disp_hadir" style="color:var(--success);">{{ $slipGaji->total_hadir }}</div>
                    <div class="r-lbl">Hadir</div>
                </div>
                <div class="rekap-item">
                    <div class="r-val" id="disp_izin" style="color:var(--warning);">{{ $slipGaji->total_izin }}</div>
                    <div class="r-lbl">Izin</div>
                </div>
                <div class="rekap-item">
                    <div class="r-val" id="disp_sakit" style="color:#0ea5e9;">{{ $slipGaji->total_sakit }}</div>
                    <div class="r-lbl">Sakit</div>
                </div>
                <div class="rekap-item">
                    <div class="r-val" id="disp_cuti" style="color:#8b5cf6;">{{ $slipGaji->total_cuti }}</div>
                    <div class="r-lbl">Cuti</div>
                </div>
                <div class="rekap-item">
                    <div class="r-val" id="disp_alpa" style="color:var(--danger);">{{ $slipGaji->total_alpa }}</div>
                    <div class="r-lbl">Alpa</div>
                </div>
            </div>
        </div>
    </div>

    {{-- KOMPONEN PENDAPATAN & POTONGAN (2 KOLOM) --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        
        {{-- Pendapatan --}}
        <div class="form-card" style="margin-bottom: 0;">
            <div class="form-section-title" style="color: var(--success-dark);">
                <i class="fas fa-plus-circle"></i> Komponen Pendapatan
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="gaji_pokok">Gaji Pokok</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="gaji_pokok" id="gaji_pokok" class="form-control" value="{{ old('gaji_pokok', $slipGaji->gaji_pokok) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="tunjangan_jabatan">Tunjangan Jabatan</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="tunjangan_jabatan" id="tunjangan_jabatan" class="form-control" value="{{ old('tunjangan_jabatan', $slipGaji->tunjangan_jabatan) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="tunjangan_kehadiran">Tunjangan Kehadiran</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="tunjangan_kehadiran" id="tunjangan_kehadiran" class="form-control" value="{{ old('tunjangan_kehadiran', $slipGaji->tunjangan_kehadiran) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="tunjangan_transport_makan">Tunjangan Makan & Transport</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="tunjangan_transport_makan" id="tunjangan_transport_makan" class="form-control" value="{{ old('tunjangan_transport_makan', $slipGaji->tunjangan_transport_makan) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group">
                <label for="bonus">Bonus / Insentif Lainnya</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="bonus" id="bonus" class="form-control" value="{{ old('bonus', $slipGaji->bonus) }}" oninput="hitungTotal()">
                </div>
            </div>
        </div>

        {{-- Potongan --}}
        <div class="form-card" style="margin-bottom: 0;">
            <div class="form-section-title" style="color: var(--danger-dark);">
                <i class="fas fa-minus-circle"></i> Komponen Potongan
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="potongan_bpjs">Potongan BPJS (Kesehatan / TK)</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="potongan_bpjs" id="potongan_bpjs" class="form-control" value="{{ old('potongan_bpjs', $slipGaji->potongan_bpjs) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="potongan_terlambat">Potongan Keterlambatan</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="potongan_terlambat" id="potongan_terlambat" class="form-control" value="{{ old('potongan_terlambat', $slipGaji->potongan_terlambat) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="potongan_lainnya">Potongan Lainnya / Alpa</label>
                <div class="input-rupiah">
                    <span>Rp</span>
                    <input type="number" step="1000" min="0" name="potongan_lainnya" id="potongan_lainnya" class="form-control" value="{{ old('potongan_lainnya', $slipGaji->potongan_lainnya) }}" oninput="hitungTotal()">
                </div>
            </div>

            <div class="form-group">
                <label for="catatan">Catatan / Keterangan Tambahan</label>
                <textarea name="catatan" id="catatan" rows="4" class="form-control" placeholder="Keterangan tambahan...">{{ old('catatan', $slipGaji->catatan) }}</textarea>
            </div>
        </div>
    </div>

    {{-- KALKULATOR TOTAL PAYOUT & STATUS --}}
    <div class="calc-card">
        <div class="calc-row">
            <span>Total Pendapatan (Kotor)</span>
            <span id="disp_tot_pendapatan" style="font-family:'JetBrains Mono'; font-weight:700;">Rp 0</span>
        </div>
        <div class="calc-row">
            <span>Total Potongan</span>
            <span id="disp_tot_potongan" style="font-family:'JetBrains Mono'; font-weight:700; color:#fca5a5;">Rp 0</span>
        </div>
        <div class="calc-total">
            <div>
                <div style="font-size: 0.75rem; text-transform:uppercase; letter-spacing:0.05em; opacity:0.8;">Take Home Pay (Gaji Bersih)</div>
                <div style="font-size: 0.8rem; font-style: italic; opacity:0.75;" id="disp_terbilang">Nol rupiah</div>
            </div>
            <div class="calc-total-val" id="disp_gaji_bersih">Rp 0</div>
        </div>
    </div>

    <div class="form-card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
        <div class="form-group" style="min-width: 220px;">
            <label for="status">Status Publikasi</label>
            <select name="status" id="status" class="form-control" required>
                <option value="draft" {{ old('status', $slipGaji->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ old('status', $slipGaji->status) == 'published' ? 'selected' : '' }}>Terbitkan (Published)</option>
            </select>
        </div>

        <div style="display:flex; gap:10px;">
            <a href="{{ route('admin.slip-gaji.show', $slipGaji) }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary" id="btnSubmit">
                <i class="fas fa-save"></i> Perbarui Slip Gaji
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
    function formatRupiah(num) {
        return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
    }

    function hitungTotal() {
        const getVal = id => parseFloat(document.getElementById(id)?.value) || 0;

        const gp  = getVal('gaji_pokok');
        const tj  = getVal('tunjangan_jabatan');
        const tk  = getVal('tunjangan_kehadiran');
        const tm  = getVal('tunjangan_transport_makan');
        const bn  = getVal('bonus');

        const bpjs = getVal('potongan_bpjs');
        const pTel = getVal('potongan_terlambat');
        const pLain = getVal('potongan_lainnya');

        const totalPendapatan = gp + tj + tk + tm + bn;
        const totalPotongan   = bpjs + pTel + pLain;
        const gajiBersih      = totalPendapatan - totalPotongan;

        document.getElementById('disp_tot_pendapatan').innerText = formatRupiah(totalPendapatan);
        document.getElementById('disp_tot_potongan').innerText   = formatRupiah(totalPotongan);
        document.getElementById('disp_gaji_bersih').innerText     = formatRupiah(gajiBersih);
    }

    async function fetchPresensi() {
        const kid = document.getElementById('karyawan_id').value;
        const bln = document.getElementById('bulan').value;
        const thn = document.getElementById('tahun').value;

        if (!kid || !bln || !thn) return;

        try {
            const url = `{{ route('admin.slip-gaji.hitung-presensi') }}?karyawan_id=${kid}&bulan=${bln}&tahun=${thn}`;
            const res = await fetch(url);
            const data = await res.json();

            if (data.success) {
                document.getElementById('disp_hadir').innerText = data.rekap.total_hadir || 0;
                document.getElementById('disp_izin').innerText  = data.rekap.total_izin || 0;
                document.getElementById('disp_sakit').innerText = data.rekap.total_sakit || 0;
                document.getElementById('disp_cuti').innerText  = data.rekap.total_cuti || 0;
                document.getElementById('disp_alpa').innerText  = data.rekap.total_alpa || 0;
            }
        } catch (e) {
            console.error('Gagal mengambil data presensi:', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        hitungTotal();
    });
</script>
@endpush
