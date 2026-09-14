@extends('layouts.admin')

@section('title', 'Kelola Izin, Sakit & Cuti')
@section('page-title', 'Kelola Izin, Sakit & Cuti')

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

    .filter-row {
        padding:12px 18px; border-bottom:1px solid var(--border);
        display:flex; gap:8px; flex-wrap:wrap; background:var(--surface-2);
        align-items:center;
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

    .badge {
        display:inline-flex; align-items:center;
        padding:3px 9px; border-radius:20px;
        font-size:11.5px; font-weight:600;
    }
    .badge-amber { background:var(--amber-light); color:var(--amber); }
    .badge-green { background:var(--emerald-light); color:var(--emerald); }
    .badge-rose  { background:var(--rose-light); color:var(--rose); }
    .badge-blue  { background:var(--primary-light); color:var(--primary); }

    .btn-approve {
        padding: 5px 12px; background: var(--emerald-light); color: var(--emerald);
        border: none; border-radius: 7px; font-size: 12px;
        font-weight: 600; cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.15s;
    }
    .btn-approve:hover { background: #a7f3d0; }

    .btn-reject {
        padding: 5px 12px; background: var(--rose-light); color: var(--rose);
        border: none; border-radius: 7px; font-size: 12px;
        font-weight: 600; cursor: pointer;
        font-family: 'DM Sans', sans-serif;
        transition: background 0.15s;
    }
    .btn-reject:hover { background: #fecdd3; }

    .empty-state { text-align:center; padding:48px 20px; color:var(--text-3); }
    .empty-state i { font-size:32px; margin-bottom:10px; display:block; opacity:0.3; }

    .pagination-wrap {
        padding:14px 18px; border-top:1px solid var(--border);
        display:flex; align-items:center; justify-content:space-between;
        font-size:12.5px; color:var(--text-3);
    }

    .stats-row {
        display:grid; grid-template-columns:repeat(3,1fr);
        gap:12px; margin-bottom:20px;
    }
    @media(max-width:900px) { .stats-row { grid-template-columns: 1fr; } }

    .stat-mini {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); padding:14px 16px;
        box-shadow:var(--shadow); display:flex; align-items:center; gap:12px;
    }

    .stat-mini-icon {
        width:36px; height:36px; border-radius:9px;
        display:flex; align-items:center; justify-content:center; font-size:14px;
        flex-shrink:0;
    }
    .stat-mini-icon.blue  { background:var(--primary-light); color:var(--primary); }
    .stat-mini-icon.green { background:var(--emerald-light); color:var(--emerald); }
    .stat-mini-icon.amber { background:var(--amber-light);   color:var(--amber); }
    .stat-mini-icon.rose  { background:var(--rose-light);    color:var(--rose); }

    .stat-mini-val  { font-size:20px; font-weight:700; color:var(--text); line-height:1; }
    .stat-mini-lbl  { font-size:11.5px; color:var(--text-3); margin-top:2px; }

    /* MODAL BUKTI */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(0,0,0,0.6); z-index: 999;
        align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal-box {
        background: white; border-radius: 14px;
        padding: 24px; max-width: 480px; width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }
    .modal-title { font-size: 15px; font-weight: 700; margin-bottom: 14px; color: #0f172a; }
    .modal-img { width: 100%; border-radius: 10px; border: 1px solid #e2e8f0; }
    .modal-close {
        margin-top: 16px; width: 100%;
        padding: 9px; background: #f1f5f9;
        border: none; border-radius: 8px;
        font-size: 13px; font-weight: 600;
        cursor: pointer; color: #64748b;
        font-family: 'DM Sans', sans-serif;
    }
    .modal-close:hover { background: #e2e8f0; }
</style>
@endpush

@section('content')

<div class="page-hd">
    <div>
        <h2>Pengajuan Izin, Sakit & Cuti</h2>
        <p>Review dan kelola persetujuan izin, sakit, dan cuti karyawan</p>
    </div>
</div>

@if(session('success'))
<div style="background:var(--emerald-light); color:var(--emerald); border:1px solid #a7f3d0; padding:12px 16px; border-radius:10px; margin-bottom:20px; font-size:13px; display:flex; align-items:center; gap:8px;">
    <i class="fas fa-circle-check"></i>
    <strong>{{ session('success') }}</strong>
</div>
@endif

<!-- Stats -->
@php
    $totalPengajuan = $pengajuan->total();
    $disetujui = \App\Models\Presensi::whereNotNull('status_izin')->where('status_izin', 'approved')->count();
    $pending   = \App\Models\Presensi::whereNotNull('status_izin')->where('status_izin', 'pending')->count();
    $ditolak   = \App\Models\Presensi::whereNotNull('status_izin')->where('status_izin', 'rejected')->count();
@endphp
<div class="stats-row" style="grid-template-columns:repeat(4,1fr);">
    <div class="stat-mini">
        <div class="stat-mini-icon blue"><i class="fas fa-file-medical"></i></div>
        <div>
            <div class="stat-mini-val">{{ $totalPengajuan }}</div>
            <div class="stat-mini-lbl">Total Pengajuan</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon green"><i class="fas fa-circle-check"></i></div>
        <div>
            <div class="stat-mini-val">{{ $disetujui }}</div>
            <div class="stat-mini-lbl">Disetujui</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon rose"><i class="fas fa-circle-xmark"></i></div>
        <div>
            <div class="stat-mini-val">{{ $ditolak }}</div>
            <div class="stat-mini-lbl">Ditolak</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon amber"><i class="fas fa-clock"></i></div>
        <div>
            <div class="stat-mini-val">{{ $pending }}</div>
            <div class="stat-mini-lbl">Menunggu Review</div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="filter-row">
        <form method="GET" action="{{ route('admin.izin') }}" style="display:contents;">
            <input type="date" name="tanggal" class="filter-input" value="{{ request('tanggal') }}" onchange="this.form.submit()">
            
            <input class="filter-input grow" name="search" placeholder="Cari nama atau NIK..." value="{{ request('search') }}">
            
            <select class="filter-input" name="jenis" onchange="this.form.submit()">
                <option value="">Semua Jenis</option>
                <option value="izin" {{ request('jenis') == 'izin' ? 'selected' : '' }}>Izin Only</option>
                <option value="sakit" {{ request('jenis') == 'sakit' ? 'selected' : '' }}>Sakit Only</option>
                <option value="cuti" {{ request('jenis') == 'cuti' ? 'selected' : '' }}>Cuti Only</option>
            </select>
            
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            
            @if(request()->hasAny(['tanggal', 'search', 'jenis']))
                <a href="{{ route('admin.izin') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-xmark"></i> Clear
                </a>
            @endif
        </form>
    </div>

    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal</th>
                    <th>Karyawan</th>
                    <th>NIK</th>
                    <th>Jenis</th>
                    <th>Keterangan</th>
                    <th>Bukti</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pengajuan as $i => $p)
                <tr>
                    <td style="color:var(--text-3);font-family:'DM Mono',monospace;font-size:12px;">
                        {{ $pengajuan->firstItem() + $i }}
                    </td>
                    <td style="font-family:'DM Mono',monospace;font-size:11px;color:var(--text-2);">
                        @if($p->status === 'cuti' && $p->cuti_start && $p->cuti_end)
                            <div style="font-weight:600;color:var(--primary);white-space:nowrap;">
                                {{ \Carbon\Carbon::parse($p->cuti_start)->format('d/m/Y') }} <span style="font-weight:400;color:var(--text-3);">s/d</span> {{ \Carbon\Carbon::parse($p->cuti_end)->format('d/m/Y') }}
                            </div>
                        @else
                            {{ $p->created_at->format('d/m/Y') }}<br>
                            <span style="color:var(--text-3);">{{ $p->created_at->format('H:i') }}</span>
                        @endif
                    </td>
                    <td class="td-name">{{ $p->karyawan->nama ?? '-' }}</td>
                    <td class="td-nik">{{ $p->karyawan->nik ?? '-' }}</td>
                    <td>
                        @php
                            // Deteksi jenis izin asli, termasuk dari tag [DITOLAK:xxx] jika ditolak
                            $jenisAsli = $p->status;
                            if ($p->status === 'alpha' && preg_match('/\[DITOLAK:(\w+)\]/', $p->keterangan ?? '', $mJenis)) {
                                $jenisAsli = $mJenis[1];
                            }
                        @endphp
                        @if($jenisAsli === 'sakit')
                            <span class="badge badge-rose"><i class="fas fa-hospital" style="margin-right:4px;font-size:9px;"></i>Sakit</span>
                        @elseif($jenisAsli === 'cuti')
                            <span class="badge badge-amber" style="background:#ffedd5;color:#ea580c;"><i class="fas fa-calendar-minus" style="margin-right:4px;font-size:9px;"></i>Cuti</span>
                        @else
                            <span class="badge badge-blue"><i class="fas fa-file-alt" style="margin-right:4px;font-size:9px;"></i>Izin</span>
                        @endif
                    </td>
                    <td style="max-width:220px;color:var(--text-2);word-wrap:break-word;white-space:normal;">
                        {{-- Bersihkan tag [DITOLAK:xxx] dari tampilan --}}
                        {{ trim(preg_replace('/\[DITOLAK:\w+\]\s*/', '', $p->keterangan ?? '-')) ?: '-' }}
                        @if($p->status_izin === 'rejected' && $p->izin_reject_reason)
                            <div style="font-size:11px;color:var(--rose);margin-top:4px;">
                                <strong>Alasan Tolak:</strong> {{ $p->izin_reject_reason }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($p->bukti)
                            <button class="btn btn-secondary btn-sm" onclick="showBukti('{{ asset('storage/' . $p->bukti) }}')">
                                <i class="fas fa-image"></i> Lihat
                            </button>
                        @else
                            <span style="color:var(--text-3);font-size:12px;">Tidak ada</span>
                        @endif
                    </td>
                    <td>
                        @if($p->status_izin === 'approved')
                            <span class="badge badge-green">
                                <i class="fas fa-check" style="margin-right:4px;font-size:9px;"></i>Disetujui
                            </span>
                        @elseif($p->status_izin === 'rejected')
                            <span class="badge badge-rose">
                                <i class="fas fa-x" style="margin-right:4px;font-size:9px;"></i>Ditolak
                            </span>
                        @else
                            <span class="badge badge-amber">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;">
                            @if($p->status_izin !== 'approved')
                            <form method="POST" action="{{ route('admin.izin.approve', $p->id) }}">
                                @csrf
                                <button type="submit" class="btn-approve" title="Setujui pengajuan ini">
                                    <i class="fas fa-check"></i> Setuju
                                </button>
                            </form>
                            @endif

                            @if($p->status_izin !== 'rejected')
                            <form method="POST" action="{{ route('admin.izin.reject', $p->id) }}" onsubmit="return handleReject(this, '{{ $p->id }}')">
                                @csrf
                                <button type="submit" class="btn-reject" title="Tolak pengajuan ini">
                                    <i class="fas fa-x"></i> Tolak
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <i class="fas fa-file-medical"></i>
                            Belum ada pengajuan (izin/sakit/cuti)
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        <span>Menampilkan {{ $pengajuan->firstItem() ?? 0 }}–{{ $pengajuan->lastItem() ?? 0 }} dari {{ $pengajuan->total() }} pengajuan</span>
        {{ $pengajuan->links() }}
    </div>
</div>

<!-- MODAL BUKTI -->
<div class="modal-overlay" id="modalBukti">
    <div class="modal-box">
        <div class="modal-title"><i class="fas fa-image" style="margin-right:8px;color:var(--primary);"></i>Bukti Pengajuan</div>
        <img src="" id="modalImg" class="modal-img" alt="Bukti">
        <button class="modal-close" onclick="closeBukti()">Tutup</button>
    </div>
</div>

<script>
function showBukti(url) {
    document.getElementById('modalImg').src = url;
    document.getElementById('modalBukti').classList.add('open');
}
function closeBukti() {
    document.getElementById('modalBukti').classList.remove('open');
}
document.getElementById('modalBukti').addEventListener('click', function(e) {
    if (e.target === this) closeBukti();
});

function handleReject(form, id) {
    const reason = prompt("Masukkan alasan penolakan:");
    if (reason === null) return false; // user cancelled
    if (reason.trim() === "") {
        alert("Alasan penolakan harus diisi!");
        return false;
    }
    
    // Add reason to form
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'reason';
    input.value = reason;
    form.appendChild(input);
    return true;
}
</script>

@endsection