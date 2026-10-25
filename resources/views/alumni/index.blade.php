@extends('layouts.app')
@section('title', 'Alumni')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Alumni, Kelulusan &amp; Kenaikan Kelas</h1>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">⚠️ {{ session('error') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

@if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
<div class="grid md:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold mb-1">Kelulusan Kelas 9</h2>
        <p class="text-xs text-slate-500 mb-3">Semua siswa aktif di kelas yang dipilih menjadi alumni sekaligus. Kalau ada satu yang gagal, semuanya dibatalkan.</p>
        <form method="POST" action="{{ route('alumni.lulus') }}" class="space-y-2"
              onsubmit="return confirm('Yakin meluluskan seluruh siswa aktif di kelas ini? Status mereka akan menjadi alumni.')">
            @csrf
            <select name="kelas_id" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                <option value="">-- Pilih kelas tingkat 9 --</option>
                @foreach($kelasLulus as $k)<option value="{{ $k->id }}" @selected(old('kelas_id')==$k->id)>{{ $k->nama_kelas }} ({{ $k->tahun_ajaran }})</option>@endforeach
            </select>
            <input type="number" name="tahun_lulus" value="{{ old('tahun_lulus', now()->year) }}" min="2000" max="2100" required
                   class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm" placeholder="Tahun lulus">
            <button class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2 rounded text-sm">Luluskan Kelas</button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold mb-1">Kenaikan Kelas</h2>
        <p class="text-xs text-slate-500 mb-3">Semua siswa aktif di kelas asal dipindah ke kelas tujuan (harus tepat satu tingkat di atasnya).</p>
        <form method="POST" action="{{ route('alumni.naik') }}" class="space-y-2"
              onsubmit="return confirm('Yakin memindahkan seluruh siswa aktif ke kelas tujuan?')">
            @csrf
            <select name="kelas_asal" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                <option value="">-- Kelas asal (tingkat 7 atau 8) --</option>
                @foreach($kelasAsal as $k)<option value="{{ $k->id }}" @selected(old('kelas_asal')==$k->id)>{{ $k->nama_kelas }} ({{ $k->tahun_ajaran }})</option>@endforeach
            </select>
            <select name="kelas_tujuan" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                <option value="">-- Kelas tujuan (tingkat 8 atau 9) --</option>
                @foreach($kelasTujuan as $k)<option value="{{ $k->id }}" @selected(old('kelas_tujuan')==$k->id)>{{ $k->nama_kelas }} ({{ $k->tahun_ajaran }})</option>@endforeach
            </select>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded text-sm">Naikkan Kelas</button>
        </form>
    </div>
</div>
@endif

<div class="flex items-center justify-between mb-3">
    <h2 class="font-semibold text-lg">Data Alumni</h2>
    <div class="flex gap-2">
    <form method="GET">
        <select name="tahun_lulus" onchange="this.form.submit()" class="border border-slate-300 rounded px-2 py-1.5 text-sm">
            <option value="">Semua tahun lulus</option>
            @foreach($daftarTahun as $t)<option value="{{ $t }}" @selected(request('tahun_lulus')==$t)>{{ $t }}</option>@endforeach
        </select>
    </form>
    <a href="{{ route('alumni.cetak', request()->only('tahun_lulus')) }}" target="_blank" class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded text-sm">Cetak PDF</a>
    </div>
</div>
<p class="text-xs text-slate-500 mb-3">📌 Tabel ini diisi OTOMATIS oleh trigger database begitu status siswa berubah menjadi alumni, bukan diinput manual.</p>

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">NIS</th><th class="p-3">Nama</th><th class="p-3">Kelas Terakhir</th><th class="p-3">Tahun Lulus</th></tr>
        </thead>
        <tbody>
            @forelse($alumni as $a)
            <tr class="border-t">
                <td class="p-3">{{ $a->siswa->nis ?? '-' }}</td>
                <td class="p-3">{{ $a->siswa->nama ?? '-' }}</td>
                <td class="p-3">{{ $a->siswa->kelas->nama_kelas ?? '-' }}</td>
                <td class="p-3">{{ $a->tahun_lulus }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada alumni.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $alumni->links() }}</div>
@endsection
