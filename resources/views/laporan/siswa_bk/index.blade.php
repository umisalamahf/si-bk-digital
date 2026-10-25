@extends('layouts.app')
@section('title', 'Laporan Rapor BK')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Rapor BK per Siswa</h1>
    <p class="mt-1 text-sm text-slate-600">Pilih siswa untuk melihat ringkasan layanan, hasil asesmen, dan tindak lanjut yang tercatat. Halaman ini hanya tersedia untuk admin dan Guru BK.</p>
</div>

<form method="GET" action="{{ route('laporan.siswa-bk.index') }}" class="mb-5 grid gap-3 rounded-xl bg-white p-4 shadow sm:grid-cols-[1fr_14rem_auto]">
    <label class="sr-only" for="cari-siswa">Cari nama atau NIS</label>
    <input id="cari-siswa" name="cari" value="{{ $filters['cari'] ?? '' }}" maxlength="150" placeholder="Cari nama atau NIS" class="w-full border border-slate-300 px-3 py-2">
    <label class="sr-only" for="kelas-id">Filter kelas</label>
    <select id="kelas-id" name="kelas_id" class="w-full border border-slate-300 px-3 py-2">
        <option value="">Semua kelas</option>
        @foreach($kelas as $item)
            <option value="{{ $item->id }}" @selected((string) ($filters['kelas_id'] ?? '') === (string) $item->id)>{{ $item->nama_kelas }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button class="rounded-lg bg-rose-700 px-4 py-2 text-sm font-semibold text-white">Cari</button>
        <a href="{{ route('laporan.siswa-bk.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Reset</a>
    </div>
</form>

<div class="overflow-x-auto rounded-xl bg-white shadow">
    <table class="w-full text-left text-sm">
        <thead class="border-b bg-slate-50">
            <tr>
                <th scope="col" class="px-4 py-3">Nama</th>
                <th scope="col" class="px-4 py-3">NIS</th>
                <th scope="col" class="px-4 py-3">Kelas</th>
                <th scope="col" class="px-4 py-3">Status</th>
                <th scope="col" class="px-4 py-3"><span class="sr-only">Aksi</span></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($siswa as $item)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $item->nama }}</td>
                    <td class="px-4 py-3">{{ $item->nis }}</td>
                    <td class="px-4 py-3">{{ $item->kelas->nama_kelas ?? '—' }}</td>
                    <td class="px-4 py-3">{{ str_replace('_', ' ', ucfirst($item->status)) }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('laporan.siswa-bk.show', $item) }}" class="font-semibold text-rose-700 underline">Buka rapor</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Tidak ada siswa yang cocok dengan pencarian.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $siswa->links() }}</div>
@endsection
