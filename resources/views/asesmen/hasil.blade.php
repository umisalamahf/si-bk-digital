@extends('layouts.app')
@section('title', 'Hasil Asesmen')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Hasil Asesmen Siswa</h1>
<form method="GET" class="flex flex-wrap items-end gap-2 mb-4 bg-white p-4 rounded-xl shadow">
    <div><label class="block text-xs text-slate-500 mb-1">Tanggal Mulai</label><input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="border border-slate-300 rounded px-3 py-2 text-sm"></div>
    <div><label class="block text-xs text-slate-500 mb-1">Tanggal Selesai</label><input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}" class="border border-slate-300 rounded px-3 py-2 text-sm"></div>
    <button class="bg-slate-600 text-white px-4 py-2 rounded text-sm">Filter</button>
    <a href="{{ route('asesmen.hasil.cetak', request()->only('tanggal_mulai', 'tanggal_selesai')) }}" target="_blank" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">Cetak PDF</a>
</form>
<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Siswa</th><th class="p-3">Instrumen</th><th class="p-3">Skor &amp; Kategori</th><th class="p-3">Kesimpulan dan Respons Terbuka</th></tr>
        </thead>
        <tbody>
            @forelse($jawaban as $j)
            <tr class="border-t">
                <td class="p-3">{{ $j->siswa->nama ?? '-' }}</td>
                <td class="p-3">{{ $j->instrumen->nama_instrumen ?? '-' }}</td>
                <td class="p-3">
                    @if($j->hasil)
                        @if(($j->hasil->detail_skor['mode'] ?? null) === 'profile')
                            <p class="font-semibold text-slate-900">{{ $j->hasil->kategori_dominan_1 ?? 'Pemetaan refleksi' }}</p>
                            <p class="mt-1 text-xs text-slate-500">Skor dibaca per aspek; tidak ada skor total pembanding.</p>
                        @else
                            <p class="font-semibold text-slate-900">{{ $j->hasil->skor_total ?? '-' }} · {{ $j->hasil->kategori_dominan_1 ?? '-' }}</p>
                        @endif
                        @if($j->hasil && !empty($j->hasil->detail_skor['aspek']))
                            <dl class="mt-2 space-y-1 text-xs text-slate-700">
                                @foreach($j->hasil->detail_skor['aspek'] as $aspek => $skor)
                                    <div class="flex justify-between gap-3">
                                        <dt>{{ $aspek }}</dt>
                                        <dd class="text-right font-semibold">
                                            {{ $skor }}
                                            @if(($j->hasil->detail_skor['mode'] ?? null) === 'profile')
                                                / 16 · {{ $j->hasil->detail_skor['interpretasi_aspek'][$aspek] ?? '' }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                            @if(($j->hasil->detail_skor['mode'] ?? null) === 'profile' && !empty($j->hasil->detail_skor['aspek_disorot']))
                                <p class="mt-2 text-xs font-semibold text-brand-700">Area yang paling terasa sesuai: {{ implode(', ', $j->hasil->detail_skor['aspek_disorot']) }}</p>
                            @elseif(!empty($j->hasil->detail_skor['aspek_prioritas']))
                                <p class="mt-2 text-xs font-semibold text-brand-700">Aspek prioritas: {{ implode(', ', $j->hasil->detail_skor['aspek_prioritas']) }}</p>
                            @endif
                        @endif
                    @else
                        -
                    @endif
                </td>
                <td class="p-3">
                    <p class="text-slate-900">{{ $j->hasil->kesimpulan ?? 'Belum dihitung' }}</p>
                    @if(!empty($j->hasil->detail_skor['disclaimer']))
                        <p class="mt-2 text-xs leading-relaxed text-amber-800">{{ $j->hasil->detail_skor['disclaimer'] }}</p>
                    @endif
                    @if($j->respons_teks)
                        <details class="mt-2">
                            <summary class="cursor-pointer text-xs font-semibold text-brand-700">Lihat respons terbuka</summary>
                            <dl class="mt-2 space-y-2 text-xs">
                                @foreach($j->respons_teks as $key => $value)
                                    <div><dt class="font-semibold text-slate-800">{{ is_numeric($key) ? 'Pertanyaan ' . $key : str_replace('_', ' ', ucfirst($key)) }}</dt><dd class="mt-0.5 whitespace-pre-line text-slate-700">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</dd></div>
                                @endforeach
                            </dl>
                        </details>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="p-6 text-center text-slate-600">Belum ada siswa yang mengisi asesmen.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $jawaban->links() }}</div>
<h2 class="text-xl font-semibold text-slate-800 mt-8 mb-3">Rekap Pilihan Sosiometri</h2>
<p class="text-sm text-slate-500 mb-3">Jumlah pemilihan ditampilkan sebagai data mentah; interpretasi dan tindak lanjut mengikuti pedoman Guru BK.</p>
<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Sesi</th><th class="p-3">Kelas</th><th class="p-3">Instrumen</th><th class="p-3">Jenis Pilihan</th><th class="p-3">Butir</th><th class="p-3">Siswa Terpilih</th><th class="p-3">Pilihan I / II / III</th><th class="p-3">Jumlah Dipilih</th></tr>
        </thead>
        <tbody>
            @forelse($rekapSosiometri as $baris)
                <tr class="border-t">
                    <td class="p-3">#{{ $baris->link_id }}</td>
                    <td class="p-3">{{ $baris->nama_kelas }}</td>
                    <td class="p-3">{{ $baris->nama_instrumen }}</td>
                    <td class="p-3">{{ $baris->kategori }}</td>
                    <td class="p-3">{{ $baris->pertanyaan }}</td>
                    <td class="p-3">{{ $baris->siswa_dipilih }}</td>
                    <td class="p-3">{{ $baris->pilihan_pertama }} / {{ $baris->pilihan_kedua }} / {{ $baris->pilihan_ketiga }}</td>
                    <td class="p-3">{{ $baris->jumlah_dipilih }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="p-6 text-center text-slate-600">Belum ada pilihan sosiometri pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
