@extends('layouts.app')
@section('title', 'RPL')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold text-slate-800">Rencana Pelaksanaan Layanan (RPL)</h1>
    <a href="{{ route('rpl.create') }}" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">+ Buat RPL</a>
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Tanggal</th><th class="p-3">Jenis</th><th class="p-3">Topik</th><th class="p-3">Kelas/Peserta</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($rpl as $r)
            <tr class="border-t">
                <td class="p-3">{{ \Carbon\Carbon::parse($r->tanggal)->format('d-m-Y') }}</td>
                <td class="p-3"><span class="px-2 py-0.5 rounded text-xs bg-rose-50 text-rose-700">{{ $r->labelJenis() }}</span></td>
                <td class="p-3">{{ $r->topik }}</td>
                <td class="p-3">
                    @if($r->jenis_rpl === 'klasikal')
                        {{ $r->kelas->nama_kelas ?? '-' }} ({{ $r->peserta->count() }} siswa)
                    @else
                        {{ $r->peserta->pluck('nama')->join(', ') }}
                    @endif
                </td>
                <td class="p-3"><a href="{{ route('rpl.show', $r) }}" class="text-rose-700 hover:underline">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada RPL dibuat.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $rpl->links() }}</div>
@endsection
