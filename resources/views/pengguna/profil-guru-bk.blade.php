@extends('layouts.app')
@section('title', 'Profil Guru BK')

@section('content')
<div class="mb-5">
    <h1 class="text-2xl font-bold text-slate-800">Profil Guru BK</h1>
    <p class="mt-1 text-sm text-slate-600">Lengkapi nama asli dan NIP agar tampil pada akun serta bagian pengesahan RPL.</p>
</div>

@if(session('status'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800">
        <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('profil-guru-bk.update') }}" class="max-w-xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100">
    @csrf
    @method('PUT')

    <div>
        <label for="nama" class="mb-1 block text-sm font-semibold text-slate-800">Nama Lengkap *</label>
        <input id="nama" name="nama" value="{{ old('nama', $profil->nama) }}" required maxlength="150" autocomplete="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
    </div>

    <div>
        <label for="nip" class="mb-1 block text-sm font-semibold text-slate-800">NIP</label>
        <input id="nip" name="nip" value="{{ old('nip', $profil->nip) }}" maxlength="30" autocomplete="off" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        <p class="mt-1 text-xs text-slate-600">Nama dan NIP ini akan dipakai untuk pengesahan RPL berikutnya dan ditampilkan pada nama akun.</p>
    </div>

    <div class="flex flex-wrap gap-2 pt-2">
        <button class="rounded-xl bg-brand-600 px-5 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan Profil</button>
        <a href="{{ route('rpl.index') }}" class="rounded-xl border border-slate-300 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali ke RPL</a>
    </div>
</form>
@endsection
