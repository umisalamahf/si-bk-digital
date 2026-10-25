@extends('layouts.app')
@section('title', $definition['title'])

@section('content')
<div class="mb-6">
    <a href="{{ route('bk-formulir.index') }}" class="text-sm font-semibold text-brand-700 hover:underline">&larr; Kembali ke Formulir Layanan BK</a>
    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">{{ $definition['title'] }}</h1>
    <p class="mt-1 text-sm text-slate-600">Data layanan bersifat privat dan hanya dapat diakses Guru BK.</p>
</div>

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-900">
        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('bk-formulir.store', $jenis) }}" class="space-y-5">
    @csrf
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-900">Identitas Murid dan Tanggal</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label for="siswa_id" class="mb-1 block text-sm font-semibold text-slate-800">
                    {{ $jenis === 'mediasi' ? 'Murid Pihak I' : 'Murid' }} <span class="text-rose-700">*</span>
                </label>
                <select id="siswa_id" name="siswa_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">
                    <option value="">Pilih murid</option>
                    @foreach($siswa as $murid)
                        <option value="{{ $murid->id }}" @selected(old('siswa_id') == $murid->id)>{{ $murid->nama }} — {{ $murid->kelas->nama_kelas ?? '-' }}</option>
                    @endforeach
                </select>
            </div>
            @if($jenis === 'mediasi')
                <div>
                    <label for="siswa_pihak_2_id" class="mb-1 block text-sm font-semibold text-slate-800">Murid Pihak II <span class="text-rose-700">*</span></label>
                    <select id="siswa_pihak_2_id" name="siswa_pihak_2_id" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">
                        <option value="">Pilih murid</option>
                        @foreach($siswa as $murid)
                            <option value="{{ $murid->id }}" @selected(old('siswa_pihak_2_id') == $murid->id)>{{ $murid->nama }} — {{ $murid->kelas->nama_kelas ?? '-' }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label for="tanggal" class="mb-1 block text-sm font-semibold text-slate-800">Tanggal <span class="text-rose-700">*</span></label>
                <input id="tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">
            </div>
        </div>
    </section>

    @foreach($definition['sections'] as $section)
        <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
            <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-900">{{ $section['title'] }}</h2>
            <div class="space-y-4">
                @foreach($section['fields'] as $field)
                    @php($fieldValue = old('data.' . $field['name']))
                    @if($field['type'] === 'ratings')
                        <p class="text-sm text-slate-700">{{ $field['label'] }}</p>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-160 text-left text-sm">
                                <thead class="bg-rose-50 text-slate-800">
                                    <tr><th class="p-2">Aspek yang diamati</th>@foreach(['SL' => 'Selalu', 'SR' => 'Sering', 'KD' => 'Kadang-kadang', 'TP' => 'Tidak Pernah'] as $key => $label)<th class="p-2 text-center">{{ $label }}</th>@endforeach</tr>
                                </thead>
                                <tbody>
                                    @foreach($field['items'] as $number => $item)
                                        <tr class="border-t border-slate-100">
                                            <td class="p-2 text-slate-800">{{ $number }}. {{ $item }}</td>
                                            @foreach(['SL', 'SR', 'KD', 'TP'] as $option)
                                                <td class="p-2 text-center"><input type="radio" name="data[{{ $field['name'] }}][{{ $number }}]" value="{{ $option }}" required @checked(($fieldValue[$number] ?? '') === $option) aria-label="{{ $item }}: {{ $option }}"></td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($field['type'] === 'textarea')
                        <div>
                            <label for="field-{{ $field['name'] }}" class="mb-1 block text-sm font-medium text-slate-800">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-rose-700">*</span>@endif</label>
                            <textarea id="field-{{ $field['name'] }}" name="data[{{ $field['name'] }}]" rows="3" @required($field['required'] ?? false) class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">{{ $fieldValue }}</textarea>
                        </div>
                    @elseif($field['type'] === 'select')
                        <div>
                            <label for="field-{{ $field['name'] }}" class="mb-1 block text-sm font-medium text-slate-800">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-rose-700">*</span>@endif</label>
                            <select id="field-{{ $field['name'] }}" name="data[{{ $field['name'] }}]" @required($field['required'] ?? false) class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">
                                <option value="">Pilih hasil</option>
                                @foreach($field['options'] as $value => $label)<option value="{{ $value }}" @selected($fieldValue === $value)>{{ $label }}</option>@endforeach
                            </select>
                        </div>
                    @elseif($field['type'] === 'date')
                        <div>
                            <label for="field-{{ $field['name'] }}" class="mb-1 block text-sm font-medium text-slate-800">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-rose-700">*</span>@endif</label>
                            <input id="field-{{ $field['name'] }}" type="date" name="data[{{ $field['name'] }}]" value="{{ $fieldValue }}" @required($field['required'] ?? false) class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">
                        </div>
                    @elseif($field['type'] === 'checkbox')
                        <fieldset>
                            <legend class="mb-2 text-sm font-medium text-slate-800">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-rose-700">*</span>@endif</legend>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($field['options'] as $value => $label)
                                    <label class="flex items-start gap-2 rounded-lg bg-rose-50/60 px-3 py-2 text-sm text-slate-800">
                                        <input type="checkbox" name="data[{{ $field['name'] }}][]" value="{{ $value }}" @checked(is_array($fieldValue) && in_array($value, $fieldValue, true))>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>
                    @else
                        <div>
                            <label for="field-{{ $field['name'] }}" class="mb-1 block text-sm font-medium text-slate-800">{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-rose-700">*</span>@endif</label>
                            <input id="field-{{ $field['name'] }}" type="text" name="data[{{ $field['name'] }}]" value="{{ $fieldValue }}" maxlength="{{ $field['max'] ?? 5000 }}" @required($field['required'] ?? false) class="w-full rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-900">
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endforeach

    <div class="flex flex-wrap gap-2">
        <button type="submit" class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">Simpan Formulir</button>
        <a href="{{ route('bk-formulir.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 hover:bg-slate-50">Batal</a>
    </div>
</form>
@endsection
