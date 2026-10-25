@extends('layouts.app')
@section('title', $definition['title'])

@section('content')
<div class="mb-6">
    <a href="{{ route('bk-formulir.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">&larr; Kembali ke arsip formulir</a>
    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $definition['title'] }}</h1>
    <p class="mt-1 text-sm text-slate-600">{{ $formulir->tanggal->translatedFormat('d F Y') }} · Dicatat oleh {{ $formulir->pembuat->name ?? '-' }}</p>
</div>

<section class="mb-5 rounded-2xl bg-linear-to-r from-rose-50 to-white p-5 shadow-sm ring-1 ring-rose-100">
    <h2 class="font-semibold text-slate-900">Murid</h2>
    <p class="mt-1 text-sm text-slate-800">{{ $formulir->siswa->nama ?? '-' }} ({{ $formulir->siswa->kelas->nama_kelas ?? '-' }})</p>
    @if($formulir->siswaPihakKedua)
        <p class="mt-1 text-sm text-slate-800">Pihak II: {{ $formulir->siswaPihakKedua->nama }} ({{ $formulir->siswaPihakKedua->kelas->nama_kelas ?? '-' }})</p>
    @endif
</section>

<div class="grid gap-5 lg:grid-cols-2">
    @foreach($definition['sections'] as $section)
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
            <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2>
            <dl class="space-y-4">
                @foreach($section['fields'] as $field)
                    @php($value = $formulir->data[$field['name']] ?? null)
                    @if($field['type'] === 'ratings')
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="bg-rose-50"><tr><th class="p-2">Aspek</th><th class="p-2">Hasil</th></tr></thead>
                                <tbody>
                                    @foreach($field['items'] as $number => $item)
                                        <tr class="border-t"><td class="p-2 text-slate-800">{{ $number }}. {{ $item }}</td><td class="p-2 font-semibold text-slate-800">{{ $value[$number] ?? '-' }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wide text-slate-600">{{ $field['label'] }}</dt>
                            <dd class="mt-1 whitespace-pre-line text-sm leading-relaxed text-slate-900">
                                @if(is_array($value))
                                    {{ implode(', ', array_map(fn ($item) => $field['options'][$item] ?? $item, $value)) ?: '-' }}
                                @elseif($field['type'] === 'select')
                                    {{ $field['options'][$value] ?? '-' }}
                                @else
                                    {{ filled($value) ? $value : '-' }}
                                @endif
                            </dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </section>
    @endforeach
</div>
@endsection
