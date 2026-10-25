@extends('layouts.app')
@section('title', 'Buat Surat')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Buat Surat Baru</h1>
<p class="text-sm text-slate-500 mb-4">📌 Nomor surat dibuat OTOMATIS saat disimpan (tidak akan bentrok walau dibuat bersamaan).</p>

<form method="POST" action="{{ route('surat.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4 max-w-2xl">
    @csrf
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Jenis Surat *</label>
            <select name="jenis_surat_id" required class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="">-- Pilih --</option>
                @foreach($jenisSurat as $j)<option value="{{ $j->id }}" @selected(old('jenis_surat_id', $jenisTerpilih) == $j->id)>{{ $j->nama_surat }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Siswa *</label>
            <select name="siswa_id" required class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="">-- Pilih --</option>
                @foreach($siswa as $s)<option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }})</option>@endforeach
            </select>
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Tanggal *</label>
        <input type="date" name="tanggal" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Perihal</label>
        <input type="text" name="perihal" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Isi Surat</label>
        <textarea name="isi" rows="6" class="w-full border border-slate-300 rounded px-3 py-2"
            placeholder="Isi lengkap menyusul setelah template asli dikirim Guru BK. Sementara bisa diketik manual dulu."></textarea>
    </div>
    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan & Buat Nomor</button>
        <a href="{{ route('surat.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
@endsection
