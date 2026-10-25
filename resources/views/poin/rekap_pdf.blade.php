<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Poin Siswa</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 18px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1 { text-align: center; font-size: 14pt; margin: 0 0 4px; }
        .subjudul, .tanggal-cetak { text-align: center; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 6px; }
        th { background: #e5e7eb; text-align: left; }
        .angka { text-align: center; }
    </style>
</head>
<body>
    <div class="kop">
        <strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>
        {{ $sekolah->alamat ?? '' }}
    </div>
    <h1>Rekap Poin Siswa</h1>
    <p class="subjudul">Status: {{ $status ? str_replace('_', ' ', $status) : 'Semua Status' }}</p>
    <p class="tanggal-cetak">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead>
            <tr><th>No.</th><th>Nama Siswa</th><th>Kelas</th><th>Tahun Ajaran</th><th class="angka">Pelanggaran</th><th class="angka">Reward</th><th class="angka">Bersih</th><th>Status</th></tr>
        </thead>
        <tbody>
            @forelse($rekap as $index => $baris)
                <tr>
                    <td class="angka">{{ $index + 1 }}</td>
                    <td>{{ $baris->siswa->nama ?? '-' }}</td>
                    <td>{{ $baris->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $baris->tahun_ajaran }}</td>
                    <td class="angka">{{ $baris->total_pelanggaran }}</td>
                    <td class="angka">{{ $baris->total_reward }}</td>
                    <td class="angka">{{ $baris->total_bersih }}</td>
                    <td>{{ str_replace('_', ' ', $baris->status_tindak_lanjut) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="angka">Tidak ada data rekap poin.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
