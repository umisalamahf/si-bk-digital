@extends('layouts.app')
@section('title', 'Laporan '.$report['label'])

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-bold">Rekap {{ $report['label'] }}</h1>
    <p class="mt-1 text-sm text-slate-600">RPL tetap menjadi rencana layanan. Isi bagian pelaksanaan untuk melengkapi rekap setelah layanan berlangsung.</p>
</div>
<nav class="mb-5 flex flex-wrap gap-2" aria-label="Jenis laporan layanan">
    @foreach($reports as $slug => $item)
        <a href="{{ route('laporan.'.$slug) }}" class="rounded px-3 py-2 text-sm {{ $slug === $jenis ? 'bg-rose-700 text-white' : 'border border-slate-300 bg-white' }}">{{ $item['label'] }}</a>
    @endforeach
</nav>
@if(session('status'))<div class="mb-4 rounded bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>@endif

<div class="space-y-5">
@forelse($rplRows as $rpl)
    <article class="rounded-xl bg-white p-5 shadow">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="font-semibold">{{ $rpl->topik_layanan ?: $rpl->topik }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ \Carbon\Carbon::parse($rpl->tanggal)->format('d-m-Y') }} · {{ $rpl->kelas->nama_kelas ?? $rpl->kelas_semester ?? 'Layanan perorangan/kelompok' }} · {{ $rpl->peserta->count() }} peserta</p>
            </div>
            <a href="{{ route('rpl.show', $rpl) }}" class="text-sm text-rose-700 underline">Lihat RPL</a>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <section class="rounded-lg bg-slate-50 p-4 text-sm">
                <h3 class="font-semibold">Rencana dan sasaran</h3>
                <p class="mt-2"><strong>Peserta:</strong> {{ $rpl->peserta->pluck('nama')->join(', ') ?: 'Peserta satu kelas' }}</p>
                <p class="mt-1"><strong>Tujuan:</strong> {{ $rpl->tujuan_layanan_bk ?: $rpl->tujuan ?: '—' }}</p>
                <p class="mt-1"><strong>Materi/topik:</strong> {{ $rpl->materi ?: $rpl->topik_layanan ?: $rpl->topik }}</p>
                @if($rpl->masalah)<p class="mt-1"><strong>Permasalahan yang dicatat di RPL:</strong> {{ $rpl->masalah }}</p>@endif
                @if($rpl->teknik)<p class="mt-1"><strong>Teknik:</strong> {{ $rpl->teknik }}</p>@endif
                @if($rpl->kegiatan_awal || $rpl->kegiatan_inti || $rpl->kegiatan_penutup)
                    <p class="mt-2"><strong>Rencana kegiatan:</strong> {{ collect([$rpl->kegiatan_awal, $rpl->kegiatan_inti, $rpl->kegiatan_penutup])->filter()->join(' · ') }}</p>
                @endif
            </section>
            <section class="rounded-lg border border-rose-100 p-4 text-sm">
                <h3 class="font-semibold">Pelaksanaan dan hasil</h3>
                @if($rpl->pelaksanaan)
                    <p class="mt-2"><strong>Hadir:</strong> {{ $rpl->pelaksanaan->jumlah_hadir }} / {{ $rpl->peserta->count() }} peserta</p>
                    <p class="mt-1 whitespace-pre-line"><strong>Pelaksanaan:</strong> {{ $rpl->pelaksanaan->hasil_pelaksanaan }}</p>
                    <p class="mt-1 whitespace-pre-line"><strong>Hasil layanan:</strong> {{ $rpl->pelaksanaan->hasil_layanan ?: '—' }}</p>
                    <p class="mt-1 whitespace-pre-line"><strong>Tindak lanjut:</strong> {{ $rpl->pelaksanaan->tindak_lanjut ?: '—' }}</p>
                @else
                    <p class="mt-2 text-slate-500">Rekap pelaksanaan belum diisi.</p>
                @endif
                <form method="POST" action="{{ route('laporan.pelaksanaan.simpan', ['jenis' => $jenis, 'rpl' => $rpl]) }}" class="mt-4 space-y-2 border-t pt-4">
                    @csrf
                    <label class="block text-xs font-medium">Jumlah peserta hadir (0–{{ $rpl->peserta->count() }})</label>
                    <input type="number" name="jumlah_hadir" min="0" max="{{ $rpl->peserta->count() }}" value="{{ old('jumlah_hadir', $rpl->pelaksanaan->jumlah_hadir ?? $rpl->peserta->count()) }}" required class="w-full border border-slate-300 px-3 py-2">
                    <label class="block text-xs font-medium">Uraian pelaksanaan aktual</label>
                    <textarea name="hasil_pelaksanaan" rows="3" required maxlength="10000" class="w-full border border-slate-300 px-3 py-2">{{ old('hasil_pelaksanaan', $rpl->pelaksanaan->hasil_pelaksanaan ?? '') }}</textarea>
                    <label class="block text-xs font-medium">Hasil layanan</label>
                    <textarea name="hasil_layanan" rows="2" maxlength="10000" class="w-full border border-slate-300 px-3 py-2">{{ old('hasil_layanan', $rpl->pelaksanaan->hasil_layanan ?? '') }}</textarea>
                    <label class="block text-xs font-medium">Tindak lanjut</label>
                    <textarea name="tindak_lanjut" rows="2" maxlength="10000" class="w-full border border-slate-300 px-3 py-2">{{ old('tindak_lanjut', $rpl->pelaksanaan->tindak_lanjut ?? '') }}</textarea>
                    <button class="rounded bg-rose-700 px-4 py-2 text-sm font-semibold text-white">Simpan rekap</button>
                </form>
            </section>
        </div>
    </article>
@empty
    <div class="rounded-xl bg-white p-8 text-center text-slate-500">Belum ada RPL {{ strtolower($report['label']) }}.</div>
@endforelse
</div>
<div class="mt-5">{{ $rplRows->links() }}</div>
@endsection
