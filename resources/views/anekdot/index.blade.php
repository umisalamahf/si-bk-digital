@extends('layouts.app')
@section('title', 'Catatan Anekdot')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold text-slate-800">Catatan Anekdot</h1>
    @if(auth()->user()->isGuruBk())
        <a href="{{ route('anekdot.create') }}" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">+ Tambah Catatan</a>
    @endif
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

@if(auth()->user()->isWaliKelas())
<div class="mb-4 p-3 bg-amber-50 text-amber-700 text-sm rounded">
    🔒 Khusus catatan kategori <b>berat</b>, isinya disembunyikan dari tampilan wali kelas — hanya muncul tanda bahwa ada catatan, sesuai aturan privasi data siswa.
</div>
@endif

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Tanggal</th><th class="p-3">Siswa</th><th class="p-3">Kategori</th><th class="p-3">Kejadian</th></tr>
        </thead>
        <tbody>
            @forelse($catatan as $c)
            <tr class="border-t">
                <td class="p-3">{{ \Carbon\Carbon::parse($c->tanggal)->format('d-m-Y') }}</td>
                <td class="p-3">{{ $c->siswa->nama ?? '-' }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs
                        {{ $c->kategori=='berat' ? 'bg-red-100 text-red-700' : ($c->kategori=='sedang' ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ $c->kategori }}
                    </span>
                </td>
                <td class="p-3">{{ \Illuminate\Support\Str::limit($c->kejadian, 80) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada catatan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $catatan->links() }}</div>
@endsection
