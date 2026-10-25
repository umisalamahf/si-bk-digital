@extends('layouts.app')
@section('title', 'Absensi')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-2">Input Absensi Kelas</h1>
<p class="text-slate-500 mb-5">Pilih kelas dan tanggal, lalu catat kehadiran siswa.</p>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<form method="GET" class="flex flex-col sm:flex-row sm:flex-wrap gap-2 mb-6 bg-white p-4 rounded-2xl ring-1 ring-rose-100 shadow-sm">
    <select name="kelas_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm" onchange="this.form.submit()">
        <option value="">-- Pilih Kelas --</option>
        @foreach($kelas as $k)
            <option value="{{ $k->id }}" @selected($kelasId==$k->id)>{{ $k->nama_kelas }}</option>
        @endforeach
    </select>
    <input type="date" name="tanggal" value="{{ $tanggal }}" class="border border-slate-200 rounded-xl px-3 py-2 text-sm" onchange="this.form.submit()">
    <a href="{{ route('absensi.rekap') }}" class="sm:ml-auto text-sm text-brand-700 self-center hover:underline">Lihat Rekap Bulanan →</a>
</form>

@if($kelasId && $siswa->count())
<form method="POST" action="{{ route('absensi.simpan') }}" class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm p-4 sm:p-5">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelasId }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">

    <p class="text-sm text-slate-500 mb-4">
        📌 Semua baris di bawah disimpan SEKALIGUS dalam satu transaksi database.
        Kalau salah satu gagal, semuanya dibatalkan (tidak ada yang tersimpan setengah-setengah).
    </p>

    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-50 text-left">
            <tr><th class="p-2">Nama</th><th class="p-2">Status</th><th class="p-2">Keterangan</th></tr>
        </thead>
        <tbody>
            @foreach($siswa as $s)
            <tr class="border-t">
                <td class="p-2">{{ $s->nama }}</td>
                <td class="p-2">
                    <select name="status[{{ $s->id }}]" class="border border-slate-200 rounded-lg px-2 py-1">
                        <option value="hadir" @selected($s->status_absen=='hadir')>Hadir</option>
                        <option value="sakit" @selected($s->status_absen=='sakit')>Sakit</option>
                        <option value="izin" @selected($s->status_absen=='izin')>Izin</option>
                        <option value="alpa" @selected($s->status_absen=='alpa')>Alpa</option>
                    </select>
                </td>
                <td class="p-2">
                    <input type="text" name="keterangan[{{ $s->id }}]" class="border border-slate-200 rounded-lg px-2 py-1 w-full" placeholder="opsional">
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <button class="mt-4 bg-brand-600 hover:bg-brand-700 text-white px-5 py-2 rounded-xl shadow-sm">Simpan Absensi</button>
</form>
@elseif($kelasId)
    <p class="text-slate-400">Tidak ada siswa aktif di kelas ini.</p>
@else
    <p class="text-slate-400">Pilih kelas dulu untuk mulai input absensi.</p>
@endif
@endsection
