@extends('layouts.app')
@section('title', 'Hasil Angket Kepuasan')

@section('content')
<div class="mb-5 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold">Hasil Angket Kepuasan</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $rpl->labelJenis() }} — {{ $rpl->topik_layanan ?: $rpl->topik }}</p>
    </div>
    <a href="{{ route('rpl.show', $rpl) }}" class="rounded border border-slate-300 px-4 py-2 text-sm">Kembali ke RPL</a>
</div>
<p class="mb-4 rounded-lg bg-white p-4 text-sm">Jumlah jawaban anonim: <strong>{{ $responseCount }}</strong></p>

<div class="mb-6 overflow-x-auto rounded-xl bg-white shadow">
    <h2 class="p-4 font-semibold">Ringkasan semua responden</h2>
    <table class="w-full text-left text-sm">
        <thead class="bg-slate-100"><tr><th class="p-3">Pernyataan</th><th class="p-3">Jawaban</th><th class="p-3">Rerata (1–4)</th><th class="p-3">Persentase dari skor maksimum</th></tr></thead>
        <tbody>
        @foreach($overallScores as $score)
            <tr class="border-t"><td class="p-3">{{ $score['question'] }}</td><td class="p-3">{{ $score['count'] }}</td><td class="p-3">{{ $score['average'] ?? '—' }}</td><td class="p-3">{{ $score['percentage'] === null ? '—' : $score['percentage'].'%' }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="grid gap-4 md:grid-cols-2">
@foreach($scoresByCategory as $category => $result)
    <section class="overflow-x-auto rounded-xl bg-white p-4 shadow">
        <h2 class="font-semibold">{{ $categoryLabels[$category] }} <span class="text-sm font-normal text-slate-500">({{ $result['count'] }} jawaban)</span></h2>
        <table class="mt-3 w-full text-left text-sm">
            <thead><tr><th class="py-2">Pernyataan</th><th class="py-2">Rerata</th></tr></thead>
            <tbody>
            @foreach($result['scores'] as $score)
                <tr class="border-t"><td class="py-2 pr-2">{{ $score['question'] }}</td><td class="py-2">{{ $score['average'] ?? '—' }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </section>
@endforeach
</div>
<p class="mt-4 text-xs text-slate-500">Persentase dihitung sebagai rerata ÷ 4 × 100. Rekap tidak menampilkan jawaban individual.</p>
@endsection
