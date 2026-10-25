@extends('layouts.app')
@section('title', 'Tambah Catatan Anekdot')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Tambah Catatan Anekdot</h1>
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('anekdot.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4 max-w-2xl">
    @csrf
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Siswa *</label>
            <select name="siswa_id" required class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="">-- Pilih --</option>
                @foreach($siswa as $s)<option value="{{ $s->id }}" @selected(old('siswa_id')==$s->id)>{{ $s->nama }} ({{ $s->nis }})</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal *</label>
            <input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Kategori *</label>
        <select name="kategori" required class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="ringan">Ringan</option>
            <option value="sedang">Sedang</option>
            <option value="berat">Berat (otomatis notif ke wali kelas)</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Kejadian *</label>
        <textarea name="kejadian" rows="4" required class="w-full border border-slate-300 rounded px-3 py-2">{{ old('kejadian') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tindak Lanjut</label>
        <textarea name="tindak_lanjut" rows="2" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('tindak_lanjut') }}</textarea>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan</button>
        <a href="{{ route('anekdot.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
@endsection
