@extends('layouts.app')
@section('title', 'Jurnal Bulanan BK')

@section('content')
<div class="mb-6">
    <p class="text-sm font-medium tracking-wide text-brand-700">BK hadir, mendengar, membimbing, menginspirasi.</p>
    <h1 class="mt-2 text-2xl font-bold leading-tight tracking-wide text-slate-800 sm:text-3xl">JURNAL BULANAN GURU BIMBINGAN DAN KONSELING</h1>
    <p class="mt-1 text-sm text-slate-600">Rekap {{ $namaBulan }} {{ $tahun }} · {{ $jumlahKegiatan }} kegiatan tercatat</p>
</div>

@if($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

<form method="GET" action="{{ route('jurnal.bulanan.index') }}" class="mb-5 flex flex-wrap items-end gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-rose-100">
    <div>
        <label for="bulan" class="mb-1 block text-sm font-medium text-slate-700">Bulan</label>
        <select id="bulan" name="bulan" class="border border-slate-300 px-3 py-2 text-sm">
            @for($nomorBulan = 1; $nomorBulan <= 12; $nomorBulan++)
                <option value="{{ $nomorBulan }}" @selected($bulan === $nomorBulan)>{{ \Illuminate\Support\Carbon::createFromDate($tahun, $nomorBulan, 1)->translatedFormat('F') }}</option>
            @endfor
        </select>
    </div>
    <div>
        <label for="tahun" class="mb-1 block text-sm font-medium text-slate-700">Tahun</label>
        <input id="tahun" type="number" name="tahun" min="2000" max="2100" value="{{ $tahun }}" required class="w-32 border border-slate-300 px-3 py-2 text-sm">
    </div>
    <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Tampilkan</button>
    <a href="{{ route('jurnal.bulanan.cetak', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="rounded-xl border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-brand-700 hover:bg-rose-50">Cetak Rekap Bulanan</a>
</form>

<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-rose-100">
    <table class="w-full text-sm">
        <thead class="bg-brand-50 text-left text-slate-800">
            <tr>
                <th class="p-3">Hari / Tanggal</th>
                <th class="p-3">Jenis Kegiatan / Layanan BK</th>
                <th class="p-3">Sasaran Layanan</th>
                <th class="p-3">Uraian Kegiatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jurnal as $item)
                <tr class="border-t border-rose-100 align-top">
                    <td class="whitespace-nowrap p-3">{{ \Illuminate\Support\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                    <td class="p-3">{{ $item->kegiatan }}</td>
                    <td class="p-3">{{ $item->sasaran ?: '-' }}</td>
                    <td class="max-w-xl whitespace-pre-line p-3">{{ $item->uraian ?: trim(($item->hasil ?? '') . "\n" . ($item->keterangan ?? '')) ?: '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-500">Belum ada jurnal untuk {{ $namaBulan }} {{ $tahun }}.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $jurnal->links() }}</div>
<p class="mt-6 text-sm text-slate-600">Setiap langkah kecil hari ini, adalah perubahan besar untuk esok hari.</p>
@endsection
