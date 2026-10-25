@extends('layouts.app')
@section('title', 'Jadwal & Agenda')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Jadwal Layanan &amp; Agenda BK</h1>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid lg:grid-cols-2 gap-6">

    {{-- ===== JADWAL LAYANAN ===== --}}
    <section>
        <h2 class="font-semibold text-lg mb-3">Jadwal Layanan</h2>
        <form method="POST" action="{{ route('jadwal.store') }}" class="bg-white rounded-xl shadow p-5 space-y-2 mb-4">
            @csrf
            <div class="grid grid-cols-3 gap-2">
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="border border-slate-300 rounded px-2 py-1.5 text-sm">
                <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required class="border border-slate-300 rounded px-2 py-1.5 text-sm" title="Jam mulai">
                <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required class="border border-slate-300 rounded px-2 py-1.5 text-sm" title="Jam selesai">
            </div>
            <input name="jenis_layanan" value="{{ old('jenis_layanan') }}" placeholder="Jenis layanan (mis. Klasikal, Konseling)" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <input name="tempat" value="{{ old('tempat') }}" placeholder="Tempat (mis. Ruang BK)" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <div class="grid grid-cols-2 gap-2">
                <select name="kelas_id" class="border border-slate-300 rounded px-2 py-1.5 text-sm">
                    <option value="">Kelas (opsional)</option>
                    @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(old('kelas_id')==$k->id)>{{ $k->nama_kelas }}</option>@endforeach
                </select>
                <select name="siswa_id" class="border border-slate-300 rounded px-2 py-1.5 text-sm">
                    <option value="">Siswa (opsional)</option>
                    @foreach($siswa as $s)<option value="{{ $s->id }}" @selected(old('siswa_id')==$s->id)>{{ $s->nama }}</option>@endforeach
                </select>
            </div>
            <textarea name="keterangan" rows="2" placeholder="Keterangan" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">{{ old('keterangan') }}</textarea>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded text-sm">+ Tambah Jadwal</button>
        </form>

        <div class="bg-white rounded-xl shadow divide-y">
            @forelse($jadwal as $j)
            <div class="p-4 text-sm">
                <p class="font-medium">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('l, d F Y') }}
                    &bull; {{ substr($j->jam_mulai, 0, 5) }}&ndash;{{ substr($j->jam_selesai, 0, 5) }}</p>
                <p>{{ $j->jenis_layanan }}@if($j->tempat) di {{ $j->tempat }}@endif</p>
                <p class="text-slate-500">
                    @if($j->kelas) Kelas {{ $j->kelas->nama_kelas }} @endif
                    @if($j->siswa) &bull; {{ $j->siswa->nama }} @endif
                </p>
            </div>
            @empty
            <p class="p-6 text-center text-slate-400 text-sm">Belum ada jadwal mendatang.</p>
            @endforelse
        </div>
    </section>

    {{-- ===== KALENDER AGENDA ===== --}}
    <section>
        <h2 class="font-semibold text-lg mb-3">Kalender Agenda BK</h2>
        <form method="POST" action="{{ route('agenda.store') }}" class="bg-white rounded-xl shadow p-5 space-y-2 mb-4">
            @csrf
            <input name="judul" value="{{ old('judul') }}" placeholder="Judul agenda (mis. Asesmen Gaya Belajar Kelas VII)" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-xs text-slate-400">Mulai</label>
                    <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai', now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-400">Selesai</label>
                    <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai', now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                </div>
            </div>
            <textarea name="deskripsi" rows="2" placeholder="Deskripsi" class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">{{ old('deskripsi') }}</textarea>
            <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded text-sm">+ Tambah Agenda</button>
        </form>

        <div class="bg-white rounded-xl shadow divide-y">
            @forelse($agenda as $a)
            <div class="p-4 text-sm">
                <p class="font-medium">{{ $a->judul }}</p>
                <p class="text-slate-500">
                    {{ \Carbon\Carbon::parse($a->tanggal_mulai)->format('d-m-Y') }}
                    @if($a->tanggal_selesai != $a->tanggal_mulai) s/d {{ \Carbon\Carbon::parse($a->tanggal_selesai)->format('d-m-Y') }} @endif
                </p>
                @if($a->deskripsi)<p class="text-slate-600">{{ $a->deskripsi }}</p>@endif
            </div>
            @empty
            <p class="p-6 text-center text-slate-400 text-sm">Belum ada agenda mendatang.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
