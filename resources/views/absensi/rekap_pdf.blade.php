<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi {{ $kelas->nama_kelas }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 18px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1 { text-align: center; font-size: 14pt; margin: 0 0 4px; }
        .subjudul, .tanggal-cetak { text-align: center; margin: 0 0 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 7px; }
        th { background: #e5e7eb; text-align: left; }
        .angka { text-align: center; }
    </style>
</head>
<body>
    <div class="kop">
        <strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>
        {{ $sekolah->alamat ?? '' }}
    </div>
    <h1>Rekap Absensi Siswa</h1>
    <p class="subjudul">Kelas {{ $kelas->nama_kelas }} — {{ \Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
    <p class="tanggal-cetak">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead>
            <tr><th>No.</th><th>Nama Siswa</th><th class="angka">Hadir</th><th class="angka">Sakit</th><th class="angka">Izin</th><th class="angka">Alpa</th></tr>
        </thead>
        <tbody>
            @forelse($rekap as $index => $baris)
                <tr>
                    <td class="angka">{{ $index + 1 }}</td>
                    <td>{{ $baris->nama }}</td>
                    <td class="angka">{{ $baris->hadir }}</td>
                    <td class="angka">{{ $baris->sakit }}</td>
                    <td class="angka">{{ $baris->izin }}</td>
                    <td class="angka">{{ $baris->alpa }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="angka">Tidak ada data siswa di kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
