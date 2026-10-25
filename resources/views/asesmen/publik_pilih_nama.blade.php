<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Isi Asesmen - SI-BK Digital</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gradient-to-br from-rose-50 to-pink-50 min-h-screen flex items-center justify-center px-4">
<div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
    <h1 class="text-lg font-bold text-rose-700 mb-1">{{ $link->instrumen->nama_instrumen }}</h1>
    <p class="text-sm text-slate-500 mb-4">Kelas {{ $link->kelas->nama_kelas }} — Pilih namamu di bawah</p>

    @if($errors->any())
    <div class="mb-3 p-3 bg-red-100 text-red-700 text-sm rounded">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('asesmen.publik.konfirmasi', $token) }}" class="space-y-3">
        @csrf
        <select name="siswa_id" required class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih Namamu --</option>
            @foreach($siswaSekelas as $s)<option value="{{ $s->id }}">{{ $s->nama }}</option>@endforeach
        </select>
        <div>
            <label class="block text-sm text-slate-600 mb-1">Konfirmasi tanggal lahirmu (biar tidak salah isi punya teman)</label>
            <input type="date" name="tanggal_lahir" required class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded">Lanjut</button>
    </form>
</div>
</body></html>
