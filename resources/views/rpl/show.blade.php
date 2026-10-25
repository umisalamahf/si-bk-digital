@extends('layouts.app')
@section('title', 'Detail RPL')

@section('content')
@if(in_array($rpl->jenis_rpl, ['konseling_individual', 'konseling_kelompok', 'klasikal']))
    <p class="mb-2 text-sm font-medium tracking-wide text-brand-700">BK hadir, mendengar, membimbing, menginspirasi.</p>
    <h1 class="mb-1 text-2xl font-bold leading-tight tracking-wide text-slate-800">
        @if($rpl->jenis_rpl === 'konseling_kelompok')
            RPL KONSELING KELOMPOK GURU BIMBINGAN DAN KONSELING
        @elseif($rpl->jenis_rpl === 'konseling_individual')
            RPL KONSELING INDIVIDU GURU BIMBINGAN DAN KONSELING
        @else
            RENCANA PELAKSANAAN LAYANAN BIMBINGAN KLASIKAL GURU BIMBINGAN DAN KONSELING
        @endif
    </h1>
@else
    <h1 class="text-2xl font-bold text-slate-800 mb-1">{{ $rpl->topik }}</h1>
@endif
<p class="text-slate-500 mb-4">{{ $rpl->labelJenis() }} — {{ \Carbon\Carbon::parse($rpl->tanggal)->format('d-m-Y') }}</p>
<div class="mb-4 flex gap-2">
    <a href="{{ route('rpl.edit', $rpl) }}" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">Ubah RPL</a>
    <a href="{{ route('rpl.cetak', $rpl) }}" target="_blank" class="bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded text-sm">Cetak PDF</a>
    <a href="{{ route('rpl.index') }}" class="border border-slate-300 px-4 py-2 rounded text-sm">Kembali</a>
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if(session('survey_url'))
<div class="mb-4 rounded border border-rose-200 bg-white p-4 text-sm">
    <p class="font-semibold">Tautan angket publik (berlaku 90 hari)</p>
    <a class="break-all text-rose-700 underline" href="{{ session('survey_url') }}">{{ session('survey_url') }}</a>
</div>
@endif
<div class="mb-5 flex flex-wrap gap-2">
    <form method="POST" action="{{ route('rpl.kepuasan.link', $rpl) }}">
        @csrf
        <button class="rounded bg-rose-600 px-4 py-2 text-sm text-white">Buat Tautan Angket Kepuasan</button>
    </form>
    <a href="{{ route('rpl.kepuasan.hasil', $rpl) }}" class="rounded border border-slate-300 px-4 py-2 text-sm">Hasil Angket Kepuasan</a>
</div>

<div class="grid md:grid-cols-2 gap-6">
    <div class="bg-white rounded-xl shadow p-5 space-y-3">
        <h2 class="font-semibold text-slate-800">{{ $rpl->jenis_rpl === 'klasikal' ? '1. IDENTITAS UMUM' : 'Identitas Umum' }}</h2>
        @if(!in_array($rpl->jenis_rpl, ['konseling_individual', 'konseling_kelompok']))
            <div><p class="text-xs text-slate-500">Satuan Pendidikan</p><p>{{ $rpl->satuan_pendidikan ?? '-' }}</p></div>
            <div><p class="text-xs text-slate-500">Kelas / Fase</p><p>{{ $rpl->kelas_fase ?? ($rpl->kelas->nama_kelas ?? '-') }}</p></div>
            <div><p class="text-xs text-slate-500">Tahun Ajaran</p><p>{{ $rpl->tahun_ajaran ?? '-' }}</p></div>
            <div><p class="text-xs text-slate-500">Jumlah Jam / Pertemuan</p><p>{{ $rpl->jumlah_jam_pertemuan ?? '-' }}</p></div>
        @endif
        @if(!in_array($rpl->jenis_rpl, ['konseling_individual', 'konseling_kelompok']))
            <div><p class="text-xs text-slate-400">Peserta</p><p>{{ $rpl->peserta->pluck('nama')->join(', ') ?: '-' }}</p></div>
        @endif
        @if($rpl->jenis_rpl === 'konseling_kelompok')
            @foreach([
                'Nama Konseli / Anggota Kelompok' => $rpl->peserta->pluck('nama')->join(', '),
                'Hari / Tanggal' => \Carbon\Carbon::parse($rpl->tanggal)->translatedFormat('l, d F Y'),
                'Pertemuan Ke-' => $rpl->pertemuan_ke,
                'Waktu' => $rpl->waktu,
                'Tempat' => $rpl->tempat,
                'Topik Permasalahan' => $rpl->topik,
                'Media yang digunakan' => $rpl->media,
            ] as $label => $value)
                <div><p class="text-xs text-slate-500">{{ $label }}</p><p class="whitespace-pre-line">{{ $value ?: '-' }}</p></div>
            @endforeach
            <div class="border-t border-rose-100 pt-4 text-center">
                <p class="font-medium text-slate-700">Guru Bimbingan dan Konseling</p>
                <div class="h-16"></div>
                <p class="font-semibold text-slate-900">{{ $rpl->nama_guru_bk ?? '-' }}</p>
                <p>NIP. {{ $rpl->nip_guru_bk ?? '-' }}</p>
            </div>
        @endif
        @if($rpl->jenis_rpl === 'konseling_individual')
            @foreach([
                'Nama Konseli' => $rpl->peserta->pluck('nama')->join(', '),
                'Kelas / Semester' => $rpl->kelas_semester,
                'Hari / Tanggal' => \Carbon\Carbon::parse($rpl->tanggal)->translatedFormat('l, d F Y'),
                'Pertemuan Ke-' => $rpl->pertemuan_ke,
                'Waktu' => $rpl->waktu,
                'Tempat' => $rpl->tempat,
                'Gejala yang Nampak / Keluhan' => $rpl->masalah,
            ] as $label => $value)
                <div><p class="text-xs text-slate-500">{{ $label }}</p><p class="whitespace-pre-line">{{ $value ?: '-' }}</p></div>
            @endforeach
            <div class="border-t border-rose-100 pt-4 text-center">
                <p class="font-medium text-slate-700">Guru Bimbingan dan Konseling</p>
                <div class="h-16"></div>
                <p class="font-semibold text-slate-900">{{ $rpl->nama_guru_bk ?? '-' }}</p>
                <p>NIP. {{ $rpl->nip_guru_bk ?? '-' }}</p>
            </div>
        @endif
        @if($rpl->jenis_rpl === 'klasikal')
            @foreach([
                '2. IDENTIFIKASI' => [
                    'Kesiapan Peserta Didik' => $rpl->kesiapan_peserta_didik,
                    'Karakteristik Materi Pelajaran' => $rpl->karakteristik_materi,
                    'Dimensi Profil Lulusan' => $rpl->dimensi_profil_lulusan,
                ],
                '3. DESAIN PEMBELAJARAN' => [
                    'Capaian Layanan' => $rpl->capaian_layanan,
                    'Lintas Disiplin Ilmu' => $rpl->lintas_disiplin_ilmu,
                    'Tujuan Layanan BK' => $rpl->tujuan_layanan_bk,
                    'Topik Layanan' => $rpl->topik_layanan,
                    'Praktik Pedagogik' => $rpl->praktik_pedagogik,
                    'Kemitraan dan Lingkungan Pembelajaran' => $rpl->kemitraan_lingkungan_pembelajaran,
                    'Pemanfaatan Digital' => $rpl->pemanfaatan_digital,
                ],
                '4. PENGALAMAN BELAJAR' => [
                    'Kegiatan Awal / Pendahuluan' => $rpl->kegiatan_awal,
                    'Kegiatan Inti' => $rpl->kegiatan_inti,
                    'Kegiatan Penutup' => $rpl->kegiatan_penutup,
                ],
                '5. ASESMEN' => [
                    'Asesmen Awal' => $rpl->asesmen_awal,
                    'Asesmen Proses' => $rpl->asesmen_proses,
                    'Asesmen Akhir' => $rpl->asesmen_akhir,
                ],
            ] as $bagian => $fields)
                <div class="border-t border-rose-100 pt-3">
                    <h3 class="font-semibold text-slate-800">{{ $bagian }}</h3>
                    @foreach($fields as $label => $value)
                        <div class="mt-2">
                            <p class="text-xs text-slate-500">{{ $label }}</p>
                            <p class="whitespace-pre-line text-slate-800">{{ $value ?: '-' }}</p>
                        </div>
                    @endforeach
                </div>
            @endforeach
            <div class="border-t border-rose-100 pt-3">
                <h3 class="font-semibold text-slate-800">Pengesahan</h3>
                <p class="mt-2">Kepala Sekolah: {{ $rpl->nama_kepala_sekolah ?? '-' }} · NIP {{ $rpl->nip_kepala_sekolah ?? '-' }}</p>
                <p>Guru BK: {{ $rpl->nama_guru_bk ?? '-' }} · NIP {{ $rpl->nip_guru_bk ?? '-' }}</p>
            </div>
        @else
            @if($rpl->tujuan)<div><p class="text-xs text-slate-400">Tujuan</p><p>{{ $rpl->tujuan }}</p></div>@endif
            @if($rpl->materi)<div><p class="text-xs text-slate-400">Materi</p><p>{{ $rpl->materi }}</p></div>@endif
        @endif
        @if($rpl->jenis_rpl !== 'konseling_kelompok')
            @if($rpl->masalah)<div><p class="text-xs text-slate-400">Masalah</p><p>{{ $rpl->masalah }}</p></div>@endif
            @if($rpl->teknik)<div><p class="text-xs text-slate-400">Teknik</p><p>{{ $rpl->teknik }}</p></div>@endif
            @if($rpl->tindak_lanjut)<div><p class="text-xs text-slate-400">Tindak Lanjut</p><p>{{ $rpl->tindak_lanjut }}</p></div>@endif
        @endif
    </div>

    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="font-semibold mb-3">Evaluasi Layanan</h2>
        @forelse($rpl->evaluasi as $e)
        <div class="border-b py-2 text-sm">
            <span class="font-medium">{{ strtoupper($e->jenis_evaluasi) }}</span> — {{ \Carbon\Carbon::parse($e->tanggal)->format('d-m-Y') }}
            <p class="text-slate-500">{{ $e->hasil }}</p>
        </div>
        @empty
        <p class="text-slate-400 text-sm mb-3">Belum ada evaluasi.</p>
        @endforelse

        <form method="POST" action="{{ route('rpl.evaluasi.store', $rpl) }}" class="mt-4 space-y-2 border-t pt-4">
            @csrf
            <select name="jenis_evaluasi" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
                <option value="laiseg">Laiseg (evaluasi segera)</option>
                <option value="laijapen">Laijapen (jangka pendek)</option>
                <option value="laijapan">Laijapan (jangka panjang)</option>
            </select>
            <input type="date" name="tanggal" value="{{ now()->format('Y-m-d') }}" required class="w-full border border-slate-300 rounded px-2 py-1 text-sm">
            <textarea name="hasil" placeholder="Hasil evaluasi" rows="2" class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
            <textarea name="tindak_lanjut" placeholder="Tindak lanjut" rows="2" class="w-full border border-slate-300 rounded px-2 py-1 text-sm"></textarea>
            <button class="bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 rounded text-sm">+ Tambah Evaluasi</button>
        </form>
    </div>
</div>
@endsection
