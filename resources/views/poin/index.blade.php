@extends('layouts.app')
@section('title', 'Rekap Poin Siswa')

@section('content')
<div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-5">
    <h1 class="text-2xl font-bold text-slate-800">Rekap Poin Siswa</h1>
    @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
        <a href="{{ route('poin.create') }}" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-sm shadow-sm">+ Catat Poin</a>
    @endif
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<div class="flex flex-wrap items-center gap-2 mb-4">
<form method="GET" class="flex flex-wrap gap-2">
    <select name="status" class="border border-slate-200 rounded-xl px-3 py-2 text-sm bg-white" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="aman" @selected(request('status')=='aman')>Aman</option>
        <option value="perlu_perhatian" @selected(request('status')=='perlu_perhatian')>Perlu Perhatian</option>
        <option value="perlu_tindak_lanjut" @selected(request('status')=='perlu_tindak_lanjut')>Perlu Tindak Lanjut</option>
    </select>
</form>
<a href="{{ route('poin.rekap.cetak', request()->only('status')) }}" target="_blank" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-sm shadow-sm">Cetak PDF</a>
<a href="{{ route('poin.detail.cetak', ['tipe' => 'pelanggaran']) }}" target="_blank" class="bg-white border border-slate-200 hover:bg-brand-50 text-slate-700 px-4 py-2 rounded-xl text-sm shadow-sm">Rekap Pelanggaran</a>
<a href="{{ route('poin.detail.cetak', ['tipe' => 'reward']) }}" target="_blank" class="bg-white border border-slate-200 hover:bg-brand-50 text-slate-700 px-4 py-2 rounded-xl text-sm shadow-sm">Rekap Reward</a>
</div>

<p class="text-sm text-slate-500 mb-4">📌 Kolom di bawah ini diisi OTOMATIS oleh trigger database setiap ada poin baru/diubah/dihapus — bukan dihitung manual di PHP.</p>

<div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-50 text-left">
            <tr><th class="p-3">Siswa</th><th class="p-3">Tahun Ajaran</th><th class="p-3">Pelanggaran</th><th class="p-3">Reward</th><th class="p-3">Bersih</th><th class="p-3">Status</th><th class="p-3">Rujukan Tindak Lanjut</th></tr>
        </thead>
        <tbody>
            @forelse($rekap as $r)
            <tr class="border-t">
                <td class="p-3"><a href="{{ route('poin.riwayat', $r->siswa_id) }}" class="text-brand-700 hover:underline">{{ $r->siswa->nama ?? '-' }}</a></td>
                <td class="p-3">{{ $r->tahun_ajaran }}</td>
                <td class="p-3">{{ $r->total_pelanggaran }}</td>
                <td class="p-3">{{ $r->total_reward }}</td>
                <td class="p-3 font-semibold">{{ $r->total_bersih }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs
                        {{ $r->status_tindak_lanjut=='perlu_tindak_lanjut' ? 'bg-red-100 text-red-700' : ($r->status_tindak_lanjut=='perlu_perhatian' ? 'bg-amber-100 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                        {{ str_replace('_',' ',$r->status_tindak_lanjut) }}
                    </span>
                </td>
                @php
                    $aturan = $aturanAkumulasi->first(
                        fn ($item) => $r->total_pelanggaran >= $item->poin_min && $r->total_pelanggaran <= $item->poin_max
                    );
                @endphp
                <td class="p-3 text-slate-700">
                    @if($aturan)
                        <div class="font-semibold text-slate-800">{{ $aturan->tindak_lanjut }}</div>
                        <div class="mt-1 text-xs">Pihak: {{ $aturan->pihak_terlibat }}</div>
                    @elseif($r->total_pelanggaran > 100)
                        <span class="text-sm">Belum ada rujukan pada dokumen untuk poin di atas 100.</span>
                    @else
                        <span class="text-slate-500">Belum ada rujukan untuk poin ini.</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="p-6 text-center text-slate-400">Belum ada data poin.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $rekap->links() }}</div>
@endsection
