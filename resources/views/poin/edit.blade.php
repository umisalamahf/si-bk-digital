@extends('layouts.app')
@section('title', 'Ubah Poin')

@section('content')
<a href="{{ route('poin.riwayat', $poin->siswa_id) }}" class="text-sm text-rose-700 hover:underline">&larr; Kembali ke riwayat</a>
<h1 class="text-2xl font-bold text-slate-800 mt-2 mb-1">Ubah Catatan Poin</h1>
<p class="text-slate-500 mb-4">{{ $poin->siswa->nama ?? '-' }} &bull; Kelas {{ $poin->siswa->kelas->nama_kelas ?? '-' }}</p>

@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('poin.update', $poin) }}" class="bg-white rounded-xl shadow p-6 space-y-4 max-w-xl">
    @csrf @method('PUT')

    <div>
        <p class="text-sm font-medium mb-1">Tipe</p>
        <p class="px-3 py-2 bg-slate-100 rounded text-sm">{{ ucfirst($poin->tipe) }}
            <span class="text-xs text-slate-400">(tipe dan siswa tidak bisa diubah; kalau salah, hapus lalu catat ulang)</span></p>
    </div>

    @if($poin->tipe === 'pelanggaran')
    <div>
        <label class="block text-sm font-medium mb-1">Jenis Pelanggaran *</label>
        <select name="jenis_pelanggaran_id" required class="w-full border border-slate-300 rounded px-3 py-2">
            @foreach($pelanggaran as $p)
                <option value="{{ $p->id }}" @selected(old('jenis_pelanggaran_id', $poin->jenis_pelanggaran_id)==$p->id)>{{ $p->kode_pelanggaran ?? 'Tanpa kode' }} — {{ $p->nama_pelanggaran }} ({{ $p->bobot_point }} poin){{ $p->penanganan ? ' · ' . $p->penanganan : '' }}</option>
            @endforeach
        </select>
    </div>
    @else
    <div>
        <label class="block text-sm font-medium mb-1">Jenis Reward *</label>
        <select name="jenis_reward_id" required class="w-full border border-slate-300 rounded px-3 py-2">
            @foreach($reward as $r)
                <option value="{{ $r->id }}" @selected(old('jenis_reward_id', $poin->jenis_reward_id)==$r->id)>{{ $r->nama_reward }} ({{ $r->poin }} poin)</option>
            @endforeach
        </select>
    </div>
    @endif

    <div>
        <label class="block text-sm font-medium mb-1">Tanggal *</label>
        <input type="date" name="tanggal" value="{{ old('tanggal', $poin->tanggal) }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Keterangan</label>
        <input type="text" name="keterangan" value="{{ old('keterangan', $poin->keterangan) }}" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <p class="text-xs text-slate-500">📌 Setelah disimpan, trigger database otomatis menghitung ulang rekap poin siswa ini.</p>
    <div class="flex gap-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan Perubahan</button>
        <a href="{{ route('poin.riwayat', $poin->siswa_id) }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
@endsection
