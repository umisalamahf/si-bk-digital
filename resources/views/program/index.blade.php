@extends('layouts.app')
@section('title', 'Program BK')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-1">Program BK</h1>
<div class="mb-4 flex flex-wrap gap-2">
    <a href="{{ route('program.index', ['jenis_program' => 'tahunan', 'tahun_ajaran' => $tahunAjaran]) }}" class="rounded-xl px-4 py-2 text-sm font-semibold {{ $jenisProgram === 'tahunan' ? 'bg-brand-600 text-white' : 'border border-rose-200 bg-white text-slate-700 hover:bg-rose-50' }}">Program Tahunan</a>
    <a href="{{ route('program.index', ['jenis_program' => 'semester_ganjil', 'tahun_ajaran' => $tahunAjaran]) }}" class="rounded-xl px-4 py-2 text-sm font-semibold {{ $jenisProgram === 'semester_ganjil' ? 'bg-brand-600 text-white' : 'border border-rose-200 bg-white text-slate-700 hover:bg-rose-50' }}">Program Semester Ganjil</a>
    <a href="{{ route('program.index', ['jenis_program' => 'semester_genap', 'tahun_ajaran' => $tahunAjaran]) }}" class="rounded-xl px-4 py-2 text-sm font-semibold {{ $jenisProgram === 'semester_genap' ? 'bg-brand-600 text-white' : 'border border-rose-200 bg-white text-slate-700 hover:bg-rose-50' }}">Program Semester Genap</a>
</div>
<p class="mb-4 text-slate-600">{{ ucfirst(str_replace('_', ' ', $jenisProgram)) }} · Tahun Ajaran {{ $tahunAjaran }}</p>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<form method="POST" action="{{ route('program.update', $program) }}" class="max-w-3xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100">
    @csrf @method('PUT')
    <div>
        <label class="block text-sm font-medium mb-1">Capaian Layanan BK</label>
        <textarea name="capaian_layanan" rows="4" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('capaian_layanan', $program->capaian_layanan) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Alur Tujuan Layanan</label>
        <textarea name="alur_tujuan" rows="4" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('alur_tujuan', $program->alur_tujuan) }}</textarea>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Action Plan</label>
        <textarea name="action_plan" rows="4" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('action_plan', $program->action_plan) }}</textarea>
    </div>
    <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan</button>
</form>
@endsection
