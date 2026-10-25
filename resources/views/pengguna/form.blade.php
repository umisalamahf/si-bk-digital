@extends('layouts.app')
@section('title', $pengguna ? 'Ubah Akun' : 'Tambah Akun')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">{{ $pengguna ? 'Ubah Akun' : 'Tambah Akun' }}</h1>
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $pengguna ? route('pengguna.update', $pengguna) : route('pengguna.store') }}"
      class="bg-white rounded-xl shadow p-6 space-y-4 max-w-xl"
      x-data="{ peran: '{{ old('role', $pengguna->role ?? 'guru_bk') }}' }">
    @csrf
    @if($pengguna) @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium mb-1">Peran *</label>
        @if($pengguna)
            <p class="px-3 py-2 bg-slate-100 rounded text-sm">{{ ['admin' => 'Admin', 'guru_bk' => 'Guru BK', 'wali_kelas' => 'Wali Kelas', 'kepala_sekolah' => 'Kepala Sekolah'][$pengguna->role] ?? $pengguna->role }}
                <span class="text-xs text-slate-400">(tidak bisa diubah; kalau salah, buat akun baru)</span></p>
        @else
            <select name="role" x-model="peran" required class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="guru_bk">Guru BK (akses penuh modul BK)</option>
                <option value="wali_kelas">Wali Kelas (hanya siswa & anekdot kelasnya)</option>
                <option value="kepala_sekolah">Kepala Sekolah (rekap, hanya lihat)</option>
                <option value="admin">Admin (akun & data master)</option>
            </select>
        @endif
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Nama *</label>
        <input type="text" name="name" value="{{ old('name', $pengguna->name ?? '') }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email (untuk login) *</label>
        <input type="email" name="email" value="{{ old('email', $pengguna->email ?? '') }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    @if(!$pengguna || in_array($pengguna->role, ['guru_bk', 'wali_kelas']))
    <div class="grid grid-cols-2 gap-4" @if(!$pengguna) x-show="peran === 'guru_bk' || peran === 'wali_kelas'" @endif>
        <div>
            <label class="block text-sm font-medium mb-1">NIP</label>
            <input type="text" name="nip" value="{{ old('nip', $profil->nip ?? '') }}" class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. HP</label>
            <input type="text" name="no_hp" value="{{ old('no_hp', $profil->no_hp ?? '') }}" class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>
    @endif

    <div>
        <label class="block text-sm font-medium mb-1">Password {{ $pengguna ? '(kosongkan kalau tidak diganti)' : '*' }}</label>
        <input type="password" name="password" @if(!$pengguna) required @endif minlength="8" autocomplete="new-password" class="w-full border border-slate-300 rounded px-3 py-2">
        <p class="text-xs text-slate-400 mt-1">Minimal 8 karakter.</p>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Ulangi Password</label>
        <input type="password" name="password_confirmation" autocomplete="new-password" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan</button>
        <a href="{{ route('pengguna.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
