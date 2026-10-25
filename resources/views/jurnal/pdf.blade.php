<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Jurnal Layanan BK</title>
    <style>
        @page { margin: 1.4cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #292429; }
        .catatan { color: #713047; font-size: 8pt; margin-bottom: 8px; }
        .catatan-bawah { color: #713047; font-size: 8pt; margin-top: 12px; }
        .kop { text-align: center; border-bottom: 2px solid #713047; padding-bottom: 9px; margin-bottom: 15px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1, .subjudul, .tanggal { text-align: center; margin: 0 0 10px; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 6px; vertical-align: top; }
        th { background: #fce9ef; color: #292429; text-align: left; }
    </style>
</head>
<body>
    <p class="catatan">BK hadir, mendengar, membimbing, menginspirasi.</p>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>{{ $judul ?? 'JURNAL HARIAN GURU BIMBINGAN DAN KONSELING' }}</h1>
    <p class="subjudul">
        @if(!empty($labelPeriode))
            Periode: {{ $labelPeriode }}
        @else
            Periode: {{ $filter['tanggal_mulai'] ?? 'awal data' }} s/d {{ $filter['tanggal_selesai'] ?? 'akhir data' }}
        @endif
    </p>
    <p class="tanggal">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead><tr><th>1. Hari / Tanggal</th><th>2. Jenis Kegiatan / Layanan BK</th><th>3. Sasaran Layanan</th><th>4. Uraian Kegiatan</th></tr></thead>
        <tbody>
            @forelse($jurnal as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                    <td>{{ $item->kegiatan }}</td>
                    <td>{{ $item->sasaran ?: '-' }}</td>
                    <td>{{ $item->uraian ?: trim(($item->hasil ?? '') . "\n" . ($item->keterangan ?? '')) ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada jurnal pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    <p class="catatan-bawah">Setiap langkah kecil hari ini, adalah perubahan besar untuk esok hari.</p>
</body>
</html>
