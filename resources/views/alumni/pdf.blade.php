<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Data Alumni</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 18px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1, .subjudul, .tanggal { text-align: center; margin: 0 0 10px; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 7px; }
        th { background: #e5e7eb; text-align: left; }
    </style>
</head>
<body>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>Rekap Data Alumni</h1>
    <p class="subjudul">Tahun Lulus: {{ $tahunLulus ?? 'Semua Tahun' }}</p>
    <p class="tanggal">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead><tr><th>No.</th><th>NIS</th><th>Nama</th><th>Kelas Terakhir</th><th>Tahun Lulus</th></tr></thead>
        <tbody>
            @forelse($alumni as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->siswa->nis ?? '-' }}</td>
                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->tahun_lulus }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada data alumni.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
