@extends('layouts.app')
@section('title', 'Ajuan Konseling')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Ajuan Konseling</h1>
    <p class="mt-1 text-sm text-slate-600">Buka slot yang dapat langsung dipesan siswa dan kelola ajuan yang masuk.</p>
</div>

@if(session('status'))<div class="mb-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

<section class="mb-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
    <h2 class="text-lg font-semibold text-slate-800">Buka Slot Konseling</h2>
    <form method="POST" action="{{ route('booking-staf.slot.store') }}" class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
        @csrf
        <div><label class="mb-1 block text-sm text-slate-700">Tanggal</label><input type="date" name="tanggal" min="{{ today()->toDateString() }}" value="{{ old('tanggal') }}" required class="w-full border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-sm text-slate-700">Mulai</label><input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required class="w-full border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-sm text-slate-700">Selesai</label><input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required class="w-full border border-slate-300 px-3 py-2 text-sm"></div>
        <div><label class="mb-1 block text-sm text-slate-700">Tempat (opsional)</label><input type="text" name="tempat" value="{{ old('tempat') }}" maxlength="150" class="w-full border border-slate-300 px-3 py-2 text-sm"></div>
        <div class="flex items-end"><button class="w-full rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Buka Slot</button></div>
    </form>
</section>

<section class="space-y-4">
    @forelse($slots as $slot)
        <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="font-semibold text-slate-800">{{ $slot->tanggal->translatedFormat('d F Y') }} · {{ substr($slot->jam_mulai, 0, 5) }}–{{ substr($slot->jam_selesai, 0, 5) }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ $slot->tempat ?: 'Tempat ditentukan kemudian' }} · {{ ucfirst($slot->status) }}</p>
                </div>
                @if($slot->status === 'tersedia')
                    <form method="POST" action="{{ route('booking-staf.slot.close', $slot) }}" onsubmit="return confirm('Tutup slot ini?')">
                        @csrf @method('PATCH')
                        <button class="rounded-lg border border-rose-200 px-3 py-1.5 text-sm text-rose-800 hover:bg-rose-50">Tutup Slot</button>
                    </form>
                @endif
            </div>
            @foreach($slot->ajuan as $ajuan)
                <div class="mt-4 border-t border-rose-100 pt-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div class="text-sm">
                            <p class="font-semibold text-slate-800">{{ $ajuan->siswa->nama }} <span class="font-normal text-slate-500">({{ $ajuan->siswa->kelas->nama_kelas ?? '-' }})</span></p>
                            <p class="mt-1 whitespace-pre-line text-slate-600">{{ $ajuan->keperluan ?: 'Keperluan tidak dicantumkan.' }}</p>
                            <p class="mt-1 text-xs text-slate-500">Status: {{ ucfirst($ajuan->status) }}</p>
                        </div>
                        @if($ajuan->status === 'dipesan')
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('booking-staf.ajuan.update', $ajuan) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="selesai"><button class="rounded-lg bg-emerald-50 px-3 py-1.5 text-sm font-medium text-emerald-800">Tandai Selesai</button></form>
                                <form method="POST" action="{{ route('booking-staf.ajuan.update', $ajuan) }}" onsubmit="return confirm('Batalkan booking ini?')">@csrf @method('PATCH')<input type="hidden" name="status" value="dibatalkan"><button class="rounded-lg bg-rose-50 px-3 py-1.5 text-sm font-medium text-rose-800">Batalkan</button></form>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </article>
    @empty
        <div class="rounded-2xl bg-white p-8 text-center text-sm text-slate-500 ring-1 ring-rose-100">Belum ada slot konseling.</div>
    @endforelse
</section>
<div class="mt-5">{{ $slots->links() }}</div>
@endsection
