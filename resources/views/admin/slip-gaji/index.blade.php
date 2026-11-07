@extends('layouts.admin')

@section('title', 'Slip Gaji')
@section('page-title', 'Slip Gaji')

@push('styles')
<style>
    .page-hd { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:1.5rem; flex-wrap:wrap; }
    .page-hd h2 { font-size:1.15rem; font-weight:700; letter-spacing:-0.02em; }
    .page-hd p  { font-size:0.825rem; color:var(--text-muted); margin-top:2px; }

    .summary-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-bottom:1.5rem; }
    .summary-card {
        background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg);
        padding:1.25rem; box-shadow:var(--shadow-sm);
    }
    .summary-card .s-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.75rem; }
    .summary-card .s-lbl { font-size:0.7rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.05em; }
    .summary-card .s-ico {
        width:32px; height:32px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:0.8rem;
    }
    .summary-card .s-val { font-size:1.35rem; font-weight:800; letter-spacing:-0.03em; font-family:'JetBrains Mono', monospace; }
    .summary-card .s-sub { font-size:0.72rem; color:var(--text-light); margin-top:4px; }

    .filter-row {
        display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;
        padding:1.25rem 1.5rem; border-bottom:1px solid var(--border); background:var(--bg);
    }
    .filter-row .fg { display:flex; flex-direction:column; gap:4px; }
    .filter-row label { font-size:0.7rem; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.04em; margin:0; }
    .filter-row select, .filter-row input { min-width:150px; }

    .card-flat { background:var(--surface); border:1px solid var(--border); border-radius:var(--radius-lg); box-shadow:var(--shadow-sm); overflow:hidden; }

    .avatar {
        width:34px; height:34px; border-radius:50%; flex-shrink:0;
        background:var(--primary-light); color:var(--primary);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:0.75rem; font-weight:700;
    }
    .td-name { font-weight:600; color:var(--text-main); }
    .td-nik  { font-family:'JetBrains Mono', monospace; font-size:0.72rem; color:var(--text-muted); }
    .td-money { font-family:'JetBrains Mono', monospace; font-weight:700; }

    .act-row { display:flex; gap:6px; align-items:center; justify-content:flex-end; }
    .act-btn {
        width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:var(--surface);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:0.75rem; color:var(--text-muted); cursor:pointer; transition:all 0.15s; text-decoration:none;
    }
    .act-btn:hover { border-color:var(--primary); color:var(--primary); background:var(--primary-light); }
    .act-btn.danger:hover { border-color:var(--danger); color:var(--danger); background:var(--danger-light); }
    .act-btn.ok:hover { border-color:var(--success); color:var(--success); background:var(--success-light); }

    .empty-state { text-align:center; padding:3rem 1.25rem; color:var(--text-muted); font-size:0.875rem; }
    .empty-state i { font-size:2rem; margin-bottom:0.75rem; display:block; opacity:0.25; }

    .pagination-wrap {
        padding:1rem 1.5rem; border-top:1px solid var(--border);
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        font-size:0.78rem; color:var(--text-muted); flex-wrap:wrap;
    }

    @media print {
        .sidebar, .topbar, .filter-row, .btn, .act-row, .pagination-wrap { display:none !important; }
        .main { margin-left:0 !important; }
        body { background:#fff !important; }
        .card-flat, .summary-card { border:none !important; box-shadow:none !important; }
    }
</style>
@endpush

@section('content')

<div class="page-hd">
    <div>
        <h2>Kelola Slip Gaji</h2>
        <p>Buat, publikasikan, dan cetak slip gaji karyawan KOPPOSINDO</p>
    </div>
    <div style="display:flex; gap:8px;">
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            <i class="fas fa-print"></i> Cetak Daftar
        </button>
        <a href="{{ route('admin.slip-gaji.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Buat Slip Gaji
        </a>
    </div>
</div>

{{-- ── SUMMARY ── --}}
<div class="summary-grid">
    <div class="summary-card">
        <div class="s-top">
            <span class="s-lbl">Total Payout</span>
            <span class="s-ico" style="background:var(--success-light); color:var(--success-dark);">
                <i class="fas fa-sack-dollar"></i>
            </span>
        </div>
        <div class="s-val" style="color:var(--success-dark);">
            {{ \App\Models\SlipGaji::rupiah($summary['total_payout']) }}
        </div>
        <div class="s-sub">Gaji bersih seluruh slip terfilter</div>
    </div>

    <div class="summary-card">
        <div class="s-top">
            <span class="s-lbl">Total Pendapatan</span>
            <span class="s-ico" style="background:var(--primary-light); color:var(--primary);">
                <i class="fas fa-arrow-trend-up"></i>
            </span>
        </div>
        <div class="s-val" style="color:var(--primary);">
            {{ \App\Models\SlipGaji::rupiah($summary['total_pendapatan']) }}
        </div>
        <div class="s-sub">Potongan: {{ \App\Models\SlipGaji::rupiah($summary['total_potongan']) }}</div>
    </div>

    <div class="summary-card">
        <div class="s-top">
            <span class="s-lbl">Jumlah Slip</span>
            <span class="s-ico" style="background:var(--warning-light); color:var(--warning-dark);">
                <i class="fas fa-file-invoice-dollar"></i>
            </span>
        </div>
        <div class="s-val">{{ $summary['jumlah_slip'] }}</div>
        <div class="s-sub">
            {{ $summary['jumlah_published'] }} published &middot; {{ $summary['jumlah_draft'] }} draft
        </div>
    </div>

    <div class="summary-card">
        <div class="s-top">
            <span class="s-lbl">Periode Aktif</span>
            <span class="s-ico" style="background:var(--bg); color:var(--text-muted);">
                <i class="fas fa-calendar-days"></i>
            </span>
        </div>
        <div class="s-val" style="font-size:1.05rem;">
            {{ $bulan ? (\App\Models\SlipGaji::NAMA_BULAN[$bulan] ?? '—') : 'Semua Bulan' }}
        </div>
        <div class="s-sub">{{ $tahun ?? 'Semua tahun' }}</div>
    </div>
</div>

{{-- ── TABEL ── --}}
<div class="card-flat">
    <form method="GET" class="filter-row">
        <div class="fg">
            <label for="f-karyawan">Karyawan</label>
            <select id="f-karyawan" name="karyawan_id" class="form-control">
                <option value="">Semua Karyawan</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}" @selected($karyawanId === $k->id)>
                        {{ $k->nama }} ({{ $k->nik }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="fg">
            <label for="f-bulan">Bulan</label>
            <select id="f-bulan" name="bulan" class="form-control">
                <option value="">Semua Bulan</option>
                @foreach(\App\Models\SlipGaji::NAMA_BULAN as $no => $nama)
                    <option value="{{ $no }}" @selected($bulan === $no)>{{ $nama }}</option>
                @endforeach
            </select>
        </div>

        <div class="fg">
            <label for="f-tahun">Tahun</label>
            <select id="f-tahun" name="tahun" class="form-control">
                <option value="">Semua Tahun</option>
                @foreach($daftarTahun as $th)
                    <option value="{{ $th }}" @selected($tahun === (int) $th)>{{ $th }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-secondary btn-sm">
            <i class="fas fa-filter"></i> Filter
        </button>

        @if(request()->hasAny(['bulan','tahun','karyawan_id']))
            <a href="{{ route('admin.slip-gaji.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-xmark"></i> Reset
            </a>
        @endif
    </form>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Karyawan</th>
                    <th>Periode</th>
                    <th>Hadir</th>
                    <th>Pendapatan</th>
                    <th>Potongan</th>
                    <th>Gaji Bersih</th>
                    <th>Status</th>
                    <th style="text-align:right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slips as $i => $slip)
                <tr>
                    <td class="td-nik">{{ $slips->firstItem() + $i }}</td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="avatar">{{ optional($slip->karyawan)->inisial ?? '?' }}</div>
                            <div>
                                <div class="td-name">{{ optional($slip->karyawan)->nama ?? 'Karyawan dihapus' }}</div>
                                <div class="td-nik">{{ optional($slip->karyawan)->nik ?? '—' }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:600; font-size:0.825rem;">{{ $slip->periode }}</td>
                    <td>
                        <span class="badge badge-hadir">{{ $slip->total_hadir }} hari</span>
                    </td>
                    <td class="td-money" style="color:var(--success-dark);">
                        {{ \App\Models\SlipGaji::rupiah($slip->total_pendapatan) }}
                    </td>
                    <td class="td-money" style="color:var(--danger-dark);">
                        {{ \App\Models\SlipGaji::rupiah($slip->total_potongan) }}
                    </td>
                    <td class="td-money" style="color:var(--primary);">
                        {{ \App\Models\SlipGaji::rupiah($slip->gaji_bersih) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $slip->status_color }}">
                            <i class="fas fa-{{ $slip->is_published ? 'circle-check' : 'pen-ruler' }}"></i>
                            {{ $slip->status_label }}
                        </span>
                    </td>
                    <td>
                        <div class="act-row">
                            <a href="{{ route('admin.slip-gaji.show', $slip) }}" class="act-btn" title="Preview">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.slip-gaji.cetak', $slip) }}" target="_blank" class="act-btn" title="Cetak / PDF">
                                <i class="fas fa-print"></i>
                            </a>
                            <a href="{{ route('admin.slip-gaji.edit', $slip) }}" class="act-btn" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>

                            @if($slip->is_published)
                                <form method="POST" action="{{ route('admin.slip-gaji.unpublish', $slip) }}"
                                      onsubmit="return confirm('Tarik slip gaji ini dari karyawan dan kembalikan ke draft?')">
                                    @csrf
                                    <button type="submit" class="act-btn" title="Unpublish">
                                        <i class="fas fa-eye-slash"></i>
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.slip-gaji.publish', $slip) }}"
                                      onsubmit="return confirm('Publikasikan slip gaji ini agar bisa dilihat karyawan?')">
                                    @csrf
                                    <button type="submit" class="act-btn ok" title="Publish">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('admin.slip-gaji.destroy', $slip) }}"
                                  onsubmit="return confirm('Hapus slip gaji ini? Tindakan ini tidak bisa dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="act-btn danger" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-file-invoice-dollar"></i>
                            Belum ada slip gaji pada filter ini.
                            <div style="margin-top:1rem;">
                                <a href="{{ route('admin.slip-gaji.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Buat Slip Gaji
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($slips->total() > 0)
    <div class="pagination-wrap">
        <span>Menampilkan {{ $slips->firstItem() }}–{{ $slips->lastItem() }} dari {{ $slips->total() }} slip</span>
        {{ $slips->links() }}
    </div>
    @endif
</div>

@endsection
