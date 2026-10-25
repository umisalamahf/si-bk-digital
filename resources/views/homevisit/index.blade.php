@extends('layouts.app')
@section('title', 'Home Visit')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Home Visit (Kunjungan Rumah)</h1>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
<form method="GET" action="{{ route('homevisit.rekap.cetak') }}" target="_blank" class="flex flex-wrap items-end gap-2 mb-4 bg-white rounded-xl shadow p-4">
    <div><label class="block text-xs text-slate-500 mb-1">Tanggal Mulai</label><input type="date" name="tanggal_mulai" class="border border-slate-300 rounded px-3 py-2 text-sm"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Tanggal Selesai</label><input type="date" name="tanggal_selesai" class="border border-slate-300 rounded px-3 py-2 text-sm"></div>
    <button class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">Cetak Rekap PDF</button>
</form>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold mb-3">Catat Kunjungan</h2>
        <form method="POST" action="{{ route('homevisit.store') }}" class="space-y-2">
            @csrf
            <select name="siswa_id" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                <option value="">-- Pilih Siswa --</option>
                @foreach($siswa as $s)<option value="{{ $s->id }}" @selected(old('siswa_id')==$s->id)>{{ $s->nama }} ({{ $s->nis }})</option>@endforeach
            </select>
            <input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <input name="alamat_dikunjungi" value="{{ old('alamat_dikunjungi') }}" placeholder="Alamat yang dikunjungi" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <input name="pihak_ditemui" value="{{ old('pihak_ditemui') }}" placeholder="Pihak yang ditemui (opsional)" maxlength="255" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <input name="tujuan" value="{{ old('tujuan') }}" placeholder="Tujuan kunjungan (wajib)" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <textarea name="hasil" rows="3" placeholder="Hasil kunjungan" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">{{ old('hasil') }}</textarea>
            <textarea name="tindak_lanjut" rows="2" placeholder="Tindak lanjut" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">{{ old('tindak_lanjut') }}</textarea>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded text-sm">Simpan</button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-left">
                <tr><th class="p-3">Tanggal</th><th class="p-3">Siswa</th><th class="p-3">Tujuan</th><th class="p-3">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($kunjungan as $k)
                <tr class="border-t">
                    <td class="p-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($k->tanggal)->format('d-m-Y') }}</td>
                    <td class="p-3">{{ $k->siswa->nama ?? '-' }}</td>
                    <td class="p-3">{{ \Illuminate\Support\Str::limit($k->tujuan, 50) }}</td>
                    <td class="p-3"><a href="{{ route('homevisit.show', $k) }}" class="text-rose-700 hover:underline">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada catatan home visit.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $kunjungan->links() }}</div>
@endsection
