@extends('layouts.app')
@section('title', 'Program Semester BK')

@section('content')
<div class="mb-6">
    <p class="text-sm font-medium tracking-wide text-brand-700">Perencanaan layanan Bimbingan dan Konseling</p>
    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">Program Semester BK</h1>
    <p class="mt-1 text-sm text-slate-600">Tahun Ajaran {{ $tahunAjaran }}</p>
</div>

<nav class="mb-5 flex flex-wrap gap-2" aria-label="Pilihan semester">
    <a href="{{ route('program-semester.index', ['jenis_program' => 'semester_ganjil', 'tahun_ajaran' => $tahunAjaran]) }}"
       @if($jenisProgram === 'semester_ganjil') aria-current="page" @endif
       class="rounded-xl px-4 py-2 text-sm font-semibold {{ $jenisProgram === 'semester_ganjil' ? 'bg-brand-600 text-white' : 'border border-rose-200 bg-white text-slate-700 hover:bg-rose-50' }}">Semester Ganjil</a>
    <a href="{{ route('program-semester.index', ['jenis_program' => 'semester_genap', 'tahun_ajaran' => $tahunAjaran]) }}"
       @if($jenisProgram === 'semester_genap') aria-current="page" @endif
       class="rounded-xl px-4 py-2 text-sm font-semibold {{ $jenisProgram === 'semester_genap' ? 'bg-brand-600 text-white' : 'border border-rose-200 bg-white text-slate-700 hover:bg-rose-50' }}">Semester Genap</a>
</nav>

@if(session('status'))<div class="mb-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

@include('program.fields', [
    'program' => $program,
    'action' => route('program-semester.update', $program),
])
@endsection
