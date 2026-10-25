<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap {{ ucfirst($tipe) }} Siswa</title>
    <style>
        @page { margin: 1.4cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 9px; margin-bottom: 15px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1, .periode, .tanggal { text-align: center; margin: 0 0 10px; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 6px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
        .angka { text-align: center; }
    </style>
</head>
<body>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>Rekap {{ ucfirst($tipe) }} Siswa</h1>
    <p class="periode">Periode: {{ $filter['tanggal_mulai'] ?? 'awal data' }} s/d {{ $filter['tanggal_selesai'] ?? 'akhir data' }}</p>
    <p class="tanggal">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead><tr><th>No.</th><th>Tanggal</th><th>Siswa</th><th>Kelas</th><th>Jenis</th><th class="angka">Poin</th><th>Keterangan</th></tr></thead>
        <tbody>
            @forelse($poin as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->tipe === 'pelanggaran' ? ($item->jenisPelanggaran->nama_pelanggaran ?? '-') : ($item->jenisReward->nama_reward ?? '-') }}</td>
                    <td class="angka">{{ $item->poin }}</td>
                    <td>{{ $item->keterangan ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Tidak ada data {{ $tipe }} pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
