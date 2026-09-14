@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Ringkasan Presensi')

@section('content')
<!-- Top Bar Actions (Optional, inject via another section or keep inline if needed, but we already have topbar in layout) -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <p style="color:var(--text-muted); font-size: 0.9rem;">Pantau aktivitas kehadiran karyawan secara real-time hari ini.</p>
    <div style="display:flex; gap:12px; align-items:center;">
        <div class="badge" style="background:var(--surface); border:1px solid var(--border); padding:0.5rem 1rem; border-radius:8px; font-weight:500; color:var(--text-muted);" id="dashDate">
            <i class="far fa-calendar-alt" style="margin-right:6px; color:var(--text-light);"></i> —
        </div>
        <a href="{{ route('admin.presensi.export') }}" class="btn btn-primary"><i class="fa fa-arrow-down"></i> Unduh Laporan</a>
    </div>
</div>

<!-- Stats -->
<style>
    /* Scoped styles for dashboard specific grids if needed */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }

    .stat-card { display: flex; flex-direction: column; position: relative; }
    .stat-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    
    .stat-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
    .stat-icon.blue { background: var(--primary-light); color: var(--primary); }
    .stat-icon.green { background: var(--success-light); color: var(--success-dark); }
    .stat-icon.orange { background: var(--warning-light); color: var(--warning-dark); }
    .stat-icon.red { background: var(--danger-light); color: var(--danger-dark); }

    .stat-label { font-size: 0.85rem; font-weight: 500; color: var(--text-muted); }
    .stat-value { font-size: 2rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.03em; line-height: 1; margin-top: 0.25rem; }
    .stat-change { font-size: 0.75rem; margin-top: 0.75rem; font-weight: 500; color: var(--text-light); }
    .stat-change.positive { color: var(--success); }
    .stat-change.negative { color: var(--danger); }

    /* CHARTS SECTION */
    .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; margin-bottom: 2rem; }
    
    /* Mini Bar Chart */
    .bar-chart-container { height: 160px; display: flex; align-items: flex-end; gap: 12px; margin-top: 1rem; }
    .bar-col { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; gap: 8px; height: 100%; }
    .bar-value { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }
    .bar-fill { width: 100%; background: linear-gradient(180deg, var(--primary) 0%, var(--primary-hover) 100%); border-radius: 6px 6px 0 0; min-height: 4px; transition: height 0.8s cubic-bezier(0.4, 0, 0.2, 1); }
    .bar-day { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

    /* Donut Chart */
    .donut-wrapper { display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .donut { position: relative; width: 140px; height: 140px; margin-bottom: 1.5rem; }
    .donut svg { transform: rotate(-90deg); width: 100%; height: 100%; }
    .donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    .donut-pct { font-size: 1.75rem; font-weight: 700; color: var(--text-main); letter-spacing: -0.03em; line-height: 1; }
    .donut-sub { font-size: 0.7rem; color: var(--text-muted); font-weight: 500; margin-top: 4px; }
    .donut-legend { width: 100%; display: flex; flex-direction: column; gap: 0.75rem; }
    .legend-row { display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem; }
    .legend-label { display: flex; align-items: center; gap: 8px; color: var(--text-muted); }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
    .legend-val { font-weight: 600; color: var(--text-main); }
    
    .table-section { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); overflow: hidden; }
    .table-header-wrap { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .filter-bar { padding: 1rem 1.5rem; background: var(--bg); border-bottom: 1px solid var(--border); display: flex; gap: 12px; }
    
    .td-name { font-weight: 600; }
    .td-nik { font-size: 0.8rem; color: var(--text-muted); }
    .conf-pill { background: var(--bg); border: 1px solid var(--border); border-radius: 6px; padding: 0.2rem 0.5rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); }
</style>

<div class="stats-grid">
    <div class="card stat-card" style="margin-bottom:0;">
        <div class="stat-card-header">
            <div>
                <div class="stat-label">Total Karyawan</div>
                <div class="stat-value">{{ $totalKaryawan ?? 0 }}</div>
            </div>
            <div class="stat-icon blue"><i class="fa fa-users"></i></div>
        </div>
        <div class="stat-change">Total karyawan terdaftar aktif</div>
    </div>
    
    <div class="card stat-card" style="margin-bottom:0;">
        <div class="stat-card-header">
            <div>
                <div class="stat-label">Hadir Hari Ini</div>
                <div class="stat-value">{{ $hadirHariIni ?? 0 }}</div>
            </div>
            <div class="stat-icon green"><i class="fa fa-check"></i></div>
        </div>
        <div class="stat-change positive"><i class="fa fa-arrow-up"></i> Berdasarkan presensi wajah</div>
    </div>
    
    <div class="card stat-card" style="margin-bottom:0;">
        <div class="stat-card-header">
            <div>
                <div class="stat-label">Izin / Sakit</div>
                <div class="stat-value">{{ $izinSakit ?? 0 }}</div>
            </div>
            <div class="stat-icon orange"><i class="fa fa-clock"></i></div>
        </div>
        <div class="stat-change">Status disetujui admin</div>
    </div>
    
    <div class="card stat-card" style="margin-bottom:0;">
        <div class="stat-card-header">
            <div>
                <div class="stat-label">Belum Presensi</div>
                <div class="stat-value">{{ $alphaHariIni ?? 0 }}</div>
            </div>
            <div class="stat-icon red"><i class="fa fa-times"></i></div>
        </div>
        <div class="stat-change negative"><i class="fa fa-exclamation-circle"></i> Membutuhkan perhatian</div>
    </div>
</div>

<!-- Charts -->
<div class="charts-grid">
    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div class="card-title">Aktivitas Presensi (7 Hari Terakhir)</div>
        </div>
        <div class="bar-chart-container" id="barChart">
            <!-- Bar Chart dipopulate oleh JavaScript -->
        </div>
    </div>
    
    <div class="card" style="margin-bottom:0;">
        <div class="card-header" style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div class="card-title">Rasio Kehadiran Hari Ini</div>
        </div>
        <div class="donut-wrapper">
            <div class="donut">
                @php
                    $total = ($hadirHariIni ?? 0) + ($izinSakit ?? 0) + ($alphaHariIni ?? 0);
                    $total = $total > 0 ? $total : 1;
                    $pctHadir = round(($hadirHariIni ?? 0) / $total * 100);
                    $pctIzin  = round(($izinSakit ?? 0) / $total * 100);
                    $pctAlpha = round(($alphaHariIni ?? 0) / $total * 100);
                @endphp
                <svg viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--border)" stroke-width="3"/>
                    
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--success)" stroke-width="3"
                        stroke-dasharray="{{ $pctHadir }} {{ 100 - $pctHadir }}" stroke-linecap="round"/>
                        
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--warning)" stroke-width="3"
                        stroke-dasharray="{{ $pctIzin }} {{ 100 - $pctIzin }}" stroke-dashoffset="-{{ $pctHadir }}" stroke-linecap="round"/>
                        
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="var(--danger)" stroke-width="3"
                        stroke-dasharray="{{ $pctAlpha }} {{ 100 - $pctAlpha }}" stroke-dashoffset="-{{ $pctHadir + $pctIzin }}" stroke-linecap="round"/>
                </svg>
                <div class="donut-center">
                    <div class="donut-pct">{{ $pctHadir }}%</div>
                    <div class="donut-sub">Hadir</div>
                </div>
            </div>
            
            <div class="donut-legend">
                <div class="legend-row">
                    <div class="legend-label"><div class="legend-dot" style="background:var(--success)"></div> Hadir</div>
                    <div class="legend-val mono">{{ $hadirHariIni ?? 0 }}</div>
                </div>
                <div class="legend-row">
                    <div class="legend-label"><div class="legend-dot" style="background:var(--warning)"></div> Izin/Sakit</div>
                    <div class="legend-val mono">{{ $izinSakit ?? 0 }}</div>
                </div>
                <div class="legend-row">
                    <div class="legend-label"><div class="legend-dot" style="background:var(--danger)"></div> Alpha/Absen</div>
                    <div class="legend-val mono">{{ $alphaHariIni ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Presensi Table -->
<div class="table-section">
    <div class="table-header-wrap">
        <h2 class="card-title">Log Presensi Karyawan</h2>
        <button class="btn btn-secondary btn-sm" onclick="location.reload()"><i class="fa fa-sync-alt"></i> Segarkan</button>
    </div>
    <div class="filter-bar">
        <div style="position:relative; flex:1; max-width: 300px;">
            <i class="fa fa-search" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--text-light); font-size:0.8rem;"></i>
            <input id="searchInput" class="form-control" placeholder="Cari nama atau NIK..." style="padding-left:32px;" oninput="filterTable()">
        </div>
        <select id="statusFilter" class="form-control" style="width: auto;" onchange="filterTable()">
            <option value="">Semua Status</option>
            <option value="hadir">Hadir</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
            <option value="cuti">Cuti</option>
            <option value="alpha">Alpha</option>
        </select>
    </div>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:50px; text-align:center;">No</th>
                    <th>Karyawan</th>
                    <th>Waktu Presensi</th>
                    <th>Akurasi FaceID</th>
                    <th>Jarak (GPS)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="presensiTable">
                @forelse($presensiHariIni ?? [] as $index => $row)
                    @php
                        if(!is_object($row)) continue;
                        $badgeClass = 'badge-alpha';
                        $badgeIcon = 'fa-times';
                        $badgeLabel = 'Alpha';
                        if($row->status == 'hadir') { $badgeClass = 'badge-hadir'; $badgeIcon = 'fa-check'; $badgeLabel = 'Hadir'; }
                        elseif($row->status == 'izin') { $badgeClass = 'badge-izin'; $badgeIcon = 'fa-envelope'; $badgeLabel = 'Izin'; }
                        elseif($row->status == 'sakit') { $badgeClass = 'badge-sakit'; $badgeIcon = 'fa-notes-medical'; $badgeLabel = 'Sakit'; }
                        elseif($row->status == 'cuti') { $badgeClass = 'badge-cuti'; $badgeIcon = 'fa-plane'; $badgeLabel = 'Cuti'; }
                    @endphp
                    <tr class="table-row" data-nama="{{ strtolower($row->nama) }}" data-nik="{{ $row->nik }}" data-status="{{ $row->status }}">
                        <td class="mono" style="text-align:center; color:var(--text-light); font-size:0.8rem;">{{ $index + 1 }}</td>
                        <td>
                            <div class="td-name">{{ $row->nama }}</div>
                            <div class="td-nik mono">{{ $row->nik }}</div>
                        </td>
                        <td>
                            @if($row->status === 'cuti' && $row->cuti_start && $row->cuti_end)
                                <span style="font-size:0.85rem; color:var(--text-muted);">
                                    {{ \Carbon\Carbon::parse($row->cuti_start)->format('d M Y') }} - {{ \Carbon\Carbon::parse($row->cuti_end)->format('d M Y') }}
                                </span>
                            @elseif(in_array($row->status, ['izin', 'sakit', 'alpha']))
                                <span style="color:var(--border);">—</span>
                            @else
                                <span class="mono" style="font-weight:500;">
                                    {{ $row->waktu_datang ? \Carbon\Carbon::parse($row->waktu_datang)->format('H:i:s') : ($row->created_at ? $row->created_at->format('H:i:s') : '—') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($row->face_confidence)
                                <span class="conf-pill mono">{{ round($row->face_confidence * 100) }}%</span>
                            @else
                                <span style="color:var(--border);">—</span>
                            @endif
                        </td>
                        <td>
                            @if($row->distance_meter)
                                <span class="mono" style="font-size:0.85rem; color:var(--text-muted);">
                                    <i class="fa fa-map-marker-alt" style="color:var(--primary); margin-right:4px;"></i> 
                                    {{ round($row->distance_meter) }}m
                                </span>
                            @else
                                <span style="color:var(--border);">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $badgeClass }}">
                                <i class="fa {{ $badgeIcon }}"></i> {{ $badgeLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 3rem 1rem; color: var(--text-muted);">
                            <div style="font-size:2rem; margin-bottom:1rem; color:var(--border);"><i class="fa fa-folder-open"></i></div>
                            Belum ada data presensi untuk hari ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Date Formatter for the top action bar
const dashboardDate = new Date();
const dashOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
document.getElementById('dashDate').innerHTML = `<i class="far fa-calendar-alt" style="margin-right:6px; color:var(--primary);"></i> ${dashboardDate.toLocaleDateString('id-ID', dashOptions)}`;

// Bar chart data dari backend
const days = {!! json_encode($chartDays ?? ['Sen','Sel','Rab','Kam','Jum','Sab','Min']) !!};
const hadirData = [{{ implode(',', $chartHadir ?? [0,0,0,0,0,0,0]) }}];
const maxVal = Math.max(...hadirData, 5); // Minimum ceiling of 5 to avoid flat charts

const barChart = document.getElementById('barChart');
days.forEach((d, i) => {
    const pct = Math.round((hadirData[i] / maxVal) * 90); 
    
    const col = document.createElement('div');
    col.className = 'bar-col';
    
    col.innerHTML = `
        <div class="bar-value mono">${hadirData[i] || '0'}</div>
        <div class="bar-fill" style="height: 0%;" data-target="${pct}%"></div>
        <div class="bar-day">${d}</div>
    `;
    barChart.appendChild(col);
});

// Trigger bar animation
setTimeout(() => {
    document.querySelectorAll('.bar-fill').forEach(bar => {
        bar.style.height = bar.getAttribute('data-target');
    });
}, 100);

// Table Filtering
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    
    document.querySelectorAll('.table-row').forEach(row => {
        const nama = row.getAttribute('data-nama');
        const nik = row.getAttribute('data-nik');
        const rowStatus = row.getAttribute('data-status');
        
        const matchSearch = nama.includes(search) || nik.includes(search);
        const matchStatus = status === "" || rowStatus === status;
        
        if(matchSearch && matchStatus) {
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>
@endpush