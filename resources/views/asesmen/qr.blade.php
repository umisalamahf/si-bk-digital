<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>QR Asesmen - {{ $link->kelas->nama_kelas ?? '' }}</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  @media print {
    .no-print { display: none !important; }
    body { background: #fff !important; }
    .kartu { box-shadow: none !important; border: 2px solid #be185d; }
  }
  .qr svg { width: 100%; height: auto; display: block; }
</style>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col items-center justify-center px-4 py-6">

@if($hanyaLokal)
<div class="no-print max-w-md mb-4 p-3 bg-amber-100 text-amber-800 text-sm rounded">
    ⚠️ Alamat di bawah memakai <b>{{ parse_url($url, PHP_URL_HOST) }}</b>, yang hanya bisa dibuka di laptop ini.
    HP siswa <b>tidak bisa</b> membukanya. Buka aplikasi lewat alamat jaringan (misalnya
    <code>http://192.168.x.x:8000</code>) atau lewat alamat hosting, lalu buat ulang halaman QR ini.
</div>
@endif

@if(! $link->masihBerlaku())
<div class="no-print max-w-md mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    ⚠️ Link ini sudah tidak berlaku ({{ $link->alasanTidakBerlaku() }}). Buat link baru di menu Asesmen.
</div>
@endif

<div class="kartu bg-white rounded-2xl shadow-lg p-8 w-full max-w-md text-center">
    <p class="text-xs tracking-widest text-rose-700 font-semibold mb-1">SI-BK DIGITAL</p>
    <h1 class="text-xl font-bold text-slate-800">{{ $link->instrumen->nama_instrumen ?? 'Asesmen' }}</h1>
    <p class="text-slate-600 mb-5">Kelas {{ $link->kelas->nama_kelas ?? '-' }}</p>

    <div class="qr mx-auto" style="width: 280px;">{!! $svg !!}</div>

    <p class="mt-5 font-medium text-slate-800">Scan dengan kamera HP,<br>lalu pilih namamu dan isi tanggal lahirmu.</p>
    <p class="mt-3 text-xs text-slate-400 break-all">{{ $url }}</p>
    <p class="mt-2 text-xs text-slate-500">Berlaku sampai {{ $link->kadaluarsa->format('d-m-Y H:i') }}</p>
</div>

<div class="no-print mt-5 flex gap-3">
    <button onclick="window.print()" class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Cetak</button>
    <a href="{{ route('asesmen.link.index') }}" class="px-5 py-2 rounded border border-slate-300 bg-white">Kembali</a>
</div>

</body>
</html>
