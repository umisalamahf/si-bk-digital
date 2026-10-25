@extends('layouts.app')
@section('title', 'Ubah Data Kelas')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Ubah Data Kelas</h1>

@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
    <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('kelas.update', $kelas) }}" class="bg-white rounded-xl shadow p-5 space-y-3 max-w-xl">
    @csrf
    @method('PUT')
    <div>
        <label for="nama_kelas" class="block text-sm font-medium mb-1">Nama Kelas</label>
        <input id="nama_kelas" name="nama_kelas" value="{{ old('nama_kelas', $kelas->nama_kelas) }}" required maxlength="50"
            class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label for="tingkat" class="block text-sm font-medium mb-1">Tingkat (7-12)</label>
        <input id="tingkat" type="number" name="tingkat" value="{{ old('tingkat', $kelas->tingkat) }}" min="7" max="12" required
            class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label for="tahun_ajaran" class="block text-sm font-medium mb-1">Tahun Ajaran</label>
        <input id="tahun_ajaran" name="tahun_ajaran" value="{{ old('tahun_ajaran', $kelas->tahun_ajaran) }}" maxlength="12" required
            class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
    </div>
    <div>
        <label for="guru_wali_id" class="block text-sm font-medium mb-1">Wali Kelas</label>
        <select id="guru_wali_id" name="guru_wali_id" class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
            <option value="">-- Tanpa Wali Kelas --</option>
            @foreach($guruWali as $guru)
                <option value="{{ $guru->id }}" @selected((string) old('guru_wali_id', $kelas->guru_wali_id) === (string) $guru->id)>{{ $guru->nama }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan Perubahan</button>
        <a href="{{ route('kelas.index') }}" class="border border-slate-300 px-5 py-2 rounded">Batal</a>
    </div>
</form>
@endsection
