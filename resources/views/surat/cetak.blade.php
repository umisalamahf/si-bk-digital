<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8">
<title>{{ $surat->nomor_surat }}</title>
<style>
  body { font-family: 'Times New Roman', serif; font-size: 14px; max-width: 210mm; margin: 20px auto; padding: 0 20mm; }
  .kop { text-align: center; border-bottom: 3px solid black; padding-bottom: 10px; margin-bottom: 20px; }
  .info { margin-bottom: 20px; }
  table.info td { padding: 2px 8px 2px 0; vertical-align: top; }
  @media print { body { margin: 0; padding: 15mm; } }
</style></head>
<body>
<div class="kop">
    <h2 style="margin:0">{{ \Illuminate\Support\Facades\DB::table('sekolah')->value('nama_sekolah') }}</h2>
    <p style="margin:0">{{ \Illuminate\Support\Facades\DB::table('sekolah')->value('alamat') }}</p>
</div>

<table class="info">
    <tr><td>Nomor</td><td>: {{ $surat->nomor_surat }}</td></tr>
    <tr><td>Perihal</td><td>: {{ $surat->perihal }}</td></tr>
    <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($surat->tanggal)->translatedFormat('d F Y') }}</td></tr>
</table>

<p>Kepada Yth. Orang Tua/Wali dari:</p>
<table class="info">
    <tr><td>Nama</td><td>: {{ $surat->siswa->nama }}</td></tr>
    <tr><td>Kelas</td><td>: {{ $surat->siswa->kelas->nama_kelas ?? '-' }}</td></tr>
</table>

<div style="margin: 20px 0; white-space: pre-line;">{{ $surat->isi ?: '[Isi surat menyusul -- template resmi belum dikirim Guru BK]' }}</div>

<table style="width:100%; margin-top: 60px;">
    <tr>
        <td style="width:50%"></td>
        <td style="width:50%; text-align:center">
            Guru BK,<br><br><br><br>
            <u>( ................................. )</u>
        </td>
    </tr>
</table>

<script>window.onload = () => window.print();</script>
</body></html>
