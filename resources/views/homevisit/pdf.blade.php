<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Home Visit {{ $homeVisit->siswa->nama ?? '' }}</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.5; color: #111827; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 9px; margin-bottom: 18px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1 { text-align: center; font-size: 14pt; margin: 0 0 18px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 8px; vertical-align: top; }
        th { width: 28%; text-align: left; background: #f3f4f6; }
        .sign { margin-top: 42px; width: 45%; margin-left: auto; text-align: center; }
    </style>
</head>
<body>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>Catatan Kunjungan Rumah (Home Visit)</h1>
    <table>
        <tbody>
            <tr><th>Nama Siswa</th><td>{{ $homeVisit->siswa->nama ?? '-' }}</td></tr>
            <tr><th>NIS</th><td>{{ $homeVisit->siswa->nis ?? '-' }}</td></tr>
            <tr><th>Kelas</th><td>{{ $homeVisit->siswa->kelas->nama_kelas ?? '-' }}</td></tr>
            <tr><th>Tanggal</th><td>{{ \Carbon\Carbon::parse($homeVisit->tanggal)->translatedFormat('d F Y') }}</td></tr>
            <tr><th>Alamat Dikunjungi</th><td>{{ $homeVisit->alamat_dikunjungi ?: '-' }}</td></tr>
            <tr><th>Pihak yang Ditemui</th><td>{{ $homeVisit->pihak_ditemui ?: '-' }}</td></tr>
            <tr><th>Tujuan</th><td>{{ $homeVisit->tujuan }}</td></tr>
            <tr><th>Hasil Kunjungan</th><td>{{ $homeVisit->hasil ?: '-' }}</td></tr>
            <tr><th>Tindak Lanjut</th><td>{{ $homeVisit->tindak_lanjut ?: '-' }}</td></tr>
        </tbody>
    </table>
    <div class="sign">Guru Bimbingan Konseling,<br><br><br><br><u>{{ $guruBk->nama ?? '.................................' }}</u><br>NIP. {{ $guruBk->nip ?? '-' }}</div>
</body>
</html>
