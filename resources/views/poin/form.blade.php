@extends('layouts.app')
@section('title', 'Catat Poin Siswa')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Catat Poin Siswa</h1>
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('poin.store') }}" class="bg-white rounded-xl shadow p-6 space-y-4 max-w-2xl" x-data="{ tipe: 'pelanggaran' }">
    @csrf
    <div>
        <label class="block text-sm font-medium mb-1">Siswa *</label>
        <select name="siswa_id" required class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih --</option>
            @foreach($siswa as $s)<option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->nis }})</option>@endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tipe *</label>
        <div class="flex gap-4">
            <label><input type="radio" name="tipe" value="pelanggaran" x-model="tipe" checked> Pelanggaran</label>
            <label><input type="radio" name="tipe" value="reward" x-model="tipe"> Reward</label>
        </div>
    </div>

    <div x-show="tipe === 'pelanggaran'">
        <label class="block text-sm font-medium mb-1">Jenis Pelanggaran</label>
        <select name="jenis_pelanggaran_id" class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih --</option>
            @foreach($pelanggaran as $p)
                <option value="{{ $p->id }}" @selected(old('jenis_pelanggaran_id') == $p->id)>
                    {{ $p->kode_pelanggaran ?? 'Tanpa kode' }} — {{ $p->nama_pelanggaran }} ({{ $p->bobot_point }} poin){{ $p->penanganan ? ' · ' . $p->penanganan : '' }}
                </option>
            @endforeach
        </select>
        @if($pelanggaran->isEmpty())
            <p class="mt-1 text-sm text-rose-800">Daftar pelanggaran belum tersedia. Tambahkan dahulu melalui menu Daftar Pelanggaran.</p>
        @else
            <p class="mt-1 text-xs text-slate-600">Bobot poin diambil otomatis dari master saat catatan disimpan.</p>
        @endif
    </div>
    <div x-show="tipe === 'reward'" style="display:none">
        <label class="block text-sm font-medium mb-1">Jenis Reward</label>
        <select name="jenis_reward_id" class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih --</option>
            @foreach($reward as $r)<option value="{{ $r->id }}">{{ $r->nama_reward }} ({{ $r->poin }} poin)</option>@endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tanggal *</label>
        <input type="date" name="tanggal" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Keterangan</label>
        <input type="text" name="keterangan" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan</button>
        <a href="{{ route('poin.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
