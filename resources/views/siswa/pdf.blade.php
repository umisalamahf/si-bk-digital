<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Data Siswa</title>
    <style>
        @page { margin: 1.4cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 9px; margin-bottom: 15px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1, .tanggal { text-align: center; margin: 0 0 10px; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 5px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
    </style>
</head>
<body>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>Rekap Data Siswa</h1>
    <p class="tanggal">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead><tr><th>No.</th><th>NIS</th><th>NISN</th><th>Nama</th><th>JK</th><th>Kelas</th><th>Usia</th><th>Status</th></tr></thead>
        <tbody>
            @forelse($siswa as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->nis }}</td>
                    <td>{{ $item->nisn ?: '-' }}</td>
                    <td>{{ $item->nama }}</td>
                    <td>{{ $item->jenis_kelamin }}</td>
                    <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->usia === null ? '-' : $item->usia . ' th' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $item->status)) }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
