@extends('layouts.app')
@section('title', $pelanggaran->exists ? 'Ubah Kode Pelanggaran' : 'Tambah Kode Pelanggaran')

@section('content')
<a href="{{ route('jenis-pelanggaran.index') }}" class="text-sm font-medium text-rose-800 hover:underline">&larr; Kembali ke daftar kode</a>
<h1 class="mb-4 mt-2 text-2xl font-bold text-slate-800">{{ $pelanggaran->exists ? 'Ubah Kode Pelanggaran' : 'Tambah Kode Pelanggaran' }}</h1>

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST"
      action="{{ $pelanggaran->exists ? route('jenis-pelanggaran.update', $pelanggaran) : route('jenis-pelanggaran.store') }}"
      class="max-w-2xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100">
    @csrf
    @if($pelanggaran->exists) @method('PUT') @endif

    <div>
        <label for="kode_pelanggaran" class="mb-1 block text-sm font-semibold text-slate-800">Kode Pelanggaran *</label>
        <input id="kode_pelanggaran" name="kode_pelanggaran" value="{{ old('kode_pelanggaran', $pelanggaran->kode_pelanggaran) }}" required maxlength="20" class="w-full border border-slate-300 px-3 py-2 text-sm" autocomplete="off">
    </div>
    <div>
        <label for="nama_pelanggaran" class="mb-1 block text-sm font-semibold text-slate-800">Nama Pelanggaran *</label>
        <input id="nama_pelanggaran" name="nama_pelanggaran" value="{{ old('nama_pelanggaran', $pelanggaran->nama_pelanggaran) }}" required maxlength="150" class="w-full border border-slate-300 px-3 py-2 text-sm">
    </div>
    <div>
        <label for="kategori_kelompok" class="mb-1 block text-sm font-semibold text-slate-800">Kategori Resmi *</label>
        <input id="kategori_kelompok" name="kategori_kelompok" value="{{ old('kategori_kelompok', $pelanggaran->kategori_kelompok) }}" required maxlength="100" class="w-full border border-slate-300 px-3 py-2 text-sm" placeholder="Contoh: Kedisiplinan & Kehadiran">
    </div>
    <div>
        <label for="kategori" class="mb-1 block text-sm font-semibold text-slate-800">Tingkat Kategori Sistem *</label>
        <select id="kategori" name="kategori" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            <option value="">-- Pilih kategori --</option>
            @foreach(['ringan' => 'Ringan', 'sedang' => 'Sedang', 'berat' => 'Berat'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kategori', $pelanggaran->kategori) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="bobot_point" class="mb-1 block text-sm font-semibold text-slate-800">Bobot Poin *</label>
        <input id="bobot_point" type="number" name="bobot_point" value="{{ old('bobot_point', $pelanggaran->bobot_point) }}" required min="1" max="2147483647" class="w-full border border-slate-300 px-3 py-2 text-sm">
        <p class="mt-1 text-xs text-slate-600">Bobot ini otomatis digunakan saat jenis pelanggaran dipilih pada pencatatan poin.</p>
    </div>
    <div>
        <label for="penanganan" class="mb-1 block text-sm font-semibold text-slate-800">Penanganan *</label>
        <textarea id="penanganan" name="penanganan" required rows="3" maxlength="2000" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old('penanganan', $pelanggaran->penanganan) }}</textarea>
    </div>

    <div class="flex flex-wrap gap-2 pt-2">
        <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan</button>
        <a href="{{ route('jenis-pelanggaran.index') }}" class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
    </div>
</form>
@endsection
