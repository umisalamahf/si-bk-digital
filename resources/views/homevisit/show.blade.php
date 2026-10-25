@extends('layouts.app')
@section('title', 'Detail Home Visit')

@section('content')
<a href="{{ route('homevisit.index') }}" class="text-sm text-rose-700 hover:underline">&larr; Kembali</a>
<h1 class="text-2xl font-bold text-slate-800 mt-2 mb-1">Home Visit: {{ $homeVisit->siswa->nama ?? '-' }}</h1>
<p class="text-slate-500 mb-4">{{ \Carbon\Carbon::parse($homeVisit->tanggal)->format('d-m-Y') }} &bull; Kelas {{ $homeVisit->siswa->kelas->nama_kelas ?? '-' }}</p>
<a href="{{ route('homevisit.cetak', $homeVisit) }}" target="_blank" class="inline-block mb-4 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">Cetak PDF</a>

<div class="bg-white rounded-xl shadow p-6 max-w-2xl space-y-4">
    <div><p class="text-xs text-slate-400">Alamat yang dikunjungi</p><p>{{ $homeVisit->alamat_dikunjungi ?: '-' }}</p></div>
    <div><p class="text-xs text-slate-400">Pihak yang ditemui</p><p>{{ $homeVisit->pihak_ditemui ?: '-' }}</p></div>
    <div><p class="text-xs text-slate-400">Tujuan</p><p>{{ $homeVisit->tujuan }}</p></div>
    <div><p class="text-xs text-slate-400">Hasil</p><p class="whitespace-pre-line">{{ $homeVisit->hasil ?: '-' }}</p></div>
    <div><p class="text-xs text-slate-400">Tindak lanjut</p><p class="whitespace-pre-line">{{ $homeVisit->tindak_lanjut ?: '-' }}</p></div>
</div>
@endsection
