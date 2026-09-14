@extends('layouts.admin')

@section('title', 'Rekap Bulanan')
@section('page-title', 'Rekap Bulanan')

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
    .btn-sm { padding:5px 11px; font-size:12px; border-radius:7px; }

    .card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden;
    }

    .tabs-wrap {
        display: flex; gap: 4px; margin-bottom: 20px;
        background: rgba(0,0,0,0.03); padding: 4px; border-radius: 10px;
        width: fit-content;
    }
    .tab-item {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px; border-radius: 8px; font-size: 13px; font-weight: 600;
        text-decoration: none; color: var(--text-2); transition: all 0.15s;
    }
    .tab-item:hover { color: var(--primary); }
    .tab-item.active { background: white; color: var(--primary); box-shadow: 0 2px 6px rgba(0,0,0,0.05); }

    .filter-row {
        padding:12px 18px; border-bottom:1px solid var(--border);
        display:flex; gap:8px; flex-wrap:wrap; background:var(--surface-2);
    }

    .filter-input {
        height:34px; padding:0 12px;
        background:var(--surface); border:1px solid var(--border);
        border-radius:8px; color:var(--text);
        font-family:'DM Sans',sans-serif; font-size:13px; outline:none;
        transition:border-color 0.15s;
    }
    .filter-input:focus { border-color:var(--primary); }
    .filter-input::placeholder { color:var(--text-3); }
    .filter-input.grow { flex:1; min-width:180px; }

    table { width:100%; border-collapse:collapse; text-align:left; }
    thead tr { background:var(--surface-2); border-bottom:1px solid var(--border); }
    th {
        padding:12px 18px; text-align:left;
        font-size:12px; font-family:'DM Sans',sans-serif;
        font-weight:600; color:var(--text); letter-spacing:0.3px;
        white-space:nowrap;
    }
    td { padding:14px 18px; font-size:13px; border-bottom:1px solid var(--border); vertical-align:middle; color:var(--text-2); }
    tr:last-child td { border-bottom:none; }
    tbody tr { transition: background 0.15s ease; }
    tbody tr:hover td { background:rgba(249, 115, 22, 0.03); }

    .td-nik { font-family:'DM Mono',monospace; font-size:11.5px; color:var(--primary); }
    .td-name { font-weight:600; color:var(--text); }

    .badge-count {
        display:inline-flex; align-items:center; justify-content:center;
        min-width:24px; height:24px; padding:0 6px; border-radius:12px;
        font-size:12px; font-weight:700; font-family:'DM Mono',monospace;
    }
    .badge-hadir { background:var(--emerald-light); color:var(--emerald); }
    .badge-izin  { background:var(--amber-light); color:var(--amber); }
    .badge-sakit { background:var(--sky-light); color:var(--sky); }
    .badge-cuti  { background:#ffedd5; color:#ea580c; }
    .badge-alpha { background:var(--rose-light); color:var(--rose); }

    .avatar {
        width:32px; height:32px; border-radius:50%;
        background:var(--primary-light); color:var(--primary);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; flex-shrink:0;
    }

    .empty-state { text-align:center; padding:48px 20px; color:var(--text-3); }
    .empty-state i { font-size:32px; margin-bottom:10px; display:block; opacity:0.3; }

    .pagination-wrap {
        padding:14px 18px; border-top:1px solid var(--border);
        display:flex; align-items:center; justify-content:space-between;
        font-size:12.5px; color:var(--text-3);
    }

    .rate-bar-wrap {
        display:flex; align-items:center; gap:8px; width:120px;
    }
    .rate-bar {
        flex:1; height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden;
    }
    .rate-fill {
        height:100%; border-radius:3px;
    }

    /* Fix Laravel Default Tailwind Pagination */
    .pagination-wrap nav { width: 100%; }
    
    /* 1. Hide the mobile pagination wrapper completely (usually the first div in nav) */
    .pagination-wrap nav > div:first-of-type { display: none !important; }
    
    /* 2. Format the desktop pagination wrapper (usually the second div) */
    .pagination-wrap nav > div:last-of-type { 
        display: flex; justify-content: flex-end; align-items: center; width: 100%; 
    }
    
    /* 3. Hide the 'Showing X to Y' duplicate text from Laravel */
    .pagination-wrap nav > div:last-of-type > div:first-child { display: none !important; }
    
    /* 4. Format the container of the actual numbers */
    .pagination-wrap .relative.z-0.inline-flex { display: flex; gap: 6px; }
    
    /* 5. Style the links and numbers */
    .pagination-wrap nav a, 
    .pagination-wrap nav span[aria-disabled="true"] > span,
    .pagination-wrap nav span[aria-current="page"] > span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px; border-radius: 8px !important;
        border: 1px solid var(--border); font-size: 13px; font-weight: 600;
        color: var(--text-2); text-decoration: none; background: white; transition: all 0.15s;
    }
    .pagination-wrap nav a:hover { border-color: var(--primary); color: var(--primary); }
    
    /* 6. Active state */
    .pagination-wrap nav span[aria-current="page"] > span {
        background: var(--primary) !important; color: white !important; border-color: var(--primary) !important;
    }
    
    /* 7. Disabled state */
    .pagination-wrap nav span[aria-disabled="true"] > span {
        background: #f8fafc !important; color: #cbd5e1 !important; border-color: #e2e8f0 !important; cursor: not-allowed;
    }
    
    /* 8. Fix SVGs */
    .pagination-wrap svg { width: 16px; height: 16px; display: block; }

    @media print {
        .sidebar, .topbar, .tabs-wrap, .btn, .filter-row, .pagination-wrap { display: none !important; }
        .main { margin-left: 0 !important; }
        .content { margin: 0 !important; padding: 0 !important; max-width: none !important; }
        body { background: white !important; }
        .card { border: none !important; box-shadow: none !important; }
        .page-hd p { color: #000 !important; }
    }
</style>
@endpush

@section('content')

<div class="tabs-wrap">
    <a href="{{ route('admin.presensi.rekap') }}" class="tab-item">
        <i class="fas fa-calendar-day"></i> Rekap Harian
    </a>
    <a href="{{ route('admin.presensi.rekap-bulanan') }}" class="tab-item active">
        <i class="fas fa-calendar-alt"></i> Rekap Bulanan
    </a>
</div>

<div class="page-hd">
    <div>
        <h2>Rekap Presensi Bulanan</h2>
        <p>Akumulasi kehadiran karyawan dalam satu bulan</p>
    </div>
    <div style="display: flex; gap: 8px;">
        <button onclick="window.print()" class="btn btn-secondary btn-sm">
            <i class="fas fa-print"></i> Cetak PDF
        </button>
        <a href="{{ route('admin.presensi.export-bulanan', request()->all()) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-file-excel"></i> Export Excel/CSV
        </a>
    </div>
</div>

<div class="card">
    <div class="filter-row">
        <form method="GET" style="display:contents;">
            <input class="filter-input grow" name="search" placeholder="Cari nama atau NIK..."
                   value="{{ request('search') }}">
            
            <input type="month" class="filter-input" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()">

            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i> Filter</button>
            
            @if(request()->hasAny(['search','bulan']))
                <a href="{{ route('admin.presensi.rekap-bulanan') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-xmark"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Karyawan</th>
                    <th>NIK</th>
                    <th>Hadir</th>
                    <th>Izin</th>
                    <th>Sakit</th>
                    <th>Cuti</th>
                    <th>Alpha</th>
                    <th>Persentase Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawans as $i => $k)
                <tr>
                    <td style="color:var(--text-3);font-family:'DM Mono',monospace;font-size:12px;">
                        {{ $karyawans->firstItem() + $i }}
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="avatar">{{ $k->inisial }}</div>
                            <div>
                                <div class="td-name">{{ $k->nama }}</div>
                                <div style="font-size:11.5px;color:var(--text-3);">{{ $k->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="td-nik">{{ $k->nik }}</td>
                    <td>
                        <span class="badge-count badge-hadir">{{ $k->hadir_count }}</span>
                    </td>
                    <td>
                        <span class="badge-count badge-izin">{{ $k->izin_count }}</span>
                    </td>
                    <td>
                        <span class="badge-count badge-sakit">{{ $k->sakit_count }}</span>
                    </td>
                    <td>
                        <span class="badge-count badge-cuti">{{ $k->cuti_count }}</span>
                    </td>
                    <td>
                        <span class="badge-count badge-alpha">{{ $k->alpha_count }}</span>
                    </td>
                    <td>
                        <div class="rate-bar-wrap">
                            <div class="rate-bar">
                                <div class="rate-fill" style="width:{{ $k->rate }}%; background:{{ $k->rate >= 90 ? 'var(--emerald)' : ($k->rate >= 75 ? 'var(--amber)' : 'var(--rose)') }}"></div>
                            </div>
                            <strong style="font-family:'DM Mono',monospace; font-size:12.5px; color:{{ $k->rate >= 90 ? 'var(--emerald)' : ($k->rate >= 75 ? 'var(--amber)' : 'var(--rose)') }}">
                                {{ $k->rate }}%
                            </strong>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-user-slash"></i>
                            Tidak ada data karyawan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        <span>Menampilkan {{ $karyawans->firstItem() }}–{{ $karyawans->lastItem() }} dari {{ $karyawans->total() }} record</span>
        {{ $karyawans->links() }}
    </div>
</div>

@endsection
