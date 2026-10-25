@extends('layouts.app')
@section('title', 'Riwayat Poin')

@section('content')
@php $bisaUbah = auth()->user()->isAdmin() || auth()->user()->isGuruBk(); @endphp
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
<h1 class="text-2xl font-bold text-slate-800 mb-1">Riwayat Poin — {{ $siswa->nama }}</h1>
<p class="text-slate-500 mb-4">NIS: {{ $siswa->nis }} • Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>

<div class="grid md:grid-cols-3 gap-4 mb-6">
    @foreach($rekap as $r)
    <div class="bg-white rounded-xl shadow p-4">
        <p class="text-xs text-slate-400">Tahun Ajaran {{ $r->tahun_ajaran }}</p>
        <p class="text-lg font-bold">{{ $r->total_bersih }} poin bersih</p>
        <p class="text-xs text-slate-500">Pelanggaran {{ $r->total_pelanggaran }} • Reward {{ $r->total_reward }}</p>
        <span class="inline-block mt-1 px-2 py-0.5 rounded text-xs
            {{ $r->status_tindak_lanjut=='perlu_tindak_lanjut' ? 'bg-red-100 text-red-700' : ($r->status_tindak_lanjut=='perlu_perhatian' ? 'bg-amber-100 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
            {{ str_replace('_',' ',$r->status_tindak_lanjut) }}
        </span>
    </div>
    @endforeach
</div>

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Tanggal</th><th class="p-3">Tipe</th><th class="p-3">Jenis</th><th class="p-3">Poin</th><th class="p-3">Keterangan</th>@if($bisaUbah)<th class="p-3">Aksi</th>@endif</tr>
        </thead>
        <tbody>
            @forelse($riwayat as $r)
            <tr class="border-t">
                <td class="p-3">{{ \Carbon\Carbon::parse($r->tanggal)->format('d-m-Y') }}</td>
                <td class="p-3">{{ ucfirst($r->tipe) }}</td>
                <td class="p-3">{{ $r->jenisPelanggaran->nama_pelanggaran ?? $r->jenisReward->nama_reward ?? '-' }}</td>
                <td class="p-3">{{ $r->poin }}</td>
                <td class="p-3">{{ $r->keterangan }}</td>
                @if($bisaUbah)
                <td class="p-3 whitespace-nowrap space-x-2">
                    <a href="{{ route('poin.edit', $r) }}" class="text-blue-600 hover:underline">Ubah</a>
                    <form method="POST" action="{{ route('poin.destroy', $r) }}" class="inline"
                          onsubmit="return confirm('Yakin menghapus catatan poin ini? Rekap poin siswa akan dihitung ulang.')">
                        @csrf @method('DELETE')
                        <button class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </td>
                @endif
            </tr>
            @empty
            <tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada riwayat poin.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
