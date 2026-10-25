@extends('layouts.app')
@section('title', 'Buat RPL')

@section('content')
@php($isEdit = isset($rpl))
<div x-data="{ jenis: @js(old('jenis_rpl', $rpl->jenis_rpl ?? 'klasikal')), tahunAjaran: @js($rpl->tahun_ajaran ?? 'Otomatis mengikuti kelas yang dipilih') }">
<div x-show="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'">
    <p class="mb-2 text-sm font-medium tracking-wide text-brand-700">BK hadir, mendengar, membimbing, menginspirasi.</p>
    <h1 class="mb-4 text-2xl font-bold leading-tight tracking-wide text-slate-800 sm:text-3xl"
        x-text="jenis === 'konseling_kelompok' ? 'RPL KONSELING KELOMPOK GURU BIMBINGAN DAN KONSELING' : 'RPL KONSELING INDIVIDU GURU BIMBINGAN DAN KONSELING'"></h1>
</div>
<div x-show="jenis === 'klasikal'">
    <p class="mb-2 text-sm font-medium tracking-wide text-brand-700">BK hadir, mendengar, membimbing, menginspirasi.</p>
    <h1 class="mb-4 text-2xl font-bold leading-tight tracking-wide text-slate-800 sm:text-3xl">RENCANA PELAKSANAAN LAYANAN BIMBINGAN KLASIKAL GURU BIMBINGAN DAN KONSELING</h1>
</div>
<h1 x-show="jenis !== 'konseling_individual' && jenis !== 'konseling_kelompok' && jenis !== 'klasikal'" class="mb-4 text-2xl font-bold text-slate-800">{{ $isEdit ? 'Ubah RPL' : 'Buat RPL Baru' }}</h1>
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('rpl.update', $rpl) : route('rpl.store') }}" class="max-w-4xl space-y-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-rose-100">
    @csrf
    @if($isEdit) @method('PUT') @endif
    <div>
        <label class="block text-sm font-medium mb-1">Jenis RPL *</label>
        <select name="jenis_rpl" x-model="jenis" required class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="klasikal" @selected(old('jenis_rpl', $rpl->jenis_rpl ?? 'klasikal') === 'klasikal')>Klasikal (1 kelas penuh)</option>
            <option value="kelompok" @selected(old('jenis_rpl', $rpl->jenis_rpl ?? '') === 'kelompok')>Kelompok (beberapa siswa)</option>
            <option value="individual" @selected(old('jenis_rpl', $rpl->jenis_rpl ?? '') === 'individual')>Individual (1 siswa)</option>
            <option value="konseling_individual" @selected(old('jenis_rpl', $rpl->jenis_rpl ?? '') === 'konseling_individual')>Konseling Individual (1 siswa, kasus)</option>
            <option value="konseling_kelompok" @selected(old('jenis_rpl', $rpl->jenis_rpl ?? '') === 'konseling_kelompok')>Konseling Kelompok (beberapa siswa, kasus bersama)</option>
        </select>
    </div>

    <div x-show="jenis !== 'klasikal'">
        <label class="block text-sm font-medium mb-1" x-text="jenis === 'konseling_kelompok' ? 'Topik Permasalahan *' : 'Topik *'"></label>
        <input type="text" name="topik" value="{{ old('topik', $rpl->topik ?? '') }}" :required="jenis !== 'klasikal'" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1" x-text="jenis === 'konseling_individual' || jenis === 'konseling_kelompok' ? 'Hari / Tanggal *' : 'Tanggal *'"></label>
        <input type="date" name="tanggal" value="{{ old('tanggal', isset($rpl) ? \Carbon\Carbon::parse($rpl->tanggal)->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div x-show="jenis === 'klasikal'">
        <label class="block text-sm font-medium mb-1">Kelas * <span class="text-xs text-slate-400">(semua siswa aktif kelas ini otomatis jadi peserta)</span></label>
        <select name="kelas_id" :required="jenis === 'klasikal'" @change="tahunAjaran = $event.target.selectedOptions[0].dataset.tahun || 'Pilih kelas untuk melihat tahun ajaran'" class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih Kelas --</option>
            @foreach($kelas as $k)<option value="{{ $k->id }}" data-tahun="{{ $k->tahun_ajaran }}" @selected(old('kelas_id', $rpl->kelas_id ?? '')==$k->id)>{{ $k->nama_kelas }}</option>@endforeach
        </select>
    </div>

    <div x-show="jenis !== 'klasikal'">
        <label class="block text-sm font-medium mb-1" x-text="jenis === 'konseling_individual' ? 'Nama Konseli * (pilih tepat satu siswa)' : (jenis === 'konseling_kelompok' ? 'Nama Konseli / Anggota Kelompok *' : 'Pilih Siswa *')"></label>
        <p x-show="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'" class="mb-1 text-xs text-slate-600">Nama konseli diambil dari biodata siswa yang dipilih.</p>
        <p x-show="jenis !== 'individual' && jenis !== 'konseling_individual' && jenis !== 'konseling_kelompok'" class="mb-1 text-xs text-slate-500">Pilih beberapa siswa dengan menahan Ctrl.</p>
        <select name="siswa_ids[]" multiple size="6" :required="jenis !== 'klasikal'" class="w-full border border-slate-300 rounded px-3 py-2">
            @foreach($siswa as $s)
            <option value="{{ $s->id }}" @selected(in_array($s->id, old('siswa_ids', isset($rpl) ? $rpl->peserta->pluck('id')->all() : [])))>{{ $s->nama }} ({{ $s->kelas->nama_kelas ?? '-' }})</option>
            @endforeach
        </select>
    </div>

    <section x-show="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'" class="space-y-4 rounded-2xl border border-rose-100 bg-rose-50/50 p-5">
        <h2 class="text-lg font-bold text-slate-800">Identitas Pertemuan Konseling</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <div x-show="jenis === 'konseling_individual'">
                <label for="kelas_semester" class="mb-1 block text-sm font-medium text-slate-700">Kelas / Semester *</label>
                <input id="kelas_semester" name="kelas_semester" value="{{ old('kelas_semester', $rpl->kelas_semester ?? '') }}" :required="jenis === 'konseling_individual'" maxlength="100" placeholder="Contoh: VII A / Semester Ganjil" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="pertemuan_ke" class="mb-1 block text-sm font-medium text-slate-700">Pertemuan Ke- *</label>
                <input id="pertemuan_ke" type="number" name="pertemuan_ke" value="{{ old('pertemuan_ke', $rpl->pertemuan_ke ?? '') }}" :required="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'" min="1" max="65535" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="waktu" class="mb-1 block text-sm font-medium text-slate-700">Waktu *</label>
                <input id="waktu" name="waktu" value="{{ old('waktu', $rpl->waktu ?? '') }}" :required="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'" maxlength="50" placeholder="Contoh: 09.00–09.45" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="tempat" class="mb-1 block text-sm font-medium text-slate-700">Tempat *</label>
                <input id="tempat" name="tempat" value="{{ old('tempat', $rpl->tempat ?? '') }}" :required="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'" maxlength="200" placeholder="Contoh: Ruang BK" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
    </section>

    <section x-show="jenis === 'klasikal'" class="space-y-5 border-t border-rose-100 pt-5">
        <div>
            <h2 class="text-lg font-bold text-slate-800">1. IDENTITAS UMUM</h2>
            <p class="mt-1 text-sm text-slate-600">Satuan pendidikan dan tahun ajaran diambil otomatis dari data sekolah dan kelas.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-800">Satuan Pendidikan</label>
                <input value="{{ $sekolah->nama_sekolah ?? '' }}" readonly class="w-full border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-800">Tahun Ajaran</label>
                <input :value="tahunAjaran" readonly class="w-full border border-slate-300 bg-slate-50 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="kelas_fase" class="mb-1 block text-sm font-semibold text-slate-800">Kelas / Fase *</label>
                <input id="kelas_fase" name="kelas_fase" value="{{ old('kelas_fase', $rpl->kelas_fase ?? '') }}" :required="jenis === 'klasikal'" maxlength="100" placeholder="Contoh: VII A / Fase D" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label for="jumlah_jam_pertemuan" class="mb-1 block text-sm font-semibold text-slate-800">Jumlah Jam / Pertemuan *</label>
                <input id="jumlah_jam_pertemuan" name="jumlah_jam_pertemuan" value="{{ old('jumlah_jam_pertemuan', $rpl->jumlah_jam_pertemuan ?? '') }}" :required="jenis === 'klasikal'" maxlength="100" placeholder="Contoh: 2 JP / 1 pertemuan" class="w-full border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>

        <div class="border-t border-rose-100 pt-4">
            <h2 class="text-lg font-bold text-slate-800">2. IDENTIFIKASI</h2>
            <div class="mt-3 grid gap-4">
                @foreach([
                    ['kesiapan_peserta_didik', 'Kesiapan Peserta Didik', 3],
                    ['karakteristik_materi', 'Karakteristik Materi Pelajaran', 3],
                    ['dimensi_profil_lulusan', 'Dimensi Profil Lulusan', 3],
                ] as [$name, $label, $rows])
                    <div>
                        <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-slate-800">{{ $label }} *</label>
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" :required="jenis === 'klasikal'" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old($name, $rpl->$name ?? '') }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-rose-100 pt-4">
            <h2 class="text-lg font-bold text-slate-800">3. DESAIN PEMBELAJARAN</h2>
            <div class="mt-3 grid gap-4">
                @foreach([
                    ['capaian_layanan', 'Capaian Layanan', true],
                    ['lintas_disiplin_ilmu', 'Lintas Disiplin Ilmu', false],
                    ['tujuan_layanan_bk', 'Tujuan Layanan BK', true],
                    ['topik_layanan', 'Topik Layanan', true],
                    ['praktik_pedagogik', 'Praktik Pedagogik', false],
                    ['kemitraan_lingkungan_pembelajaran', 'Kemitraan dan Lingkungan Pembelajaran', false],
                    ['pemanfaatan_digital', 'Pemanfaatan Digital', false],
                ] as [$name, $label, $required])
                    <div>
                        <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-slate-800">{{ $label }}{{ $required ? ' *' : '' }}</label>
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" @if($required) :required="jenis === 'klasikal'" @endif class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old($name, $rpl->$name ?? ($name === 'topik_layanan' ? ($rpl->topik ?? '') : '')) }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-rose-100 pt-4">
            <h2 class="text-lg font-bold text-slate-800">4. PENGALAMAN BELAJAR</h2>
            <div class="mt-3 grid gap-4">
                @foreach([
                    ['kegiatan_awal', 'Kegiatan Awal / Pendahuluan', 4],
                    ['kegiatan_inti', 'Kegiatan Inti', 7],
                    ['kegiatan_penutup', 'Kegiatan Penutup', 4],
                ] as [$name, $label, $rows])
                    <div>
                        <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-slate-800">{{ $label }} *</label>
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" :required="jenis === 'klasikal'" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old($name, $rpl->$name ?? '') }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-rose-100 pt-4">
            <h2 class="text-lg font-bold text-slate-800">5. ASESMEN</h2>
            <div class="mt-3 grid gap-4">
                @foreach([
                    ['asesmen_awal', 'Asesmen Awal'],
                    ['asesmen_proses', 'Asesmen Proses'],
                    ['asesmen_akhir', 'Asesmen Akhir'],
                ] as [$name, $label])
                    <div>
                        <label for="{{ $name }}" class="mb-1 block text-sm font-semibold text-slate-800">{{ $label }} *</label>
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" :required="jenis === 'klasikal'" class="w-full border border-slate-300 px-3 py-2 text-sm">{{ old($name, $rpl->$name ?? '') }}</textarea>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-xl bg-rose-50 p-4 text-sm text-slate-800">
            <h2 class="font-bold">Pengesahan / Tanda Tangan</h2>
            <p class="mt-2">Kepala Sekolah: {{ $rpl->nama_kepala_sekolah ?? $sekolah->kepala_sekolah ?? 'Belum diisi pada Identitas Sekolah' }} (NIP {{ $rpl->nip_kepala_sekolah ?? $sekolah->kepala_sekolah_nip ?? '—' }})</p>
            <p class="mt-1">Guru BK: {{ $rpl->nama_guru_bk ?? $guruBk->nama ?? '—' }} (NIP {{ $rpl->nip_guru_bk ?? $guruBk->nip ?? '—' }})</p>
            <p class="mt-2 text-xs text-slate-600">Nama dan NIP diambil otomatis dari Identitas Sekolah dan profil Guru BK.</p>
        </div>
    </section>

    <div x-show="jenis !== 'klasikal'">
        <label class="block text-sm font-medium mb-1">Tujuan</label>
        <textarea name="tujuan" rows="2" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('tujuan', $rpl->tujuan ?? '') }}</textarea>
    </div>
    <div x-show="jenis !== 'konseling_individual' && jenis !== 'konseling_kelompok'">
        <label class="block text-sm font-medium mb-1">Materi</label>
        <textarea name="materi" rows="2" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('materi', $rpl->materi ?? '') }}</textarea>
    </div>

    <div x-show="jenis === 'konseling_individual'">
        <label class="block text-sm font-medium mb-1">Gejala yang Nampak / Keluhan *</label>
        <textarea name="masalah" rows="3" :required="jenis === 'konseling_individual'" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('masalah', $rpl->masalah ?? '') }}</textarea>
    </div>
    <div x-show="jenis === 'konseling_individual'">
        <label class="block text-sm font-medium mb-1">Teknik Konseling *</label>
        <input type="text" name="teknik" value="{{ old('teknik', $rpl->teknik ?? '') }}" class="w-full border border-slate-300 rounded px-3 py-2">
    </div>

    <div x-show="jenis === 'konseling_kelompok'">
        <label for="media" class="mb-1 block text-sm font-medium text-slate-700">Media yang digunakan *</label>
        <textarea id="media" name="media" rows="3" :required="jenis === 'konseling_kelompok'" maxlength="5000" placeholder="Tuliskan media yang digunakan dalam layanan" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('media', $rpl->media ?? '') }}</textarea>
    </div>

    <div x-show="jenis !== 'klasikal'">
        <label class="block text-sm font-medium mb-1">Tindak Lanjut</label>
        <textarea name="tindak_lanjut" rows="2" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('tindak_lanjut', $rpl->tindak_lanjut ?? '') }}</textarea>
    </div>

    <div x-show="jenis === 'konseling_individual' || jenis === 'konseling_kelompok'" class="rounded-2xl border border-rose-100 bg-white p-5 text-center text-sm text-slate-700">
        <p class="font-semibold">Guru Bimbingan dan Konseling</p>
        <div class="h-16"></div>
        <p class="font-semibold text-slate-900">{{ $rpl->nama_guru_bk ?? $guruBk->nama ?? 'Nama Guru BK belum tersedia' }}</p>
        <p>NIP. {{ $rpl->nip_guru_bk ?? $guruBk->nip ?? '-' }}</p>
        <p class="mt-2 text-xs text-slate-500">Nama dan NIP diambil otomatis dari profil Guru BK.</p>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
        <a href="{{ route('rpl.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endsection
