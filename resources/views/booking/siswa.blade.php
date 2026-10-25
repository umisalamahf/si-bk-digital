@extends('layouts.app')
@section('title', 'Booking Konseling')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-800">Booking Konseling</h1>
    <p class="mt-1 text-sm text-slate-600">Halo, {{ $siswa->nama }}. Pilih waktu yang tersedia untuk membuat booking konseling.</p>
</div>

@if(session('status'))<div class="mb-4 rounded-xl bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>@endif
@if($errors->any())<div class="mb-4 rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $errors->first() }}</div>@endif

<section class="mb-7">
    <h2 class="mb-3 text-lg font-semibold text-slate-800">Slot Tersedia</h2>
    <div class="grid gap-3 md:grid-cols-2">
        @forelse($slots as $slot)
            <article class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
                <p class="font-semibold text-slate-800">{{ $slot->tanggal->translatedFormat('d F Y') }}</p>
                <p class="mt-1 text-sm text-slate-600">{{ substr($slot->jam_mulai, 0, 5) }}–{{ substr($slot->jam_selesai, 0, 5) }} · {{ $slot->tempat ?: 'Tempat ditentukan kemudian' }}</p>
                <p class="mt-1 text-sm text-slate-600">Guru BK: {{ $slot->guruBk->name }}</p>
                <form method="POST" action="{{ route('booking-siswa.book') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="slot_konseling_id" value="{{ $slot->id }}">
                    <div>
                        <label for="keperluan-{{ $slot->id }}" class="mb-1 block text-sm font-medium text-slate-700">Keperluan (opsional)</label>
                        <textarea id="keperluan-{{ $slot->id }}" name="keperluan" rows="2" maxlength="2000" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old('keperluan') }}</textarea>
                    </div>
                    <button class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Pesan Slot</button>
                </form>
            </article>
        @empty
            <p class="rounded-2xl bg-white p-5 text-sm text-slate-500 ring-1 ring-rose-100">Belum ada slot yang tersedia.</p>
        @endforelse
    </div>
</section>

<section>
    <h2 class="mb-3 text-lg font-semibold text-slate-800">Riwayat Booking Saya</h2>
    <div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-rose-100">
        <table class="w-full text-left text-sm">
            <thead class="bg-brand-50 text-slate-700"><tr><th class="p-3">Tanggal & Waktu</th><th class="p-3">Guru BK</th><th class="p-3">Keperluan</th><th class="p-3">Status</th></tr></thead>
            <tbody>
                @forelse($ajuan as $item)
                    <tr class="border-t border-rose-50">
                        <td class="p-3">{{ $item->slot->tanggal->translatedFormat('d F Y') }}<br>{{ substr($item->slot->jam_mulai, 0, 5) }}–{{ substr($item->slot->jam_selesai, 0, 5) }}</td>
                        <td class="p-3">{{ $item->slot->guruBk->name }}</td>
                        <td class="max-w-sm whitespace-pre-line p-3">{{ $item->keperluan ?: '-' }}</td>
                        <td class="p-3">{{ ucfirst($item->status) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="p-5 text-center text-slate-500">Belum ada booking konseling.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
