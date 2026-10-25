@extends('layouts.app')
@section('title', 'Identitas Sekolah')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-1">Identitas Sekolah</h1>
<p class="text-slate-500 mb-4">Data ini dipakai sebagai kop pada surat PDF.</p>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('sekolah.update') }}" class="bg-white rounded-xl shadow p-6 space-y-4 max-w-2xl">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium mb-1">Nama Sekolah *</label>
        <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $sekolah->nama_sekolah ?? '') }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">NPSN</label>
            <input type="text" name="npsn" value="{{ old('npsn', $sekolah->npsn ?? '') }}" class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nama Kepala Sekolah</label>
            <input type="text" name="kepala_sekolah" value="{{ old('kepala_sekolah', $sekolah->kepala_sekolah ?? '') }}" class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">NIP Kepala Sekolah</label>
        <input type="text" name="kepala_sekolah_nip" value="{{ old('kepala_sekolah_nip', $sekolah->kepala_sekolah_nip ?? '') }}" maxlength="30" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Alamat</label>
        <textarea name="alamat" rows="3" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('alamat', $sekolah->alamat ?? '') }}</textarea>
    </div>
    <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan</button>
</form>
@endsection
