@extends('layouts.app')
@section('title', 'Biodata Siswa')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <a href="{{ route('siswa.index') }}" class="text-sm font-medium text-brand-700 hover:underline">&larr; Kembali ke Data Siswa</a>
        <h1 class="mt-2 text-2xl font-bold tracking-wide text-slate-800">BIODATA SISWA</h1>
        <p class="mt-1 text-sm text-slate-600">Setiap Anak Unik, Setiap Potensi Berarti</p>
    </div>
    <p class="text-right text-sm font-medium text-brand-700">Guru BK hadir untuk memahami, mendampingi, dan membantu siswa berkembang.</p>
    @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
        <a href="{{ route('siswa.edit', $siswa) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-700">
            Ubah Biodata
        </a>
    @endif
    @if(auth()->user()->isAdmin())
        <a href="{{ route('siswa.akun.edit', $siswa) }}" class="inline-flex items-center justify-center rounded-xl border border-brand-200 bg-white px-4 py-2 text-sm font-medium text-brand-700 shadow-sm hover:bg-brand-50">
            {{ $siswa->user ? 'Kelola Akun Siswa' : 'Buat Akun Siswa' }}
        </a>
    @endif
</div>

<div class="mb-5 rounded-2xl bg-gradient-to-r from-rose-50 to-white p-5 shadow-sm ring-1 ring-rose-100">
    <p class="text-sm font-medium text-brand-700">{{ $siswa->nis }}@if($siswa->nisn) <span class="text-slate-400">&bull;</span> NISN {{ $siswa->nisn }}@endif</p>
    <h2 class="mt-1 text-xl font-semibold text-slate-800">{{ $siswa->nama }}</h2>
    <div class="mt-3 flex flex-wrap gap-2 text-sm text-slate-600">
        @if($siswa->nama_panggilan)<span class="rounded-full bg-white px-3 py-1 ring-1 ring-rose-100">Panggilan: {{ $siswa->nama_panggilan }}</span>@endif
        <span class="rounded-full bg-white px-3 py-1 ring-1 ring-rose-100">{{ $siswa->kelas->nama_kelas ?? '-' }}</span>
        @if($siswa->no_absen)<span class="rounded-full bg-white px-3 py-1 ring-1 ring-rose-100">Absen {{ $siswa->no_absen }}</span>@endif
        <span class="rounded-full bg-white px-3 py-1 ring-1 ring-rose-100">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
        <span class="rounded-full bg-white px-3 py-1 ring-1 ring-rose-100">{{ ucfirst(str_replace('_', ' ', $siswa->status)) }}</span>
    </div>
</div>

<div class="grid gap-5 lg:grid-cols-2">
    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-800">I. Data Pribadi Siswa</h2>
        <dl class="space-y-3 text-sm">
            <div><dt class="text-slate-500">NIS / NISN</dt><dd class="font-medium text-slate-800">{{ $siswa->nis }} / {{ $siswa->nisn ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Nama panggilan</dt><dd class="font-medium text-slate-800">{{ $siswa->nama_panggilan ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Kelas / No. Absen</dt><dd class="font-medium text-slate-800">{{ $siswa->kelas->nama_kelas ?? '-' }} / {{ $siswa->no_absen ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Tempat & tanggal lahir</dt><dd class="font-medium text-slate-800">{{ $siswa->tempat_lahir ?: '-' }}@if($siswa->tanggal_lahir), {{ \Illuminate\Support\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') }}@endif</dd></div>
            <div><dt class="text-slate-500">Jenis kelamin</dt><dd class="font-medium text-slate-800">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</dd></div>
            <div><dt class="text-slate-500">Agama</dt><dd class="font-medium text-slate-800">{{ $siswa->agama ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Alamat lengkap</dt><dd class="whitespace-pre-line font-medium text-slate-800">{{ $siswa->alamat ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Nomor kontak siswa</dt><dd class="font-medium text-slate-800">{{ $siswa->no_hp_siswa ?: '-' }}</dd></div>
        </dl>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-800">II. Data Orang Tua / Wali</h2>
        <dl class="space-y-3 text-sm">
            <div><dt class="text-slate-500">Nama ayah</dt><dd class="font-medium text-slate-800">{{ $siswa->nama_ayah ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Pekerjaan ayah</dt><dd class="font-medium text-slate-800">{{ $siswa->pekerjaan_ayah ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">No. HP ayah</dt><dd class="font-medium text-slate-800">{{ $siswa->no_hp_ayah ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Nama ibu</dt><dd class="font-medium text-slate-800">{{ $siswa->nama_ibu ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Pekerjaan ibu</dt><dd class="font-medium text-slate-800">{{ $siswa->pekerjaan_ibu ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">No. HP ibu</dt><dd class="font-medium text-slate-800">{{ $siswa->no_hp_ibu ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Nama orang tua (umum)</dt><dd class="font-medium text-slate-800">{{ $siswa->nama_ortu ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">No. HP orang tua (umum)</dt><dd class="font-medium text-slate-800">{{ $siswa->no_hp_ortu ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Penghasilan orang tua/wali per bulan</dt><dd class="font-medium text-slate-800">{{ $siswa->penghasilan_orang_tua !== null ? 'Rp ' . number_format((float) $siswa->penghasilan_orang_tua, 0, ',', '.') : '-' }}</dd></div>
            <div><dt class="text-slate-500">Nama wali</dt><dd class="font-medium text-slate-800">{{ $siswa->nama_wali ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Hubungan dengan siswa</dt><dd class="font-medium text-slate-800">{{ $siswa->hubungan_wali ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">No. HP wali</dt><dd class="font-medium text-slate-800">{{ $siswa->no_hp_wali ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Pekerjaan wali</dt><dd class="font-medium text-slate-800">{{ $siswa->pekerjaan_wali ?: '-' }}</dd></div>
        </dl>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-800">III. Data Pendukung</h2>
        <dl class="space-y-3 text-sm">
            <div><dt class="text-slate-500">Hobi / Minat</dt><dd class="whitespace-pre-line font-medium text-slate-800">{{ $siswa->hobi_minat ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Bakat / Potensi</dt><dd class="whitespace-pre-line font-medium text-slate-800">{{ $siswa->bakat_potensi ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Cita-cita</dt><dd class="font-medium text-slate-800">{{ $siswa->cita_cita ?: '-' }}</dd></div>
            <div><dt class="text-slate-500">Kondisi khusus yang perlu diperhatikan</dt><dd class="whitespace-pre-line font-medium text-slate-800">{{ $siswa->kondisi_khusus ?: '-' }}</dd></div>
        </dl>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-800">IV. Catatan Guru BK</h2>
        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $siswa->catatan_guru_bk ?: 'Belum ada catatan Guru BK.' }}</p>
    </section>

    <section class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
        <h2 class="mb-4 border-b border-rose-100 pb-3 text-lg font-semibold text-slate-800">Catatan Biodata Tambahan</h2>
        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $siswa->catatan_penting ?: 'Belum ada catatan.' }}</p>
    </section>
</div>
<p class="mt-6 text-sm text-slate-600">Bimbingan hari ini, kesuksesan esok hari.</p>
@endsection
