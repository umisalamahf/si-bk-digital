@extends('layouts.app')
@section('title', 'Surat Kelakuan Baik')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-2">Buat Surat Kelakuan Baik</h1>
<p class="text-sm text-slate-500 mb-4">Nomor surat dibuat otomatis. Surat menggunakan identitas sekolah, data siswa, dan tanda tangan Guru BK yang tercatat.</p>

@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('surat.kelakuan-baik.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4 max-w-2xl">
    @csrf
    <div>
        <label for="siswa_id" class="block text-sm font-medium mb-1">Siswa *</label>
        <select id="siswa_id" name="siswa_id" required class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih siswa aktif --</option>
            @foreach($siswa as $item)
                <option value="{{ $item->id }}" @selected(old('siswa_id') == $item->id)>{{ $item->nama }} (NIS {{ $item->nis }}) — {{ $item->kelas->nama_kelas ?? '-' }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="perihal" class="block text-sm font-medium mb-1">Keperluan / Keterangan *</label>
        <textarea id="perihal" name="perihal" rows="3" maxlength="200" required class="w-full border border-slate-300 rounded px-3 py-2" placeholder="Contoh: persyaratan pendaftaran pendidikan lanjutan">{{ old('perihal') }}</textarea>
    </div>
    <div>
        <label for="tanggal" class="block text-sm font-medium mb-1">Tanggal Surat *</label>
        <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Buat & Cetak PDF</button>
        <a href="{{ route('surat.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
@endsection
