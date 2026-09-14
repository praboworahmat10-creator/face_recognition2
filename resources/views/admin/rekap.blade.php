@extends('layouts.admin')

@section('title', 'Rekap Presensi')
@section('page-title', 'Rekap Presensi')

@push('styles')
<style>
    .page-hd {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 1.5rem;
    }
    .page-hd h2 { font-size: 1.15rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.03em; }
    .page-hd p  { font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem; }

    /* ── TABS ── */
    .tabs-wrap {
        display: flex; gap: 4px; margin-bottom: 1.5rem;
        background: var(--bg); padding: 4px; border-radius: 10px;
        width: fit-content; border: 1px solid var(--border);
    }
    .tab-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 0.55rem 1.1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600;
        color: var(--text-muted); transition: all 0.2s; cursor: pointer;
        border: none; background: none; font-family: 'Inter', sans-serif;
    }
    .tab-btn:hover { color: var(--primary); }
    .tab-btn.active {
        background: var(--surface); color: var(--primary);
        box-shadow: var(--shadow-sm);
    }

    .tab-panel { display: none; }
    .tab-panel.active { display: block; }

    /* ── TABLE CARD ── */
    .table-section {
        background: var(--surface); border: 1px solid var(--border);
        border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden;
    }
    .filter-row {
        padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
        display: flex; gap: 0.5rem; flex-wrap: wrap; background: var(--bg);
        align-items: center;
    }

    .avatar {
        width: 32px; height: 32px; border-radius: 50%;
        background: var(--primary-light); color: var(--primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.75rem; font-weight: 700; flex-shrink: 0;
    }

    .td-nik { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--primary); }
    .td-name { font-weight: 600; color: var(--text-main); }

    .badge-hadir { background: var(--success-light); color: var(--success-dark); }
    .badge-izin  { background: var(--warning-light); color: var(--warning-dark); }
    .badge-sakit { background: #E0F2FE; color: #0284C7; }
    .badge-cuti  { background: #FFEDD5; color: #EA580C; }
    .badge-alpha { background: var(--danger-light); color: var(--danger-dark); }

    .badge-count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 28px; height: 28px; padding: 0 0.4rem; border-radius: 14px;
        font-size: 0.8rem; font-weight: 700; font-family: 'JetBrains Mono', monospace;
    }

    .action-btn {
        width: 30px; height: 30px; border-radius: 8px;
        border: 1px solid var(--border); background: var(--surface);
        color: var(--text-muted); font-size: 0.75rem; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        transition: all 0.15s;
    }
    .action-btn:hover { border-color: var(--primary); color: var(--primary); }

    .pagination-wrap {
        padding: 1rem 1.5rem; border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        font-size: 0.8rem; color: var(--text-muted);
    }

    .rate-bar-wrap { display: flex; align-items: center; gap: 0.5rem; width: 130px; }
    .rate-bar { flex: 1; height: 6px; background: var(--border); border-radius: 3px; overflow: hidden; }
    .rate-fill { height: 100%; border-radius: 3px; }

    .empty-state { text-align: center; padding: 3rem 1.5rem; color: var(--text-muted); }
    .empty-state i { font-size: 2rem; margin-bottom: 0.75rem; display: block; opacity: 0.3; }

    /* ── PAGINATION FIX ── */
    .pagination-wrap nav { width: 100%; }
    .pagination-wrap nav > div:first-of-type { display: none !important; }
    .pagination-wrap nav > div:last-of-type { display: flex; justify-content: flex-end; align-items: center; width: 100%; }
    .pagination-wrap nav > div:last-of-type > div:first-child { display: none !important; }
    .pagination-wrap .relative.z-0.inline-flex { display: flex; gap: 6px; }
    .pagination-wrap nav a,
    .pagination-wrap nav span[aria-disabled="true"] > span,
    .pagination-wrap nav span[aria-current="page"] > span {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 32px; height: 32px; padding: 0 8px; border-radius: 8px !important;
        border: 1px solid var(--border); font-size: 0.8rem; font-weight: 600;
        color: var(--text-muted); text-decoration: none; background: white; transition: all 0.15s;
    }
    .pagination-wrap nav a:hover { border-color: var(--primary); color: var(--primary); }
    .pagination-wrap nav span[aria-current="page"] > span {
        background: var(--primary) !important; color: white !important; border-color: var(--primary) !important;
    }
    .pagination-wrap nav span[aria-disabled="true"] > span {
        background: var(--bg) !important; color: var(--border) !important; border-color: var(--border) !important; cursor: not-allowed;
    }
    .pagination-wrap svg { width: 16px; height: 16px; display: block; }

    @media print {
        .sidebar, .topbar, .tabs-wrap, .btn, .filter-row, .pagination-wrap { display: none !important; }
        .main { margin-left: 0 !important; }
        .content { margin: 0 !important; padding: 0 !important; max-width: none !important; }
        body { background: white !important; }
        .table-section { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')

{{-- ── TAB SWITCHER ── --}}
<div class="tabs-wrap">
    <button class="tab-btn active" onclick="switchTab('harian', this)">
        <i class="fa fa-calendar-day"></i> Rekap Harian
    </button>
    <button class="tab-btn" onclick="switchTab('bulanan', this)">
        <i class="fa fa-calendar-alt"></i> Rekap Bulanan
    </button>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- TAB 1: REKAP HARIAN                                --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div id="panel-harian" class="tab-panel active">
    <div class="page-hd">
        <div>
            <h2>Rekap Presensi Karyawan</h2>
            <p>Log history kehadiran karyawan per transaksi</p>
        </div>
        <a href="{{ route('admin.presensi.export', request()->all()) }}" class="btn btn-primary" style="font-size:0.85rem;">
            <i class="fa fa-file-excel"></i> Export Excel/CSV
        </a>
    </div>

    <div class="table-section">
        <div class="filter-row">
            <form method="GET" style="display:contents;">
                <input type="hidden" name="tab" value="harian">
                <input class="form-control" style="flex:1; min-width:180px;" name="search" placeholder="Cari nama atau NIK..."
                       value="{{ request('search') }}">
                <input type="date" class="form-control" style="width:auto;" name="tanggal" value="{{ request('tanggal') }}" onchange="this.form.submit()">
                <select class="form-control" style="width:auto;" name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                    <option value="izin"  {{ request('status') == 'izin'  ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="cuti"  {{ request('status') == 'cuti'  ? 'selected' : '' }}>Cuti</option>
                    <option value="alpha" {{ request('status') == 'alpha' ? 'selected' : '' }}>Alpha</option>
                </select>
                <button type="submit" class="btn btn-secondary" style="font-size:0.8rem;"><i class="fa fa-search"></i> Filter</button>
                @if(request()->hasAny(['search','tanggal','status']))
                    <a href="{{ route('admin.presensi.rekap') }}" class="btn btn-secondary" style="font-size:0.8rem;">
                        <i class="fa fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Karyawan</th>
                        <th>NIK</th>
                        <th>Tanggal</th>
                        <th>Waktu Datang</th>
                        <th>Waktu Pulang</th>
                        <th>Confidence</th>
                        <th>Jarak</th>
                        <th>Status</th>
                        <th style="width:50px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presensis as $i => $p)
                    <tr>
                        <td class="mono" style="color:var(--text-light); font-size:0.8rem;">
                            {{ $presensis->firstItem() + $i }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar">{{ $p->karyawan ? $p->karyawan->inisial : substr($p->nama, 0, 1) }}</div>
                                <div>
                                    <div class="td-name">{{ $p->nama }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $p->karyawan ? $p->karyawan->email : '' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="td-nik">{{ $p->nik }}</td>
                        <td style="font-size:0.8rem;color:var(--text-muted);white-space:nowrap;">
                            @if($p->status === 'cuti' && $p->cuti_start && $p->cuti_end && !request()->filled('tanggal'))
                                {{ \Carbon\Carbon::parse($p->cuti_start)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($p->cuti_end)->format('d/m/Y') }}
                            @else
                                {{ $p->tanggal }}
                            @endif
                        </td>
                        <td class="mono" style="font-size:0.8rem;">{{ $p->waktu_datang_format }}</td>
                        <td class="mono" style="font-size:0.8rem;">{{ $p->waktu_pulang_format }}</td>
                        <td class="mono" style="font-size:0.8rem; color:{{ $p->face_confidence >= 0.8 ? 'var(--success)' : 'var(--text-main)' }}">
                            {{ $p->face_confidence ? ($p->confidence_pct . '%') : '—' }}
                        </td>
                        <td style="font-size:0.8rem;color:var(--text-muted);">{{ $p->jarak_label }}</td>
                        <td>
                            <span class="badge badge-{{ $p->status }}">
                                @if($p->status == 'hadir')
                                    <i class="fa fa-check"></i>
                                @elseif($p->status == 'izin')
                                    <i class="fa fa-exclamation-triangle"></i>
                                @elseif($p->status == 'sakit')
                                    <i class="fa fa-notes-medical"></i>
                                @elseif($p->status == 'cuti')
                                    <i class="fa fa-plane"></i>
                                @else
                                    <i class="fa fa-times"></i>
                                @endif
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td>
                            <button class="action-btn" title="Detail"
                                    onclick="openDetailModal(this)"
                                    data-nama="{{ $p->nama }}"
                                    data-nik="{{ $p->nik }}"
                                    data-status="{{ $p->status }}"
                                    data-waktu="{{ $p->status === 'cuti' && $p->cuti_start && $p->cuti_end ? \Carbon\Carbon::parse($p->cuti_start)->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($p->cuti_end)->translatedFormat('d F Y') : (($p->waktu_datang ?? $p->created_at) ? \Carbon\Carbon::parse($p->waktu_datang ?? $p->created_at)->translatedFormat('d F Y, H:i:s') : '—') }}"
                                    data-bukti="{{ $p->bukti ? asset('storage/' . $p->bukti) : '' }}"
                                    data-bukti-pulang="{{ $p->bukti_pulang ? asset('storage/' . $p->bukti_pulang) : '' }}"
                                    data-keterangan="{{ $p->keterangan ?? '' }}">
                                <i class="fa fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <i class="fa fa-clipboard-list"></i>
                                Belum ada rekap presensi yang sesuai filter
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>Menampilkan {{ $presensis->firstItem() ?? 0 }}–{{ $presensis->lastItem() ?? 0 }} dari {{ $presensis->total() }} record</span>
            {{ $presensis->links() }}
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════ --}}
{{-- TAB 2: REKAP BULANAN                               --}}
{{-- ═══════════════════════════════════════════════════ --}}
<div id="panel-bulanan" class="tab-panel">
    <div class="page-hd">
        <div>
            <h2>Rekap Presensi Bulanan</h2>
            <p>Akumulasi kehadiran karyawan dalam satu bulan</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="window.print()" class="btn btn-secondary" style="font-size:0.85rem;">
                <i class="fa fa-print"></i> Cetak
            </button>
            <a href="{{ route('admin.presensi.export-bulanan', request()->all()) }}" class="btn btn-primary" style="font-size:0.85rem;">
                <i class="fa fa-file-excel"></i> Export Excel/CSV
            </a>
        </div>
    </div>

    <div class="table-section">
        <div class="filter-row">
            <form method="GET" style="display:contents;">
                <input type="hidden" name="tab" value="bulanan">
                <input class="form-control" style="flex:1; min-width:180px;" name="search_bulanan" placeholder="Cari nama atau NIK..."
                       value="{{ request('search_bulanan') }}">
                <input type="month" class="form-control" style="width:auto;" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()">
                <button type="submit" class="btn btn-secondary" style="font-size:0.8rem;"><i class="fa fa-search"></i> Filter</button>
                @if(request()->hasAny(['search_bulanan','bulan']))
                    <a href="{{ route('admin.presensi.rekap', ['tab' => 'bulanan']) }}" class="btn btn-secondary" style="font-size:0.8rem;">
                        <i class="fa fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px;">#</th>
                        <th>Karyawan</th>
                        <th>NIK</th>
                        <th style="text-align:center;">Hadir</th>
                        <th style="text-align:center;">Izin</th>
                        <th style="text-align:center;">Sakit</th>
                        <th style="text-align:center;">Cuti</th>
                        <th style="text-align:center;">Alpha</th>
                        <th>Persentase Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($karyawans as $i => $k)
                    <tr>
                        <td class="mono" style="color:var(--text-light); font-size:0.8rem;">
                            {{ $karyawans->firstItem() + $i }}
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div class="avatar">{{ $k->inisial }}</div>
                                <div>
                                    <div class="td-name">{{ $k->nama }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $k->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="td-nik">{{ $k->nik }}</td>
                        <td style="text-align:center;"><span class="badge-count badge-hadir">{{ $k->hadir_count }}</span></td>
                        <td style="text-align:center;"><span class="badge-count badge-izin">{{ $k->izin_count }}</span></td>
                        <td style="text-align:center;"><span class="badge-count badge-sakit">{{ $k->sakit_count }}</span></td>
                        <td style="text-align:center;"><span class="badge-count badge-cuti">{{ $k->cuti_count }}</span></td>
                        <td style="text-align:center;"><span class="badge-count badge-alpha">{{ $k->alpha_count }}</span></td>
                        <td>
                            <div class="rate-bar-wrap">
                                <div class="rate-bar">
                                    <div class="rate-fill" style="width:{{ $k->rate }}%; background:{{ $k->rate >= 90 ? 'var(--success)' : ($k->rate >= 75 ? 'var(--warning)' : 'var(--danger)') }}"></div>
                                </div>
                                <strong class="mono" style="font-size:0.8rem; color:{{ $k->rate >= 90 ? 'var(--success)' : ($k->rate >= 75 ? 'var(--warning)' : 'var(--danger)') }}">
                                    {{ $k->rate }}%
                                </strong>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fa fa-user-slash"></i>
                                Tidak ada data karyawan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            <span>Menampilkan {{ $karyawans->firstItem() ?? 0 }}–{{ $karyawans->lastItem() ?? 0 }} dari {{ $karyawans->total() }} karyawan</span>
            {{ $karyawans->links() }}
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(tab, btn) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Update tab panels
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
}

// Auto-switch to correct tab based on URL param
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab === 'bulanan') {
        const bulananBtn = document.querySelectorAll('.tab-btn')[1];
        switchTab('bulanan', bulananBtn);
    }
});
</script>
@endpush
