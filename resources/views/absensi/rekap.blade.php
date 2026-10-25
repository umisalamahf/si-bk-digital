@extends('layouts.app')
@section('title', 'Rekap Absensi')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-2">Rekap Absensi Bulanan</h1>
<p class="text-slate-500 mb-5">Pantau ringkasan kehadiran siswa berdasarkan kelas dan periode.</p>
<p class="text-sm text-slate-500 mb-4">📌 Data ini dihasilkan oleh <code>CALL sp_rekap_absensi()</code> — procedure di database, bukan dihitung di PHP.</p>

<form method="GET" class="flex flex-col sm:flex-row sm:flex-wrap gap-2 mb-6 bg-white p-4 rounded-2xl ring-1 ring-rose-100 shadow-sm">
    <select name="kelas_id" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        <option value="">-- Pilih Kelas --</option>
        @foreach($kelas as $k)<option value="{{ $k->id }}" @selected($kelasId==$k->id)>{{ $k->nama_kelas }}</option>@endforeach
    </select>
    <select name="bulan" class="border border-slate-200 rounded-xl px-3 py-2 text-sm">
        @foreach(range(1,12) as $b)<option value="{{ $b }}" @selected($bulan==$b)>{{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}</option>@endforeach
    </select>
    <input type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2100" class="border border-slate-200 rounded-xl px-3 py-2 text-sm sm:w-24">
    <button class="bg-brand-50 hover:bg-brand-100 text-brand-700 px-4 py-2 rounded-xl text-sm font-medium">Tampilkan</button>
    @if($kelasId)
        <a href="{{ route('absensi.rekap.cetak', ['kelas_id' => $kelasId, 'bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-xl text-sm shadow-sm">Cetak PDF</a>
    @endif
</form>

<div class="bg-white rounded-2xl ring-1 ring-rose-100 shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-brand-50 text-left">
            <tr><th class="p-3">Nama</th><th class="p-3">Hadir</th><th class="p-3">Sakit</th><th class="p-3">Izin</th><th class="p-3">Alpa</th></tr>
        </thead>
        <tbody>
            @forelse($rekap as $r)
            <tr class="border-t">
                <td class="p-3">{{ $r->nama }}</td>
                <td class="p-3">{{ $r->hadir }}</td>
                <td class="p-3">{{ $r->sakit }}</td>
                <td class="p-3">{{ $r->izin }}</td>
                <td class="p-3">{{ $r->alpa }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="p-6 text-center text-slate-400">Pilih kelas dulu untuk lihat rekap.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
