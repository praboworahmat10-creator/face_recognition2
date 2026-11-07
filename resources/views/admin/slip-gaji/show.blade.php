@extends('layouts.admin')

@section('title', 'Detail Slip Gaji - ' . $slipGaji->karyawan->nama)
@section('page-title', 'Detail Slip Gaji')

@push('styles')
<style>
    .slip-container {
        max-width: 900px;
        margin: 0 auto 3rem;
    }
    .slip-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-sm);
        padding: 2.5rem;
    }
    .slip-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--border);
        margin-bottom: 2rem;
    }
    .slip-brand h1 {
        font-size: 1.35rem;
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.02em;
    }
    .slip-brand p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 2px;
    }
    .slip-meta {
        text-align: right;
    }
    .slip-meta .meta-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .slip-meta .meta-no {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .karyawan-box {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
    }
    .kb-item .kb-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 4px;
    }
    .kb-item .kb-val {
        font-size: 0.925rem;
        font-weight: 700;
        color: var(--text-main);
    }

    .tables-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 768px) {
        .tables-grid { grid-template-columns: 1fr; }
    }

    .section-box {
        border: 1px solid var(--border);
        border-radius: var(--radius-md);
        overflow: hidden;
    }
    .sec-head {
        padding: 0.75rem 1rem;
        font-size: 0.825rem;
        font-weight: 700;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .sec-head.inc {
        background: var(--success-light);
        color: var(--success-dark);
        border-bottom: 1px solid #a7f3d0;
    }
    .sec-head.dec {
        background: var(--danger-light);
        color: var(--danger-dark);
        border-bottom: 1px solid #fecaca;
    }
    .slip-tbl {
        width: 100%;
        border-collapse: collapse;
    }
    .slip-tbl td {
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        border-bottom: 1px solid var(--border);
    }
    .slip-tbl tr:last-child td {
        border-bottom: none;
    }
    .slip-tbl .money {
        text-align: right;
        font-family: 'JetBrains Mono', monospace;
        font-weight: 600;
    }

    .summary-total {
        background: var(--surface);
        border: 2px solid var(--primary);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .st-val {
        font-size: 1.75rem;
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
        color: var(--primary);
    }

    .sign-row {
        display: flex;
        justify-content: space-between;
        margin-top: 3rem;
        text-align: center;
    }
    .sign-box {
        width: 220px;
    }
    .sign-line {
        margin-top: 4.5rem;
        border-top: 1px dashed var(--text-muted);
        padding-top: 0.5rem;
        font-weight: 700;
        font-size: 0.875rem;
    }
</style>
@endpush

@section('content')

<div class="slip-container">
    {{-- Action Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('admin.slip-gaji.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </a>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @if ($slipGaji->status === 'draft')
                <form action="{{ route('admin.slip-gaji.publish', $slipGaji) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Publikasikan slip gaji ini ke karyawan?')">
                        <i class="fas fa-paper-plane"></i> Publikasikan
                    </button>
                </form>
            @else
                <form action="{{ route('admin.slip-gaji.unpublish', $slipGaji) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Kembalikan slip gaji ini ke status draft?')">
                        <i class="fas fa-undo"></i> Tarik ke Draft
                    </button>
                </form>
            @endif

            <a href="{{ route('admin.slip-gaji.edit', $slipGaji) }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-edit"></i> Edit
            </a>

            <a href="{{ route('admin.slip-gaji.cetak', $slipGaji) }}" target="_blank" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> Cetak / Unduh PDF
            </a>

            <form action="{{ route('admin.slip-gaji.destroy', $slipGaji) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus slip gaji ini secara permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div style="background: var(--success-light); color: var(--success-dark); border: 1px solid var(--success); border-radius: 8px; padding: 0.85rem 1.25rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Kertas Slip Gaji --}}
    <div class="slip-card">
        <div class="slip-header">
            <div class="slip-brand">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <div style="width: 38px; height: 38px; background: var(--primary); color: #fff; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                        <i class="fas fa-mail-bulk"></i>
                    </div>
                    <div>
                        <h1>KOPPOSINDO</h1>
                        <p>Koperasi Pegawai Pos Indonesia</p>
                    </div>
                </div>
            </div>
            <div class="slip-meta">
                <div class="meta-title">SLIP GAJI KARYAWAN</div>
                <div class="meta-no">No: {{ $slipGaji->nomor_slip }}</div>
                <div style="margin-top: 6px;">
                    <span class="badge badge-{{ $slipGaji->status_color }}">
                        {{ $slipGaji->status_label }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Identitas Karyawan --}}
        <div class="karyawan-box">
            <div class="kb-item">
                <div class="kb-label">Nama Karyawan</div>
                <div class="kb-val">{{ $slipGaji->karyawan->nama }}</div>
            </div>
            <div class="kb-item">
                <div class="kb-label">Nomor Induk Karyawan (NIK)</div>
                <div class="kb-val" style="font-family:'JetBrains Mono';">{{ $slipGaji->karyawan->nik }}</div>
            </div>
            <div class="kb-item">
                <div class="kb-label">Jabatan / Divisi</div>
                <div class="kb-val">{{ $slipGaji->karyawan->jabatan ?? '-' }} / {{ $slipGaji->karyawan->divisi ?? '-' }}</div>
            </div>
            <div class="kb-item">
                <div class="kb-label">Periode Pembayaran</div>
                <div class="kb-val">{{ $slipGaji->periode }}</div>
            </div>
        </div>

        {{-- Rekap Kehadiran --}}
        <div style="background: var(--bg); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 0.75rem 1.25rem; margin-bottom: 2rem; display: flex; justify-content: space-around; text-align: center; font-size: 0.85rem;">
            <div>
                <span style="color:var(--text-muted); font-size: 0.7rem; text-transform:uppercase; font-weight:600; display:block;">Hadir</span>
                <strong style="color:var(--success); font-family:'JetBrains Mono'; font-size:1.1rem;">{{ $slipGaji->total_hadir }}</strong> hari
            </div>
            <div>
                <span style="color:var(--text-muted); font-size: 0.7rem; text-transform:uppercase; font-weight:600; display:block;">Izin</span>
                <strong style="color:var(--warning); font-family:'JetBrains Mono'; font-size:1.1rem;">{{ $slipGaji->total_izin }}</strong> hari
            </div>
            <div>
                <span style="color:var(--text-muted); font-size: 0.7rem; text-transform:uppercase; font-weight:600; display:block;">Sakit</span>
                <strong style="color:#0ea5e9; font-family:'JetBrains Mono'; font-size:1.1rem;">{{ $slipGaji->total_sakit }}</strong> hari
            </div>
            <div>
                <span style="color:var(--text-muted); font-size: 0.7rem; text-transform:uppercase; font-weight:600; display:block;">Cuti</span>
                <strong style="color:#8b5cf6; font-family:'JetBrains Mono'; font-size:1.1rem;">{{ $slipGaji->total_cuti }}</strong> hari
            </div>
            <div>
                <span style="color:var(--text-muted); font-size: 0.7rem; text-transform:uppercase; font-weight:600; display:block;">Alpa</span>
                <strong style="color:var(--danger); font-family:'JetBrains Mono'; font-size:1.1rem;">{{ $slipGaji->total_alpa }}</strong> hari
            </div>
        </div>

        {{-- Rincian Penerimaan & Potongan --}}
        <div class="tables-grid">
            {{-- Tabel Penerimaan --}}
            <div class="section-box">
                <div class="sec-head inc">
                    <span><i class="fas fa-plus-circle"></i> PENERIMAAN</span>
                    <span>NOMINAL</span>
                </div>
                <table class="slip-tbl">
                    <tr>
                        <td>Gaji Pokok</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->gaji_pokok) }}</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Jabatan</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_jabatan) }}</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Kehadiran</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_kehadiran) }}</td>
                    </tr>
                    <tr>
                        <td>Tunjangan Transport & Makan</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_transport_makan) }}</td>
                    </tr>
                    <tr>
                        <td>Bonus / Insentif</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->bonus) }}</td>
                    </tr>
                    <tr style="background: var(--bg); font-weight: 700;">
                        <td>Total Pendapatan (A)</td>
                        <td class="money" style="color: var(--success-dark);">{{ \App\Models\SlipGaji::rupiah($slipGaji->total_pendapatan) }}</td>
                    </tr>
                </table>
            </div>

            {{-- Tabel Potongan --}}
            <div class="section-box">
                <div class="sec-head dec">
                    <span><i class="fas fa-minus-circle"></i> POTONGAN</span>
                    <span>NOMINAL</span>
                </div>
                <table class="slip-tbl">
                    <tr>
                        <td>Potongan BPJS (Kes / TK)</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_bpjs) }}</td>
                    </tr>
                    <tr>
                        <td>Potongan Keterlambatan</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_terlambat) }}</td>
                    </tr>
                    <tr>
                        <td>Potongan Lainnya / Alpa</td>
                        <td class="money">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_lainnya) }}</td>
                    </tr>
                    <tr style="background: var(--bg); font-weight: 700;">
                        <td>Total Potongan (B)</td>
                        <td class="money" style="color: var(--danger-dark);">{{ \App\Models\SlipGaji::rupiah($slipGaji->total_potongan) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Take Home Pay --}}
        <div class="summary-total">
            <div>
                <div style="font-size: 0.8rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-muted); font-weight:700;">
                    Penerimaan Bersih (Take Home Pay: A - B)
                </div>
                <div style="font-size: 0.85rem; font-style: italic; color:var(--text-muted); margin-top: 4px;">
                    Terbilang: <strong>{{ ucwords(\App\Models\SlipGaji::angkaKeHuruf($slipGaji->gaji_bersih)) }} rupiah</strong>
                </div>
                @if ($slipGaji->catatan)
                    <div style="font-size: 0.8rem; color:var(--text-muted); margin-top: 6px;">
                        <em>Catatan: {{ $slipGaji->catatan }}</em>
                    </div>
                @endif
            </div>
            <div class="st-val">
                {{ $slipGaji->gaji_bersih_rupiah }}
            </div>
        </div>

        {{-- Area Tanda Tangan --}}
        <div class="sign-row">
            <div class="sign-box">
                <div style="font-size: 0.8rem; color: var(--text-muted);">Penerima,</div>
                <div class="sign-line">{{ $slipGaji->karyawan->nama }}</div>
            </div>
            <div class="sign-box">
                <div style="font-size: 0.8rem; color: var(--text-muted);">Bandung, {{ now()->translatedFormat('d F Y') }}<br>Pengurus KOPPOSINDO</div>
                <div class="sign-line">Bendahara / HRD</div>
            </div>
        </div>
    </div>
</div>

@endsection
