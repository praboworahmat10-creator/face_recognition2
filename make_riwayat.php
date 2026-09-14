<?php
$content = file_get_contents('resources/views/presensi/index.blade.php');

// Get everything up to `<main class="main">`
$pos = strpos($content, '<main class="main">');
if ($pos !== false) {
    $header = substr($content, 0, $pos + strlen('<main class="main">'));
} else {
    die("Could not find main tag\n");
}

$riwayatContent = <<<HTML

    <div class="page-header">
        <h1>Riwayat Presensi</h1>
        <p>Lihat detail kehadiran harian dan ringkasan bulanan Anda</p>
    </div>

    <div class="content" style="max-width: 1000px; margin: 0 auto;">
        
        <div style="display:flex; gap:10px; margin-bottom: 20px;">
            <a href="?tab=harian" class="btn {{ \$tab == 'harian' ? 'btn-primary' : 'btn-secondary' }}">Riwayat Harian</a>
            <a href="?tab=bulanan" class="btn {{ \$tab == 'bulanan' ? 'btn-primary' : 'btn-secondary' }}">Rekap Bulanan</a>
        </div>

        @if(\$tab == 'harian')
            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <div class="card-icon blue"><i class="fas fa-calendar-day"></i></div>
                        <span class="card-title">30 Hari Terakhir</span>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                            <thead>
                                <tr style="background:var(--surface-2); border-bottom:1px solid var(--border);">
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Tanggal</th>
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Jam Masuk</th>
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Jam Pulang</th>
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\$harian as \$h)
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <td style="padding:12px 16px; color:var(--text); font-weight:500;">{{ \Carbon\Carbon::parse(\$h->created_at)->format('d M Y') }}</td>
                                        <td style="padding:12px 16px; color:var(--text-2); font-family:monospace;">{{ \$h->waktu_datang ?? '-' }}</td>
                                        <td style="padding:12px 16px; color:var(--text-2); font-family:monospace;">{{ \$h->waktu_pulang ?? '-' }}</td>
                                        <td style="padding:12px 16px;">
                                            @if(\$h->status == 'hadir')
                                                <span style="background:var(--emerald-lt); color:var(--emerald); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Hadir</span>
                                            @elseif(\$h->status == 'izin')
                                                <span style="background:var(--amber-lt); color:var(--amber); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Izin</span>
                                            @elseif(\$h->status == 'sakit')
                                                <span style="background:rgba(225,29,72,.15); color:var(--rose); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Sakit</span>
                                            @elseif(\$h->status == 'cuti')
                                                <span style="background:rgba(217,119,6,.15); color:var(--amber); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Cuti</span>
                                            @else
                                                <span style="background:var(--rose-lt); color:var(--rose); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">{{ \$h->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding:30px; text-align:center; color:var(--text-3);">Belum ada riwayat presensi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <form method="GET" style="margin-bottom: 20px; display:flex; gap:10px;">
                <input type="hidden" name="tab" value="bulanan">
                <input type="month" name="bulan" value="{{ \$bulan }}" style="padding:8px 12px; border:1px solid var(--border); border-radius:8px; font-family:'DM Sans'; font-size:13px; outline:none;">
                <button type="submit" class="btn btn-secondary">Filter Bulan</button>
            </form>

            <div class="metrics-grid" style="margin-bottom: 20px;">
                <div class="metric-box">
                    <div class="metric-val" style="color:var(--emerald);">{{ \$summary['hadir'] }}</div>
                    <div class="metric-lbl">Hadir</div>
                </div>
                <div class="metric-box">
                    <div class="metric-val" style="color:var(--amber);">{{ \$summary['izin'] }}</div>
                    <div class="metric-lbl">Izin</div>
                </div>
                <div class="metric-box">
                    <div class="metric-val" style="color:var(--rose);">{{ \$summary['sakit'] }}</div>
                    <div class="metric-lbl">Sakit</div>
                </div>
                <div class="metric-box">
                    <div class="metric-val" style="color:#ef4444;">{{ \$summary['alpha'] }}</div>
                    <div class="metric-lbl">Alpha</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-header-left">
                        <div class="card-icon green"><i class="fas fa-list"></i></div>
                        <span class="card-title">Detail Bulan Ini</span>
                    </div>
                </div>
                <div class="card-body" style="padding:0;">
                    <div style="overflow-x:auto;">
                        <table style="width:100%; border-collapse:collapse; text-align:left; font-size:13px;">
                            <thead>
                                <tr style="background:var(--surface-2); border-bottom:1px solid var(--border);">
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Tanggal</th>
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Jam Masuk</th>
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Jam Pulang</th>
                                    <th style="padding:12px 16px; font-weight:600; color:var(--text-2);">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(\$bulanan as \$b)
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <td style="padding:12px 16px; color:var(--text); font-weight:500;">{{ \Carbon\Carbon::parse(\$b->created_at)->format('d M Y') }}</td>
                                        <td style="padding:12px 16px; color:var(--text-2); font-family:monospace;">{{ \$b->waktu_datang ?? '-' }}</td>
                                        <td style="padding:12px 16px; color:var(--text-2); font-family:monospace;">{{ \$b->waktu_pulang ?? '-' }}</td>
                                        <td style="padding:12px 16px;">
                                            @if(\$b->status == 'hadir')
                                                <span style="background:var(--emerald-lt); color:var(--emerald); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Hadir</span>
                                            @elseif(\$b->status == 'izin')
                                                <span style="background:var(--amber-lt); color:var(--amber); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Izin</span>
                                            @elseif(\$b->status == 'sakit')
                                                <span style="background:rgba(225,29,72,.15); color:var(--rose); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Sakit</span>
                                            @elseif(\$b->status == 'cuti')
                                                <span style="background:rgba(217,119,6,.15); color:var(--amber); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">Cuti</span>
                                            @else
                                                <span style="background:var(--rose-lt); color:var(--rose); padding:3px 8px; border-radius:6px; font-weight:600; font-size:11px; text-transform:uppercase;">{{ \$b->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" style="padding:30px; text-align:center; color:var(--text-3);">Tidak ada data di bulan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

</main>
<script>
    function updateClock() {
        const now = new Date();
        document.getElementById('liveClock').textContent = 
            [now.getHours(), now.getMinutes(), now.getSeconds()].map(n => String(n).padStart(2, '0')).join(':');
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
</body>
</html>
HTML;

file_put_contents('resources/views/presensi/riwayat.blade.php', $header . $riwayatContent);
echo "Created riwayat.blade.php\n";
?>
