@extends('layouts.app')
@section('title', 'Data Siswa')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5">
    <h1 class="text-2xl font-bold text-slate-800">Data Siswa</h1>
    <div class="flex gap-2">
        <a href="{{ route('siswa.cetak', request()->only('cari', 'kelas_id')) }}" target="_blank" class="bg-white border border-slate-200 hover:bg-brand-50 text-slate-700 px-4 py-2 rounded-xl text-sm shadow-sm">Cetak PDF</a>
        @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
            <a href="{{ route('siswa.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-sm shadow-sm">+ Tambah Siswa</a>
        @endif
    </div>
</div>

@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-100 text-red-800 rounded text-sm">{{ session('error') }}</div>@endif

<form method="GET" class="mb-5 flex flex-col gap-2 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-rose-100 sm:flex-row">
    <input type="text" name="cari" value="{{ request('cari') }}" placeholder="Cari nama/NIS..."
        class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm sm:flex-1">
    <select name="kelas_id" aria-label="Filter berdasarkan kelas"
        class="w-full rounded-xl border border-rose-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-brand-500 focus:ring-2 focus:ring-rose-100 sm:max-w-xs">
        <option value="">Semua Kelas</option>
        @foreach($kelas as $k)
            <option value="{{ $k->id }}" @selected((string) request('kelas_id') === (string) $k->id)>{{ $k->nama_kelas }}</option>
        @endforeach
    </select>
    <button class="rounded-xl bg-brand-50 px-5 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-100 sm:min-w-24">Cari</button>
</form>

<div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-50 text-left">
            <tr>
                <th class="p-3">NIS</th><th class="p-3">Nama</th><th class="p-3">Kelas</th>
                <th class="p-3">JK</th><th class="p-3">Usia</th><th class="p-3">Status</th><th class="p-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswa as $s)
            <tr class="border-t">
                <td class="p-3">{{ $s->nis }}</td>
                <td class="p-3">{{ $s->nama }}</td>
                <td class="p-3">{{ $s->kelas->nama_kelas ?? '-' }}</td>
                <td class="p-3">{{ $s->jenis_kelamin }}</td>
                <td class="p-3">{{ $s->usia !== null ? $s->usia . ' th' : '-' }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs
                        {{ $s->status=='aktif' ? 'bg-rose-50 text-rose-700' : ($s->status=='alumni' ? 'bg-slate-200 text-slate-600' : 'bg-amber-100 text-amber-700') }}">
                        {{ str_replace('_',' ',$s->status) }}
                    </span>
                </td>
                <td class="p-3 space-x-2">
                    @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
                        <a href="{{ route('siswa.show', $s) }}" class="text-brand-700 hover:underline">Biodata</a>
                    @endif
                    @if(! auth()->user()->isWaliKelas())
                        <a href="{{ route('poin.riwayat', $s) }}" class="text-rose-700 hover:underline">Poin</a>
                    @endif
                    @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
                        <a href="{{ route('siswa.edit', $s) }}" class="text-blue-600 hover:underline">Ubah</a>
                        <form method="POST" action="{{ route('siswa.destroy', $s) }}" class="inline"
                              onsubmit="return confirm('Yakin hapus siswa {{ $s->nama }}?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-6 text-center text-slate-400">Belum ada data siswa.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $siswa->links() }}</div>
@endsection
