@extends('layouts.admin')

@section('title', 'Data Karyawan')
@section('page-title', 'Data Karyawan')

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
    .btn-danger { background:var(--rose-light); color:var(--rose); border:1px solid #fecdd3; }
    .btn-danger:hover { background:var(--rose); color:white; }
    .btn-sm { padding:5px 11px; font-size:12px; border-radius:7px; }

    .card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); box-shadow:var(--shadow); overflow:hidden;
    }

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

    .badge {
        display:inline-flex; align-items:center;
        padding:3px 9px; border-radius:20px;
        font-size:11.5px; font-weight:600;
    }
    .badge-aktif    { background:var(--emerald-light); color:var(--emerald); }
    .badge-cuti     { background:var(--amber-light);   color:var(--amber); }
    .badge-resign   { background:#e2e8f0; color:#475569; }
    .badge-suspend  { background:var(--rose-light);    color:var(--rose); }

    .face-badge {
        display:inline-flex; align-items:center; gap:4px;
        padding:3px 8px; border-radius:20px; font-size:11px; font-weight:600;
    }
    .face-enrolled { background:var(--emerald-light); color:var(--emerald); }
    .face-none     { background:#f1f5f9; color:var(--text-3); }

    .action-btn {
        width:28px; height:28px; border-radius:7px;
        border:1px solid var(--border); background:var(--surface-2);
        color:var(--text-3); font-size:12px; cursor:pointer;
        display:inline-flex; align-items:center; justify-content:center;
        transition:all 0.15s; text-decoration:none;
    }
    .action-btn:hover { border-color:var(--primary); color:var(--primary); }
    .action-btn.del:hover { border-color:var(--rose); color:var(--rose); }

    .avatar {
        width:32px; height:32px; border-radius:50%;
        background:var(--primary-light); color:var(--primary);
        display:inline-flex; align-items:center; justify-content:center;
        font-size:12px; font-weight:700; flex-shrink:0;
    }

    .empty-state { text-align:center; padding:48px 20px; color:var(--text-3); }
    .empty-state i { font-size:32px; margin-bottom:10px; display:block; opacity:0.3; }

    .pagination-wrap {
        padding: 14px 18px; border-top: 1px solid var(--border);
        display: flex; align-items: center; justify-content: space-between;
        font-size: 12.5px; color: var(--text-3);
    }
    .pagination {
        display: flex; list-style: none; margin: 0; padding: 0; gap: 4px;
    }
    .pagination li { display: inline-block; }
    .pagination li a, .pagination li span {
        padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border);
        color: var(--text-2); text-decoration: none; font-size: 12px; background: white;
    }
    .pagination li.active span {
        background: var(--primary); color: white; border-color: var(--primary);
    }
    .pagination li.disabled span {
        opacity: 0.5; background: #f8fafc; cursor: not-allowed;
    }
    .pagination li a:hover {
        background: var(--surface-2); border-color: var(--primary); color: var(--primary);
    }

    .stats-row {
        display:grid; grid-template-columns:repeat(4,1fr);
        gap:12px; margin-bottom:20px;
    }
    @media(max-width:900px) { .stats-row { grid-template-columns:repeat(2,1fr); } }

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
</style>
@endpush

@section('content')

<div class="page-hd">
    <div>
        <h2>Data Karyawan</h2>
        <p>Kelola data dan wajah karyawan terdaftar</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.karyawan.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Karyawan
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-row">
    <div class="stat-mini">
        <div class="stat-mini-icon blue"><i class="fas fa-users"></i></div>
        <div>
            <div class="stat-mini-val">{{ $karyawans->total() }}</div>
            <div class="stat-mini-lbl">Total Karyawan</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon green"><i class="fas fa-face-smile"></i></div>
        <div>
            <div class="stat-mini-val">{{ $totalEnrolled ?? 0 }}</div>
            <div class="stat-mini-lbl">Sudah Enroll Wajah</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon amber"><i class="fas fa-user-clock"></i></div>
        <div>
            <div class="stat-mini-val">{{ $totalAktif ?? 0 }}</div>
            <div class="stat-mini-lbl">Karyawan Aktif</div>
        </div>
    </div>
    <div class="stat-mini">
        <div class="stat-mini-icon rose"><i class="fas fa-user-xmark"></i></div>
        <div>
            <div class="stat-mini-val">{{ $totalBelumEnroll ?? 0 }}</div>
            <div class="stat-mini-lbl">Belum Enroll</div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card">
    <div class="filter-row">
        <form method="GET" style="display:contents;">
            <input class="filter-input grow" name="search" placeholder="Cari nama atau NIK..."
                   value="{{ request('search') }}">
            <select class="filter-input" name="divisi" onchange="this.form.submit()">
                <option value="">Semua Divisi</option>
                @foreach($divisis ?? [] as $p)
                    <option value="{{ $p }}" {{ request('divisi') == $p ? 'selected' : '' }}>{{ $p }}</option>
                @endforeach
            </select>
            <select class="filter-input" name="status" onchange="this.form.submit()">
                <option value="">Semua Status</option>
                <option value="aktif"   {{ request('status') == 'aktif'   ? 'selected' : '' }}>Aktif</option>
                <option value="cuti"    {{ request('status') == 'cuti'    ? 'selected' : '' }}>Cuti</option>
                <option value="resign"  {{ request('status') == 'resign'  ? 'selected' : '' }}>Resign</option>
                <option value="suspend" {{ request('status') == 'suspend' ? 'selected' : '' }}>Suspend</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
            @if(request()->hasAny(['search','divisi','status']))
                <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-xmark"></i>
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
                    <th>Divisi</th>
                    <th>Status</th>
                    <th>Wajah</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($karyawans as $i => $m)
                <tr>
                    <td style="color:var(--text-3);font-family:'DM Mono',monospace;font-size:12px;">
                        {{ $karyawans->firstItem() + $i }}
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="avatar">{{ $m->inisial }}</div>
                            <div>
                                <div class="td-name">{{ $m->nama }}</div>
                                <div style="font-size:11.5px;color:var(--text-3);">{{ $m->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="td-nik">{{ $m->nik }}</td>
                    <td style="font-size:12.5px;color:var(--text-2);">
                        {{ $m->divisi }}<br>
                        <span style="font-size:11px;color:var(--text-3);">{{ $m->jabatan }}</span>
                    </td>

                    <td>
                        <span class="badge badge-{{ $m->status_karyawan }}">
                            {{ ucfirst($m->status_karyawan) }}
                        </span>
                    </td>
                    <td>
                        @if($m->face_enrolled)
                            <span class="face-badge face-enrolled">
                                <i class="fas fa-check"></i> {{ $m->face_sample_count }} sampel
                            </span>
                        @else
                            <span class="face-badge face-none">
                                <i class="fas fa-xmark"></i> Belum
                            </span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.karyawan.show', $m) }}" class="action-btn" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.karyawan.edit', $m) }}" class="action-btn" title="Edit">
                                <i class="fas fa-pen"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.karyawan.destroy', $m) }}"
                                  onsubmit="return confirm('Hapus karyawan {{ $m->nama }}?')" style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" class="action-btn del" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            Belum ada data karyawan
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        <span>Menampilkan {{ $karyawans->firstItem() }}–{{ $karyawans->lastItem() }} dari {{ $karyawans->total() }} karyawan</span>
        {{ $karyawans->links('pagination::bootstrap-4') }}
    </div>
</div>

@endsection