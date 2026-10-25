@extends('layouts.app')
@section('title', 'Akreditasi & Supervisi BK')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-1">Dokumen Bukti & Supervisi BK</h1>
<p class="text-slate-600 mb-5">Ruang kerja internal untuk mengarsipkan dokumen pendukung dan mencatat temuan serta tindak lanjut BK. Status di halaman ini bukan penilaian atau jaminan pemenuhan standar akreditasi.</p>

@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<form method="GET" action="{{ route('bk-akreditasi.index') }}" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow">
    <div>
        <label for="filter-periode" class="mb-1 block text-sm font-medium">Filter periode</label>
        <input id="filter-periode" name="periode" value="{{ $periode }}" maxlength="30" placeholder="Contoh: 2026/2027" class="border border-slate-300 px-3 py-2 text-sm">
    </div>
    <button class="rounded bg-slate-700 px-4 py-2 text-sm text-white">Terapkan</button>
    @if($periode)<a href="{{ route('bk-akreditasi.index') }}" class="text-sm text-rose-700 hover:underline">Hapus filter</a>@endif
</form>

<section class="mb-8">
    <h2 class="mb-3 text-xl font-semibold text-slate-800">Dokumen bukti BK</h2>
    <form method="POST" action="{{ route('bk-akreditasi.document.store') }}" enctype="multipart/form-data" class="mb-5 max-w-4xl space-y-3 rounded-xl bg-white p-5 shadow">
        @csrf
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label for="doc-judul" class="mb-1 block text-sm font-medium">Judul dokumen</label>
                <input id="doc-judul" name="judul" value="{{ old('judul') }}" maxlength="150" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="doc-periode" class="mb-1 block text-sm font-medium">Periode</label>
                <input id="doc-periode" name="periode" value="{{ old('periode') }}" maxlength="30" placeholder="Contoh: 2026/2027" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="doc-kategori" class="mb-1 block text-sm font-medium">Kategori</label>
                <select id="doc-kategori" name="kategori" required class="w-full border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Pilih kategori</option>
                    @foreach(['program' => 'Program', 'pelaksanaan' => 'Pelaksanaan', 'evaluasi' => 'Evaluasi', 'administrasi' => 'Administrasi', 'pengembangan_profesi' => 'Pengembangan profesi', 'lainnya' => 'Lainnya'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('kategori') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="doc-file" class="mb-1 block text-sm font-medium">File (maks. 10 MB)</label>
                <input id="doc-file" type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <div>
            <label for="doc-keterangan" class="mb-1 block text-sm font-medium">Keterangan (opsional)</label>
            <textarea id="doc-keterangan" name="keterangan" maxlength="5000" rows="2" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old('keterangan') }}</textarea>
        </div>
        <button class="rounded bg-rose-600 px-5 py-2 text-sm text-white hover:bg-rose-700">Simpan dokumen</button>
        <p class="text-xs text-slate-500">File disimpan privat pada disk lokal; hanya PDF, dokumen Office, dan gambar JPG/PNG yang diizinkan.</p>
    </form>

    <div class="overflow-x-auto rounded-xl bg-white shadow">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-100"><tr><th class="p-3">Dokumen</th><th class="p-3">Periode / kategori</th><th class="p-3">Status internal</th><th class="p-3">Pengunggah</th><th class="p-3">Aksi</th></tr></thead>
            <tbody>
                @forelse($documents as $document)
                    <tr class="border-t align-top">
                        <td class="p-3">{{ $document->judul }}<p class="mt-1 text-xs text-slate-500">{{ $document->nama_file }} · {{ number_format($document->ukuran_file / 1024, 1, ',', '.') }} KB</p>@if($document->keterangan)<p class="mt-1 text-xs text-slate-600">{{ $document->keterangan }}</p>@endif</td>
                        <td class="p-3">{{ $document->periode }}<p class="mt-1 text-xs text-slate-500">{{ str_replace('_', ' ', ucfirst($document->kategori)) }}</p></td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('bk-akreditasi.document.status', $document) }}" class="flex flex-wrap gap-2">
                                @csrf @method('PATCH')
                                <select name="status" aria-label="Status internal dokumen" class="border border-slate-300 px-2 py-1 text-xs">
                                    @foreach(['draft' => 'Draf', 'in_review' => 'Ditinjau', 'ready' => 'Siap internal'] as $value => $label)
                                        <option value="{{ $value }}" @selected($document->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="text-rose-700 hover:underline">Simpan</button>
                            </form>
                        </td>
                        <td class="p-3">{{ $document->uploader?->name ?? '-' }}<p class="mt-1 text-xs text-slate-500">{{ $document->created_at?->format('d-m-Y') ?? '-' }}</p></td>
                        <td class="p-3"><a href="{{ route('bk-akreditasi.document.download', $document) }}" class="font-medium text-rose-700 hover:underline">Unduh</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada dokumen bukti untuk periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $documents->links() }}</div>
</section>

<section>
    <h2 class="mb-3 text-xl font-semibold text-slate-800">Supervisi & audit kinerja BK</h2>
    <form method="POST" action="{{ route('bk-supervisi.store') }}" class="mb-5 max-w-4xl space-y-3 rounded-xl bg-white p-5 shadow">
        @csrf
        <div class="grid gap-3 md:grid-cols-2">
            <div>
                <label for="record-jenis" class="mb-1 block text-sm font-medium">Jenis catatan</label>
                <select id="record-jenis" name="jenis" required class="w-full border border-slate-300 px-3 py-2 text-sm">
                    <option value="supervision" @selected(old('jenis') === 'supervision')>Supervisi</option>
                    <option value="performance_audit" @selected(old('jenis') === 'performance_audit')>Audit kinerja internal</option>
                </select>
            </div>
            <div>
                <label for="record-periode" class="mb-1 block text-sm font-medium">Periode</label>
                <input id="record-periode" name="periode" value="{{ old('periode') }}" maxlength="30" placeholder="Contoh: 2026/2027" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="record-tanggal" class="mb-1 block text-sm font-medium">Tanggal</label>
                <input id="record-tanggal" type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="record-judul" class="mb-1 block text-sm font-medium">Judul / fokus</label>
                <input id="record-judul" name="judul" value="{{ old('judul') }}" maxlength="150" required class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div class="md:col-span-2">
                <label for="record-temuan" class="mb-1 block text-sm font-medium">Temuan</label>
                <textarea id="record-temuan" name="temuan" maxlength="10000" rows="3" required class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old('temuan') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <label for="record-follow-up" class="mb-1 block text-sm font-medium">Tindak lanjut awal (opsional)</label>
                <textarea id="record-follow-up" name="tindak_lanjut" maxlength="10000" rows="2" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old('tindak_lanjut') }}</textarea>
            </div>
            <div>
                <label for="record-due" class="mb-1 block text-sm font-medium">Batas tindak lanjut (opsional)</label>
                <input id="record-due" type="date" name="batas_tindak_lanjut" value="{{ old('batas_tindak_lanjut') }}" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
        <button class="rounded bg-rose-600 px-5 py-2 text-sm text-white hover:bg-rose-700">Catat supervisi/audit</button>
    </form>

    <div class="space-y-4">
        @forelse($records as $record)
            <article class="rounded-xl bg-white p-5 shadow">
                <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">{{ $record->jenis === 'supervision' ? 'Supervisi' : 'Audit kinerja internal' }} · {{ $record->periode }} · {{ $record->tanggal->format('d-m-Y') }}</p>
                        <h3 class="mt-1 text-lg font-semibold text-slate-800">{{ $record->judul }}</h3>
                        <p class="text-xs text-slate-500">Dicatat oleh {{ $record->creator?->name ?? '-' }} · {{ $record->created_at?->format('d-m-Y') ?? '-' }}</p>
                    </div>
                </div>
                <p class="mb-1 text-sm font-semibold">Temuan</p>
                <p class="mb-3 whitespace-pre-line text-sm text-slate-700">{{ $record->temuan }}</p>
                <form method="POST" action="{{ route('bk-supervisi.update', $record) }}" class="grid gap-3 md:grid-cols-2">
                    @csrf @method('PATCH')
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-medium">Tindak lanjut</label>
                        <textarea name="tindak_lanjut" maxlength="10000" rows="2" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ $record->tindak_lanjut }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Perbarui temuan</label>
                        <textarea name="temuan" maxlength="10000" rows="2" required class="w-full border border-slate-300 px-3 py-2 text-sm">{{ $record->temuan }}</textarea>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium">Status tindak lanjut</label>
                            <select name="status" required class="w-full border border-slate-300 px-3 py-2 text-sm">
                                @foreach(['planned' => 'Direncanakan', 'in_progress' => 'Berjalan', 'follow_up' => 'Perlu tindak lanjut', 'closed' => 'Ditutup internal'] as $value => $label)
                                    <option value="{{ $value }}" @selected($record->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium">Batas tindak lanjut (opsional)</label>
                            <input type="date" name="batas_tindak_lanjut" value="{{ $record->batas_tindak_lanjut?->toDateString() }}" class="w-full border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="md:col-span-2"><button class="rounded bg-slate-700 px-4 py-2 text-sm text-white">Simpan tindak lanjut</button></div>
                </form>
            </article>
        @empty
            <div class="rounded-xl bg-white p-6 text-center text-slate-500 shadow">Belum ada catatan supervisi atau audit untuk periode ini.</div>
        @endforelse
    </div>
    <div class="mt-3">{{ $records->links() }}</div>
</section>
@endsection
