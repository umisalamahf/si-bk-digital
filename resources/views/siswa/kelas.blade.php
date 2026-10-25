@extends('layouts.app')
@section('title', 'Data Kelas')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Data Kelas</h1>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<div class="grid md:grid-cols-3 gap-6">
    <div class="md:col-span-2 bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-100 text-left">
                <tr><th class="p-3">Nama Kelas</th><th class="p-3">Tingkat</th><th class="p-3">Tahun Ajaran</th><th class="p-3">Jml Siswa</th><th class="p-3">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($kelas as $k)
                @continue(in_array($k->nama_kelas, ['VII A', 'VII B'], true) && (int) $k->tingkat === 7 && $k->tahun_ajaran === '2026/2027')
                <tr class="border-t">
                    <td class="p-3">{{ $k->nama_kelas }}</td>
                    <td class="p-3">{{ $k->tingkat }}</td>
                    <td class="p-3">{{ $k->tahun_ajaran }}</td>
                    <td class="p-3">{{ $k->siswa_count }}</td>
                    <td class="p-3"><a href="{{ route('kelas.edit', $k) }}" class="text-rose-700 hover:underline">Ubah</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada kelas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold mb-3">Tambah Kelas</h2>
        <form method="POST" action="{{ route('kelas.store') }}" class="space-y-3">
            @csrf
            <input name="nama_kelas" placeholder="Nama kelas (mis. XII RPL 1)" required
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
            <input type="number" name="tingkat" placeholder="Tingkat (7-12)" min="7" max="12" required
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
            <input name="tahun_ajaran" placeholder="Tahun ajaran (2026/2027)" required
                class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
            <select name="guru_wali_id" class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
                <option value="">-- Wali Kelas (opsional) --</option>
                @foreach($guruWali as $gw)<option value="{{ $gw->id }}">{{ $gw->nama }}</option>@endforeach
            </select>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded text-sm">Simpan</button>
        </form>
    </div>
</div>
@endsection
