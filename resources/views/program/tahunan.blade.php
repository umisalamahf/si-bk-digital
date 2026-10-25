@extends('layouts.app')
@section('title', 'Program Tahunan BK')

@section('content')
<div class="mb-6">
    <p class="text-sm font-medium tracking-wide text-brand-700">Perencanaan layanan Bimbingan dan Konseling</p>
    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800">Program Tahunan BK</h1>
    <p class="mt-1 text-sm text-slate-600">Tahun Ajaran {{ $tahunAjaran }}</p>
</div>

@if(session('status'))<div class="mb-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

@include('program.fields', [
    'program' => $program,
    'action' => route('program-tahunan.update', $program),
])
@endsection
