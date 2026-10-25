@extends('layouts.app')
@section('title', 'Daftar Kode Pelanggaran')

@section('content')
<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Kode Pelanggaran Siswa</h1>
        <p class="mt-1 text-sm text-slate-600">Master ini menjadi sumber bobot poin pada pencatatan pelanggaran.</p>
    </div>
    <a href="{{ route('jenis-pelanggaran.create') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700">+ Tambah Kode</a>
</div>

@if(session('status'))
    <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-3 text-sm text-rose-800">{{ session('status') }}</div>
@endif
@if(session('error'))
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">{{ session('error') }}</div>
@endif

<div class="overflow-x-auto rounded-2xl bg-white shadow-sm ring-1 ring-rose-100">
    <table class="w-full text-left text-sm">
        <thead class="bg-brand-50 text-slate-800">
            <tr>
                <th class="p-3">Kode</th>
                <th class="p-3">Jenis Pelanggaran</th>
                <th class="p-3">Kategori Resmi</th>
                <th class="p-3">Bobot Poin</th>
                <th class="p-3">Penanganan</th>
                <th class="p-3">Catatan Digunakan</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pelanggaran as $item)
                <tr class="border-t border-slate-100">
                    <td class="p-3 font-semibold text-slate-800">{{ $item->kode_pelanggaran ?? '—' }}</td>
                    <td class="p-3 text-slate-800">
                        <div class="font-medium">{{ $item->nama_pelanggaran }}</div>
                        <div class="mt-1 text-xs text-slate-600">Tingkat sistem: {{ ucfirst($item->kategori) }}</div>
                    </td>
                    <td class="p-3 text-slate-700">{{ $item->kategori_kelompok ?? '—' }}</td>
                    <td class="p-3 font-semibold text-slate-800">{{ $item->bobot_point }}</td>
                    <td class="p-3 text-slate-700">{{ $item->penanganan ?? '—' }}</td>
                    <td class="p-3 text-slate-700">{{ $item->poin_siswa_count }}</td>
                    <td class="p-3">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('jenis-pelanggaran.edit', $item) }}" class="rounded-lg border border-rose-200 px-3 py-1.5 font-medium text-rose-800 hover:bg-rose-50">Ubah</a>
                            @if((int) $item->poin_siswa_count === 0)
                                <form method="POST" action="{{ route('jenis-pelanggaran.destroy', $item) }}" onsubmit="return confirm('Hapus kode pelanggaran ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg border border-slate-200 px-3 py-1.5 font-medium text-slate-700 hover:bg-slate-50">Hapus</button>
                                </form>
                            @else
                                <span class="self-center text-xs text-slate-500">Sudah dipakai</span>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-8 text-center text-slate-600">Belum ada kode pelanggaran. Tambahkan master agar dapat dipilih saat mencatat poin.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $pelanggaran->links() }}</div>

<section class="mt-8 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-rose-100">
    <h2 class="text-lg font-bold text-slate-800">Rujukan Akumulasi Poin dan Tindak Lanjut</h2>
    <p class="mt-1 text-sm text-slate-600">Rujukan ini mengikuti daftar resmi. Perhitungan status rekap yang sudah berjalan tidak diubah.</p>
    <p class="mt-2 rounded-lg bg-rose-50 p-3 text-sm text-rose-900">Data sumber memuat satu kriteria yang menarget orientasi seksual. Kriteria tersebut tidak dimasukkan sebagai pelanggaran; gunakan aturan berbasis tindakan yang objektif dan berlaku setara bagi semua siswa bila sekolah ingin menggantinya.</p>
    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-160 text-left text-sm">
            <thead class="bg-brand-50 text-slate-800">
                <tr><th class="p-3">Akumulasi Poin</th><th class="p-3">Tindak Lanjut</th><th class="p-3">Pihak Terlibat</th></tr>
            </thead>
            <tbody>
                @foreach($aturanAkumulasi as $aturan)
                    <tr class="border-t border-slate-100">
                        <td class="p-3 font-semibold text-slate-800">{{ $aturan->poin_min }}{{ $aturan->poin_min === $aturan->poin_max ? '' : '–' . $aturan->poin_max }}</td>
                        <td class="p-3 text-slate-700">{{ $aturan->tindak_lanjut }}</td>
                        <td class="p-3 text-slate-700">{{ $aturan->pihak_terlibat }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <p class="mt-3 text-xs text-slate-600">Dokumen sumber tidak menetapkan tindak lanjut untuk akumulasi di atas 100; sistem tidak mengarang aturan untuk rentang tersebut.</p>
    </div>
</section>
@endsection
