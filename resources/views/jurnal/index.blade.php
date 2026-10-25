@extends('layouts.app')
@section('title', 'Jurnal Layanan')

@section('content')
<div class="mb-6">
    <p class="text-sm font-medium tracking-wide text-brand-700">BK hadir, mendengar, membimbing, menginspirasi.</p>
    <h1 class="mt-2 text-2xl font-bold leading-tight tracking-wide text-slate-800 sm:text-3xl">JURNAL HARIAN GURU BIMBINGAN DAN KONSELING</h1>
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif
<div class="mb-4">
    <form method="GET" action="{{ route('jurnal.cetak') }}" target="_blank" class="flex flex-wrap items-end gap-2 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-rose-100">
        <div><label class="block text-xs text-slate-500 mb-1">Tanggal Mulai</label><input type="date" name="tanggal_mulai" class="border border-slate-300 rounded px-3 py-2 text-sm"></div>
        <div><label class="block text-xs text-slate-500 mb-1">Tanggal Selesai</label><input type="date" name="tanggal_selesai" class="border border-slate-300 rounded px-3 py-2 text-sm"></div>
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">Cetak Rekap PDF</button>
    </form>
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-3 font-semibold text-slate-800">Catat Jurnal Harian</h2>
        <form method="POST" action="{{ route('jurnal.store') }}" class="space-y-2">
            @csrf
            <label for="tanggal" class="block text-sm font-medium text-slate-700">1. Hari / Tanggal</label>
            <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 px-2 py-1.5 text-sm">

            <label for="kegiatan" class="block pt-2 text-sm font-medium text-slate-700">2. Jenis Kegiatan / Layanan BK</label>
            <input id="kegiatan" name="kegiatan" value="{{ old('kegiatan') }}" placeholder="Contoh: Bimbingan klasikal" required maxlength="255" class="w-full border border-slate-300 px-2 py-1.5 text-sm">

            <label for="sasaran" class="block pt-2 text-sm font-medium text-slate-700">3. Sasaran Layanan</label>
            <input id="sasaran" name="sasaran" value="{{ old('sasaran') }}" placeholder="Contoh: Kelas VII A" maxlength="200" class="w-full border border-slate-300 px-2 py-1.5 text-sm">

            <label for="uraian" class="block pt-2 text-sm font-medium text-slate-700">4. Uraian Kegiatan</label>
            <textarea id="uraian" name="uraian" placeholder="Tuliskan uraian kegiatan atau layanan BK" rows="5" maxlength="10000" required class="w-full border border-slate-300 px-2 py-1.5 text-sm">{{ old('uraian') }}</textarea>
            <button class="w-full rounded-xl bg-brand-600 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan Jurnal</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-rose-100 lg:col-span-2">
        <table class="w-full text-sm">
            <thead class="bg-brand-50 text-left text-slate-800">
                <tr><th class="p-3">1. Hari / Tanggal</th><th class="p-3">2. Jenis Kegiatan / Layanan BK</th><th class="p-3">3. Sasaran Layanan</th><th class="p-3">4. Uraian Kegiatan</th></tr>
            </thead>
            <tbody>
                @forelse($jurnal as $j)
                <tr class="border-t border-rose-100 align-top">
                    <td class="whitespace-nowrap p-3">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}</td>
                    <td class="p-3">{{ $j->kegiatan }}</td>
                    <td class="p-3">{{ $j->sasaran ?: '-' }}</td>
                    <td class="max-w-md whitespace-pre-line p-3">{{ \Illuminate\Support\Str::limit($j->uraian ?: trim(($j->hasil ?? '') . "\n" . ($j->keterangan ?? '')), 180) ?: '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada jurnal.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $jurnal->links() }}</div>
<p class="mt-6 text-sm leading-relaxed text-slate-600">Setiap langkah kecil hari ini, adalah perubahan besar untuk esok hari.</p>
@endsection
