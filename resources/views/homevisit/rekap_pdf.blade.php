<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Home Visit</title>
    <style>
        @page { margin: 1.3cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 9px; margin-bottom: 15px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1, .periode, .tanggal { text-align: center; margin: 0 0 9px; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 5px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
    </style>
</head>
<body>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>Rekap Home Visit</h1>
    <p class="periode">Periode: {{ $filter['tanggal_mulai'] ?? 'awal data' }} s/d {{ $filter['tanggal_selesai'] ?? 'akhir data' }}</p>
    <p class="tanggal">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead><tr><th>No.</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Alamat</th><th>Pihak Ditemui</th><th>Tujuan</th><th>Hasil</th><th>Tindak Lanjut</th></tr></thead>
        <tbody>
            @forelse($kunjungan as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->alamat_dikunjungi ?: '-' }}</td>
                    <td>{{ $item->pihak_ditemui ?: '-' }}</td>
                    <td>{{ $item->tujuan }}</td>
                    <td>{{ $item->hasil ?: '-' }}</td>
                    <td>{{ $item->tindak_lanjut ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Tidak ada data home visit pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
