@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="mb-7">
    <p class="text-sm font-medium text-brand-600 mb-1">{{ now()->translatedFormat('l, d F Y') }}</p>
    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-slate-800 mb-1">Selamat datang, {{ auth()->user()->name }} 👋</h1>
    <p class="text-slate-500">Berikut ringkasan administrasi Bimbingan dan Konseling hari ini.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-rose-100 p-5">
        <div class="flex items-start justify-between">
            <div><p class="text-sm text-slate-500">Total Siswa Aktif</p><p class="text-3xl font-bold text-slate-800 mt-2">{{ $totalSiswa }}</p></div>
            <span class="rounded-xl bg-brand-50 text-brand-600 px-3 py-2 text-lg">♧</span>
        </div>
    </div>
    @if(! auth()->user()->isWaliKelas())
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-rose-100 p-5">
        <div class="flex items-start justify-between"><div><p class="text-sm text-slate-500">Perlu Perhatian</p><p class="text-3xl font-bold text-slate-800 mt-2">{{ $perluPerhatian }}</p></div><span class="rounded-xl bg-amber-50 text-amber-600 px-3 py-2">!</span></div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-rose-100 p-5">
        <div class="flex items-start justify-between"><div><p class="text-sm text-slate-500">Perlu Tindak Lanjut</p><p class="text-3xl font-bold text-slate-800 mt-2">{{ $perluTindakLanjut }}</p></div><span class="rounded-xl bg-rose-50 text-rose-600 px-3 py-2">↗</span></div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-rose-100 p-5">
        <div class="flex items-start justify-between"><div><p class="text-sm text-slate-500">Absensi Hari Ini</p><p class="text-3xl font-bold text-slate-800 mt-2">{{ $absenHariIni }}</p></div><span class="rounded-xl bg-sky-50 text-sky-600 px-3 py-2">✓</span></div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm ring-1 ring-rose-100 p-5">
        <div class="flex items-start justify-between"><div><p class="text-sm text-slate-500">Anekdot Berat (total)</p><p class="text-3xl font-bold text-slate-800 mt-2">{{ $anekdotBeratBelumDitindak }}</p></div><span class="rounded-xl bg-violet-50 text-violet-600 px-3 py-2">✦</span></div>
    </div>
    @endif
</div>

<div class="mt-8 mb-3">
    <h2 class="text-lg font-semibold text-slate-800">Akses cepat</h2>
</div>
<div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
    <a href="{{ route('siswa.index') }}" class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="font-semibold text-slate-800">📋 Data Siswa</p>
        <p class="text-sm text-slate-500">
            @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk()) Kelola data siswa, tambah/ubah/hapus @else Lihat data siswa @endif
        </p>
    </a>
    @if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
    <a href="{{ route('absensi.index') }}" class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="font-semibold text-slate-800">✅ Input Absensi</p>
        <p class="text-sm text-slate-500">Catat kehadiran 1 kelas sekaligus</p>
    </a>
    @endif
    @if(! auth()->user()->isWaliKelas())
    <a href="{{ route('poin.index') }}" class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="font-semibold text-slate-800">⭐ Poin Siswa</p>
        <p class="text-sm text-slate-500">Lihat rekap poin pelanggaran & reward</p>
    </a>
    @endif
    @if(auth()->user()->isGuruBk() || auth()->user()->isWaliKelas())
    <a href="{{ route('anekdot.index') }}" class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5 hover:shadow-md hover:-translate-y-0.5 transition block">
        <p class="font-semibold text-slate-800">📝 Catatan Anekdot</p>
        <p class="text-sm text-slate-500">Catatan perilaku & kejadian khusus siswa</p>
    </a>
    @endif
</div>

@if(auth()->user()->isAdmin() || auth()->user()->isGuruBk())
<div class="mt-8 grid lg:grid-cols-2 gap-5">
    <div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-slate-800">📅 Jadwal Layanan Terdekat</h2>
            <a href="{{ route('jadwal.index') }}" class="text-xs text-brand-600 hover:underline">Kelola</a>
        </div>
        @forelse($jadwalTerdekat as $j)
        <div class="py-2 border-b last:border-b-0 text-sm">
            <p class="font-medium">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('D, d M') }}
                &bull; {{ substr($j->jam_mulai, 0, 5) }}&ndash;{{ substr($j->jam_selesai, 0, 5) }}</p>
            <p class="text-slate-600">{{ $j->jenis_layanan }}@if($j->tempat) di {{ $j->tempat }}@endif
                @if($j->nama_kelas) &bull; {{ $j->nama_kelas }}@endif
                @if($j->nama_siswa) &bull; {{ $j->nama_siswa }}@endif</p>
        </div>
        @empty
        <p class="text-sm text-slate-400">Belum ada jadwal mendatang.</p>
        @endforelse
    </div>

    <div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-5">
        <h2 class="font-semibold text-slate-800 mb-3">🗓️ Agenda Hari Ini</h2>
        @forelse($agendaHariIni as $a)
        <div class="py-2 border-b last:border-b-0 text-sm">
            <p class="font-medium">{{ $a->judul }}</p>
            @if($a->deskripsi)<p class="text-slate-600">{{ $a->deskripsi }}</p>@endif
        </div>
        @empty
        <p class="text-sm text-slate-400">Tidak ada agenda hari ini.</p>
        @endforelse
    </div>
</div>
@endif
@endsection
