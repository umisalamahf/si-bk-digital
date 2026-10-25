@extends('layouts.app')
@section('title', 'Formulir Layanan BK')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-brand-700">Layanan Bimbingan dan Konseling</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900">Formulir Layanan BK</h1>
        <p class="mt-1 text-sm text-slate-600">Catat observasi, wawancara, konsultasi, dan mediasi secara terstruktur.</p>
    </div>
    <a href="{{ route('bk-formulir.create', 'observasi') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">
        + Formulir Baru
    </a>
</div>

@if(session('status'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-900">{{ session('status') }}</div>
@endif

<div class="mb-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Lembar Observasi Murid', 'observasi'],
        ['Pedoman Wawancara Guru BK', 'wawancara'],
        ['Formulir Konsultasi', 'konsultasi'],
        ['Mediasi Konflik Siswa', 'mediasi'],
    ] as [$label, $jenis])
        <a href="{{ route('bk-formulir.create', $jenis) }}" class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-rose-100 transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="block text-sm font-semibold text-slate-900">{{ $label }}</span>
            <span class="mt-1 block text-xs text-slate-600">Buka formulir &rarr;</span>
        </a>
    @endforeach
</div>

<div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-rose-100">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-rose-50 text-slate-800">
                <tr>
                    <th class="p-3 font-semibold">Tanggal</th>
                    <th class="p-3 font-semibold">Jenis Layanan</th>
                    <th class="p-3 font-semibold">Murid</th>
                    <th class="p-3 font-semibold">Dicatat Oleh</th>
                    <th class="p-3 font-semibold">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($formulir as $item)
                    <tr class="border-t border-slate-100">
                        <td class="whitespace-nowrap p-3">{{ $item->tanggal->translatedFormat('d M Y') }}</td>
                        <td class="p-3 font-medium text-slate-800">{{ str_replace('_', ' ', ucfirst($item->jenis)) }}</td>
                        <td class="p-3">
                            {{ $item->siswa->nama ?? '-' }}
                            @if($item->siswaPihakKedua)
                                <span class="text-slate-500"> &amp; {{ $item->siswaPihakKedua->nama }}</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $item->pembuat->name ?? '-' }}</td>
                        <td class="p-3"><a href="{{ route('bk-formulir.show', $item) }}" class="font-semibold text-brand-700 hover:underline">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-7 text-center text-slate-600">Belum ada formulir layanan yang disimpan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $formulir->links() }}</div>
@endsection
