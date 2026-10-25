@extends('layouts.app')
@section('title', 'Akun Siswa')

@section('content')
<div class="mx-auto max-w-2xl">
    <a href="{{ route('siswa.show', $siswa) }}" class="text-sm font-medium text-brand-700 hover:underline">&larr; Kembali ke biodata siswa</a>
    <div class="mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100">
        <h1 class="text-2xl font-bold text-slate-800">{{ $siswa->user ? 'Kelola Akun Siswa' : 'Buat Akun Siswa' }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $siswa->nama }} · NIS {{ $siswa->nis }}</p>

        @if($errors->any())
            <div class="mt-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>
        @endif

        @if($siswa->status !== 'aktif')
            <div class="mt-4 rounded-xl bg-amber-50 p-3 text-sm text-amber-900">Akun login hanya tersedia untuk siswa berstatus aktif.</div>
        @else
            <form method="POST" action="{{ route('siswa.akun.update', $siswa) }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email untuk login</label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $siswa->user?->email) }}"
                        class="w-full border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">
                        {{ $siswa->user ? 'Password baru (kosongkan jika tidak diubah)' : 'Password awal (minimal 8 karakter)' }}
                    </label>
                    <input id="password" name="password" type="password" {{ $siswa->user ? '' : 'required' }} minlength="8"
                        class="w-full border border-slate-300 px-3 py-2 text-sm">
                </div>
                <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Simpan Akun</button>
            </form>
        @endif
    </div>
</div>
@endsection
