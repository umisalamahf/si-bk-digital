<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>{{ $surat->nomor_surat }}</title>
<style>
  /* Catatan: dompdf TIDAK mendukung flexbox/grid, jadi tata letak pakai tabel. */
  @page { margin: 2cm 2.3cm 2cm 2.3cm; }
  body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.4; color: #000; }
  .kop { text-align: center; border-bottom: 3px double #000; padding-bottom: 6px; margin-bottom: 18px; }
  .kop .nama { font-size: 15pt; font-weight: bold; text-transform: uppercase; }
  .kop .alamat { font-size: 10.5pt; }
  table.meta td { padding: 1px 4px 1px 0; vertical-align: top; }
  table.meta td.k { width: 70px; }
  .judul { text-align: center; font-weight: bold; text-decoration: underline; margin: 14px 0 2px 0; text-transform: uppercase; }
  .isi { margin: 16px 0; text-align: left; white-space: pre-wrap; }
  table.ttd { width: 100%; margin-top: 36px; page-break-inside: avoid; }
  .identitas { margin: 18px 0; }
</style>
</head>
<body>

<div class="kop">
    <div class="nama">{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</div>
    <div class="alamat">{{ $sekolah->alamat ?? '' }}</div>
    @if($sekolah && $sekolah->npsn)
        <div class="alamat">NPSN: {{ $sekolah->npsn }}</div>
    @endif
</div>

<div class="judul">{{ $surat->jenisSurat->nama_surat }}</div>
<div style="text-align:center; margin-bottom: 14px;">Nomor: {{ $surat->nomor_surat }}</div>

@if($surat->jenisSurat->kode === 'SKB')
<p>Yang bertanda tangan di bawah ini, Guru Bimbingan dan Konseling {{ $sekolah->nama_sekolah ?? '' }}, menerangkan bahwa:</p>
<table class="meta identitas">
    <tr><td class="k">Nama</td><td>: {{ $surat->siswa->nama }}</td></tr>
    <tr><td class="k">NIS</td><td>: {{ $surat->siswa->nis }}</td></tr>
    @if($surat->siswa->nisn)<tr><td class="k">NISN</td><td>: {{ $surat->siswa->nisn }}</td></tr>@endif
    <tr><td class="k">Kelas</td><td>: {{ $surat->siswa->kelas->nama_kelas ?? '-' }}</td></tr>
    <tr><td class="k">Sekolah</td><td>: {{ $sekolah->nama_sekolah ?? '-' }}</td></tr>
</table>
<p>Berdasarkan catatan dan pertimbangan Guru Bimbingan dan Konseling, siswa tersebut dinyatakan berkelakuan baik. Surat keterangan ini dibuat untuk keperluan:</p>
<p style="margin-left: 24px;"><strong>{{ $surat->perihal }}</strong></p>
<p>Demikian surat keterangan ini dibuat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya.</p>
<p style="text-align:right; margin-top: 24px;">{{ $sekolah->nama_sekolah ?? '' }}, {{ $tanggalTampil }}</p>
@else
<table class="meta">
    <tr><td class="k">Perihal</td><td>: {{ $surat->perihal ?: '-' }}</td></tr>
    <tr><td class="k">Tanggal</td><td>: {{ $tanggalTampil }}</td></tr>
</table>

<p style="margin-top: 14px;">Kepada Yth. Orang Tua/Wali dari:</p>
<table class="meta">
    <tr><td class="k">Nama</td><td>: {{ $surat->siswa->nama }}</td></tr>
    <tr><td class="k">NIS</td><td>: {{ $surat->siswa->nis }}</td></tr>
    <tr><td class="k">Kelas</td><td>: {{ $surat->siswa->kelas->nama_kelas ?? '-' }}</td></tr>
</table>

<div class="isi">{{ $surat->isi ?: '[Isi surat belum diisi]' }}</div>
@endif

<table class="ttd">
    <tr>
        <td style="width:55%"></td>
        <td style="text-align:center">
            Guru Bimbingan Konseling,<br><br><br><br><br>
            <u>{{ $guruBk->nama ?? '.................................' }}</u><br>
            NIP. {{ $guruBk->nip ?? '-' }}
        </td>
    </tr>
</table>

</body>
</html>
