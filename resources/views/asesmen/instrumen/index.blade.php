@extends('layouts.app')
@section('title', 'Kelola Instrumen Asesmen')

@section('content')
<div class="flex flex-wrap justify-between items-center gap-3 mb-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Instrumen Asesmen BK</h1>
        <p class="text-sm text-slate-500">Kelola instrumen dan butir pertanyaan yang dipakai pada link asesmen.</p>
    </div>
    <a href="{{ route('asesmen.instrumen.create') }}" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">+ Instrumen Baru</a>
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if(session('error'))<div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">{{ session('error') }}</div>@endif

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Nama Instrumen</th><th class="p-3">Jenis Asesmen</th><th class="p-3">Jenjang</th><th class="p-3">Jumlah Butir</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($instrumen as $item)
                <tr class="border-t">
                    <td class="p-3">{{ $item->nama_instrumen }}@if($item->keterangan)<p class="text-xs text-slate-500">{{ $item->keterangan }}</p>@endif</td>
                    <td class="p-3">{{ $item->jenis->nama ?? '-' }}</td>
                    <td class="p-3">{{ $item->jenjang ?: '-' }}</td>
                    <td class="p-3">{{ $item->butir_count }}</td>
                    <td class="p-3">
                        <form method="POST" action="{{ route('asesmen.instrumen.destroy', $item) }}" onsubmit="return confirm('Hapus instrumen ini? Instrumen yang sudah dipakai tidak dapat dihapus.')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada instrumen asesmen.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $instrumen->links() }}</div>
@endsection
