<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji — {{ $slipGaji->karyawan->nama }} ({{ $slipGaji->periode }})</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #1e293b;
            background: #f1f5f9;
            padding: 30px 15px;
            font-size: 13px;
            line-height: 1.5;
        }

        .no-print-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }
        .btn-print {
            background: #4f46e5;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-print:hover {
            background: #4338ca;
        }
        .btn-close {
            background: #f8fafc;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .payslip-paper {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            border-radius: 4px;
        }

        /* HEADER */
        .kop-surat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .kop-kiri {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-box {
            width: 50px;
            height: 50px;
            background: #4f46e5;
            color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
        }
        .kop-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.01em;
        }
        .kop-title p {
            font-size: 11px;
            color: #64748b;
        }
        .kop-kanan {
            text-align: right;
        }
        .doc-title {
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.05em;
        }
        .doc-no {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }

        /* INFO KARYAWAN */
        .karyawan-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-val {
            color: #0f172a;
            font-weight: 700;
            text-align: right;
        }

        /* REKAP PRESENSI */
        .presensi-strip {
            display: flex;
            justify-content: space-between;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        .p-item span {
            display: block;
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .p-item strong {
            font-size: 13px;
            color: #0f172a;
            font-family: 'JetBrains Mono', monospace;
        }

        /* TABEL RINCIAN */
        .details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .section-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        .section-table th {
            text-align: left;
            padding: 8px 10px;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 2px solid #cbd5e1;
        }
        .section-table th:last-child {
            text-align: right;
        }
        .section-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .section-table td.amount {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }
        .section-table tr.total-row td {
            font-weight: 700;
            background: #f8fafc;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
        }

        /* TOTAL GAJI BERSIH */
        .net-pay-box {
            border: 2px solid #0f172a;
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fafafa;
        }
        .net-pay-left .label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #0f172a;
        }
        .net-pay-left .terbilang {
            font-size: 11px;
            font-style: italic;
            color: #475569;
            margin-top: 3px;
        }
        .net-pay-amount {
            font-size: 20px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            color: #0f172a;
        }

        /* CATATAN */
        .notes-box {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 30px;
            font-style: italic;
        }

        /* TANDA TANGAN */
        .signature-grid {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
            text-align: center;
        }
        .sign-col {
            width: 220px;
        }
        .sign-date {
            font-size: 11px;
            color: #475569;
            margin-bottom: 60px;
        }
        .sign-name {
            font-weight: 700;
            font-size: 12px;
            border-top: 1px solid #94a3b8;
            padding-top: 5px;
            color: #0f172a;
        }

        /* PRINT MEDIA */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .payslip-paper {
                box-shadow: none !important;
                border: none !important;
                padding: 15mm 15mm;
                max-width: 100%;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <div>
            <strong>Preview Cetak Slip Gaji</strong>
            <span style="font-size: 12px; color:#64748b; margin-left:8px;">{{ $slipGaji->karyawan->nama }} ({{ $slipGaji->periode }})</span>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="window.print()" class="btn-print">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v4a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>
                Cetak / Simpan PDF
            </button>
            <a href="javascript:window.close()" class="btn-close">Tutup</a>
        </div>
    </div>

    <div class="payslip-paper">
        {{-- KOP --}}
        <div class="kop-surat">
            <div class="kop-kiri">
                <div class="logo-box">K</div>
                <div class="kop-title">
                    <h1>KOPPOSINDO</h1>
                    <p>Koperasi Pegawai Pos Indonesia</p>
                    <p style="font-size: 10px; color:#94a3b8;">Jl. Banda No. 30, Bandung — Jawa Barat</p>
                </div>
            </div>
            <div class="kop-kanan">
                <div class="doc-title">SLIP GAJI KARYAWAN</div>
                <div class="doc-no">No: {{ $slipGaji->nomor_slip }}</div>
                <div style="font-size: 11px; color:#64748b; margin-top:2px;">Periode: {{ $slipGaji->periode }}</div>
            </div>
        </div>

        {{-- INFORMASI KARYAWAN --}}
        <div class="karyawan-grid">
            <div class="info-row">
                <span class="info-label">Nama Karyawan</span>
                <span class="info-val">{{ $slipGaji->karyawan->nama }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Divisi / Departemen</span>
                <span class="info-val">{{ $slipGaji->karyawan->divisi ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Nomor Induk Karyawan</span>
                <span class="info-val" style="font-family:'JetBrains Mono';">{{ $slipGaji->karyawan->nik }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Jabatan</span>
                <span class="info-val">{{ $slipGaji->karyawan->jabatan ?? '—' }}</span>
            </div>
        </div>

        {{-- REKAP PRESENSI BULAN INI --}}
        <div class="presensi-strip">
            <div class="p-item">
                <span>Hadir</span>
                <strong>{{ $slipGaji->total_hadir }} hari</strong>
            </div>
            <div class="p-item">
                <span>Izin</span>
                <strong>{{ $slipGaji->total_izin }} hari</strong>
            </div>
            <div class="p-item">
                <span>Sakit</span>
                <strong>{{ $slipGaji->total_sakit }} hari</strong>
            </div>
            <div class="p-item">
                <span>Cuti</span>
                <strong>{{ $slipGaji->total_cuti }} hari</strong>
            </div>
            <div class="p-item">
                <span>Alpa / Tanpa Ket.</span>
                <strong>{{ $slipGaji->total_alpa }} hari</strong>
            </div>
        </div>

        {{-- TABEL RINCIAN PENDAPATAN & POTONGAN --}}
        <div class="details-container">
            {{-- PENERIMAAN --}}
            <div>
                <table class="section-table">
                    <thead>
                        <tr>
                            <th>A. Penerimaan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Gaji Pokok</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->gaji_pokok) }}</td>
                        </tr>
                        <tr>
                            <td>Tunjangan Jabatan</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_jabatan) }}</td>
                        </tr>
                        <tr>
                            <td>Tunjangan Kehadiran</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_kehadiran) }}</td>
                        </tr>
                        <tr>
                            <td>Tunjangan Makan & Transport</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_transport_makan) }}</td>
                        </tr>
                        <tr>
                            <td>Bonus & Insentif</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->bonus) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Penerimaan (A)</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->total_pendapatan) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- POTONGAN --}}
            <div>
                <table class="section-table">
                    <thead>
                        <tr>
                            <th>B. Potongan</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>BPJS Kesehatan & TK</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_bpjs) }}</td>
                        </tr>
                        <tr>
                            <td>Potongan Keterlambatan</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_terlambat) }}</td>
                        </tr>
                        <tr>
                            <td>Potongan Lainnya / Alpa</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_lainnya) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td>Total Potongan (B)</td>
                            <td class="amount">{{ \App\Models\SlipGaji::rupiah($slipGaji->total_potongan) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- GAJI BERSIH --}}
        <div class="net-pay-box">
            <div class="net-pay-left">
                <div class="label">Penerimaan Bersih (Take Home Pay: A - B)</div>
                <div class="terbilang">Terbilang: # {{ ucwords(\App\Models\SlipGaji::angkaKeHuruf($slipGaji->gaji_bersih)) }} Rupiah #</div>
            </div>
            <div class="net-pay-amount">
                {{ $slipGaji->gaji_bersih_rupiah }}
            </div>
        </div>

        @if ($slipGaji->catatan)
            <div class="notes-box">
                * Catatan: {{ $slipGaji->catatan }}
            </div>
        @endif

        {{-- TANDA TANGAN --}}
        <div class="signature-grid">
            <div class="sign-col">
                <div class="sign-date">Penerima,</div>
                <div class="sign-name">{{ $slipGaji->karyawan->nama }}</div>
            </div>
            <div class="sign-col">
                <div class="sign-date">Bandung, {{ now()->translatedFormat('d F Y') }}<br>Pengurus KOPPOSINDO</div>
                <div class="sign-name">Bendahara / HRD</div>
            </div>
        </div>
    </div>

</body>
</html>
