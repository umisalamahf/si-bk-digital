@extends('layouts.app')
@section('title', 'Persuratan BK')

@section('content')
<div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-3 mb-5">
    <h1 class="text-2xl font-bold text-slate-800">Persuratan BK</h1>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('surat.create', ['jenis' => 'SPO']) }}" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-sm shadow-sm">+ Surat Pemanggilan Orang Tua</a>
        @if(auth()->user()->isGuruBk())
            <a href="{{ route('surat.kelakuan-baik.create') }}" class="bg-brand-50 hover:bg-brand-100 border border-brand-100 text-brand-700 px-4 py-2 rounded-xl text-sm">+ Surat Kelakuan Baik</a>
        @endif
        <a href="{{ route('surat.create') }}" class="bg-white border border-slate-200 hover:bg-brand-50 text-slate-700 px-4 py-2 rounded-xl text-sm">+ Surat BK Lainnya</a>
    </div>
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-50 text-left">
            <tr><th class="p-3">Nomor Surat</th><th class="p-3">Jenis</th><th class="p-3">Siswa</th><th class="p-3">Tanggal</th><th class="p-3">Status</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($surat as $s)
            <tr class="border-t">
                <td class="p-3 font-mono text-xs">{{ $s->nomor_surat }}</td>
                <td class="p-3">{{ $s->jenisSurat->nama_surat ?? '-' }}</td>
                <td class="p-3">{{ $s->siswa->nama ?? '-' }}</td>
                <td class="p-3">{{ \Carbon\Carbon::parse($s->tanggal)->format('d-m-Y') }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs {{ $s->status=='dicetak' ? 'bg-rose-50 text-rose-700' : 'bg-slate-100 text-slate-600' }}">{{ $s->status }}</span>
                </td>
                <td class="p-3"><a href="{{ route('surat.cetak', $s) }}" target="_blank" class="text-brand-700 hover:underline">Cetak</a></td>
            </tr>
            @empty
            <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada surat dibuat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $surat->links() }}</div>
@endsection
