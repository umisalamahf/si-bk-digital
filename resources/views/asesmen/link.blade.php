@extends('layouts.app')
@section('title', 'Link Asesmen')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-2">Link / Token Asesmen Mandiri</h1>
<p class="text-slate-500 mb-5">Kelola asesmen mandiri kelas, bagikan akses siswa, dan pantau hasilnya.</p>
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ route('asesmen.instrumen.index') }}" class="bg-brand-50 hover:bg-brand-100 border border-brand-100 text-brand-700 px-4 py-2 rounded-xl text-sm">Kelola Instrumen</a>
    <a href="{{ route('asesmen.hasil') }}" class="bg-white hover:bg-brand-50 border border-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm">Lihat Hasil</a>
</div>
@if(session('status'))
<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">
    {{ session('status') }}
    @if(session('token_baru'))
    <div class="mt-2 flex items-center gap-2">
        <input readonly value="{{ url('/asesmen/isi/' . session('token_baru')) }}"
            class="border border-slate-300 rounded px-2 py-1 text-xs flex-1" onclick="this.select()">
    </div>
    @endif
</div>
@endif

<div class="grid lg:grid-cols-3 gap-5">
    <div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5">
        <h2 class="font-semibold mb-3">Buat Link Baru</h2>
        <form method="POST" action="{{ route('asesmen.link.store') }}" class="space-y-3">
            @csrf
            <select name="instrumen_id" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
                <option value="">-- Instrumen --</option>
                @foreach($instrumen as $i)<option value="{{ $i->id }}">{{ $i->nama_instrumen }} ({{ $i->jenis->nama }})</option>@endforeach
            </select>
            <select name="kelas_id" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
                <option value="">-- Kelas --</option>
                @foreach($kelas as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
            </select>
            <input type="number" name="masa_berlaku_hari" value="7" min="1" max="30"
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm" placeholder="Masa berlaku (hari)">
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded text-sm">Buat Link</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-brand-50 text-left">
                <tr><th class="p-3">Instrumen</th><th class="p-3">Kelas</th><th class="p-3">Kadaluarsa</th><th class="p-3">Status</th><th class="p-3">QR</th></tr>
            </thead>
            <tbody>
                @forelse($link as $l)
                <tr class="border-t">
                    <td class="p-3">{{ $l->instrumen->nama_instrumen ?? '-' }}</td>
                    <td class="p-3">{{ $l->kelas->nama_kelas ?? '-' }}</td>
                    <td class="p-3">{{ \Carbon\Carbon::parse($l->kadaluarsa)->format('d-m-Y H:i') }}</td>
                    <td class="p-3">
                        @if($l->masihBerlaku())
                            <span class="px-2 py-0.5 rounded text-xs bg-rose-50 text-rose-700">Aktif</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs bg-slate-200 text-slate-600">Kadaluarsa</span>
                        @endif
                    </td>
                    <td class="p-3"><a href="{{ route('asesmen.link.qr', $l) }}" target="_blank" class="text-brand-700 hover:underline">Lihat QR</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada link dibuat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
