@extends('layouts.app')
@section('title', 'Audit Log')

@section('content')
<div class="flex items-center justify-between mb-2">
    <h1 class="text-2xl font-bold text-slate-800">Audit Log Data Siswa</h1>
    <form method="GET">
        <select name="aksi" onchange="this.form.submit()" class="border border-slate-300 rounded px-2 py-1.5 text-sm">
            <option value="">Semua aksi</option>
            <option value="update" @selected(request('aksi')=='update')>Diubah</option>
            <option value="delete" @selected(request('aksi')=='delete')>Dihapus</option>
        </select>
    </form>
</div>
<p class="text-sm text-slate-500 mb-4">📌 Riwayat ini dicatat OTOMATIS oleh trigger database setiap data siswa diubah atau dihapus, lengkap dengan siapa pelakunya.</p>

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Waktu</th><th class="p-3">Aksi</th><th class="p-3">Oleh</th><th class="p-3">Perubahan</th></tr>
        </thead>
        <tbody>
            @forelse($log as $l)
            <tr class="border-t align-top">
                <td class="p-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($l->created_at)->format('d-m-Y H:i') }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs {{ $l->aksi=='delete' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                        {{ $l->aksi=='delete' ? 'dihapus' : 'diubah' }}
                    </span>
                    <div class="text-xs text-slate-400 mt-1">siswa #{{ $l->record_id }}</div>
                </td>
                <td class="p-3">{{ $l->nama_user ?? '(tidak tercatat)' }}</td>
                <td class="p-3">
                    @forelse($l->perubahan as $p)
                        <div><span class="font-medium">{{ str_replace('_', ' ', $p[0]) }}</span>:
                            <span class="text-slate-500">{{ $p[1] }}</span> &rarr; <span class="text-slate-800">{{ $p[2] }}</span></div>
                    @empty
                        <span class="text-slate-400">-</span>
                    @endforelse
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada riwayat perubahan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $log->links() }}</div>
@endsection
