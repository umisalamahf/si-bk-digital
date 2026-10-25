<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>RPL {{ $rpl->topik }}</title>
    <style>
        @page { margin: 1.5cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; line-height: 1.4; color: #292429; }
        .kop { text-align: center; border-bottom: 2px solid #a94466; padding-bottom: 9px; margin-bottom: 15px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; color: #713047; }
        .note { margin: 0 0 8px; color: #713047; font-size: 8pt; }
        h1 { text-align: center; font-size: 14pt; margin: 0 0 3px; color: #713047; }
        .subjudul { text-align: center; margin: 0 0 15px; color: #5d555b; }
        h2 { font-size: 11pt; margin: 15px 0 6px; padding: 5px 7px; background: #fce9ef; color: #713047; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d8cbd0; padding: 6px; vertical-align: top; }
        th { text-align: left; background: #fff5f8; color: #40363c; }
        .identity th { width: 29%; }
        .content-table th { width: 29%; }
        .content { white-space: pre-line; }
        .signatures { margin-top: 28px; page-break-inside: avoid; }
        .signatures td { width: 50%; border: 0; text-align: center; }
        .sign-space { height: 55px; }
        .section, .participants, .evaluation { page-break-inside: avoid; }
    </style>
</head>
<body>
    @if(in_array($rpl->jenis_rpl, ['konseling_individual', 'konseling_kelompok', 'klasikal']))
        <p class="note">BK hadir, mendengar, membimbing, menginspirasi.</p>
    @endif
    <div class="kop">
        <strong>{{ $rpl->satuan_pendidikan ?? $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>
        {{ $sekolah->alamat ?? '' }}
    </div>
    <h1>
        @if($rpl->jenis_rpl === 'konseling_individual')
            RPL KONSELING INDIVIDU GURU BIMBINGAN DAN KONSELING
        @elseif($rpl->jenis_rpl === 'konseling_kelompok')
            RPL KONSELING KELOMPOK GURU BIMBINGAN DAN KONSELING
        @elseif($rpl->jenis_rpl === 'klasikal')
            RENCANA PELAKSANAAN LAYANAN BIMBINGAN KLASIKAL GURU BIMBINGAN DAN KONSELING
        @else
            Rencana Pelaksanaan Layanan
        @endif
    </h1>
    <p class="subjudul">
        @if($rpl->jenis_rpl === 'klasikal') Bimbingan Klasikal · @endif
        {{ $rpl->labelJenis() }}
    </p>

    @if($rpl->jenis_rpl === 'konseling_kelompok')
        <div class="section">
            <h2>Identitas Konseling Kelompok</h2>
            <table class="identity">
                <tbody>
                    <tr><th>Nama Konseli / Anggota Kelompok</th><td>{{ $rpl->peserta->pluck('nama')->join(', ') ?: '-' }}</td></tr>
                    <tr><th>Hari / Tanggal</th><td>{{ \Carbon\Carbon::parse($rpl->tanggal)->translatedFormat('l, d F Y') }}</td></tr>
                    <tr><th>Pertemuan Ke-</th><td>{{ $rpl->pertemuan_ke ?: '-' }}</td></tr>
                    <tr><th>Waktu</th><td>{{ $rpl->waktu ?: '-' }}</td></tr>
                    <tr><th>Tempat</th><td>{{ $rpl->tempat ?: '-' }}</td></tr>
                    <tr><th>Topik Permasalahan</th><td class="content">{{ $rpl->topik ?: '-' }}</td></tr>
                    <tr><th>Media yang digunakan</th><td class="content">{{ $rpl->media ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>
    @elseif($rpl->jenis_rpl === 'konseling_individual')
        <div class="section">
            <h2>Identitas Konseling Individu</h2>
            <table class="identity">
                <tbody>
                    <tr><th>Nama Konseli</th><td>{{ $rpl->peserta->pluck('nama')->join(', ') ?: '-' }}</td></tr>
                    <tr><th>Kelas / Semester</th><td>{{ $rpl->kelas_semester ?: '-' }}</td></tr>
                    <tr><th>Hari / Tanggal</th><td>{{ \Carbon\Carbon::parse($rpl->tanggal)->translatedFormat('l, d F Y') }}</td></tr>
                    <tr><th>Pertemuan Ke-</th><td>{{ $rpl->pertemuan_ke ?: '-' }}</td></tr>
                    <tr><th>Waktu</th><td>{{ $rpl->waktu ?: '-' }}</td></tr>
                    <tr><th>Tempat</th><td>{{ $rpl->tempat ?: '-' }}</td></tr>
                    <tr><th>Gejala yang Nampak / Keluhan</th><td class="content">{{ $rpl->masalah ?: '-' }}</td></tr>
                    <tr><th>Teknik Konseling</th><td class="content">{{ $rpl->teknik ?: '-' }}</td></tr>
                    <tr><th>Tindak Lanjut</th><td class="content">{{ $rpl->tindak_lanjut ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>
    @else
    <div class="section">
        <h2>1. IDENTITAS UMUM</h2>
        <table class="identity">
            <tbody>
                <tr><th>Satuan Pendidikan</th><td>{{ $rpl->satuan_pendidikan ?? $sekolah->nama_sekolah ?? '-' }}</td></tr>
                <tr><th>Kelas / Fase</th><td>{{ $rpl->kelas_fase ?? ($rpl->kelas->nama_kelas ?? '-') }}</td></tr>
                <tr><th>Tahun Ajaran</th><td>{{ $rpl->tahun_ajaran ?? '-' }}</td></tr>
                <tr><th>Jumlah Jam / Pertemuan</th><td>{{ $rpl->jumlah_jam_pertemuan ?? '-' }}</td></tr>
                <tr><th>Tanggal</th><td>{{ \Carbon\Carbon::parse($rpl->tanggal)->translatedFormat('d F Y') }}</td></tr>
                <tr><th>Topik</th><td>{{ $rpl->topik_layanan ?? $rpl->topik }}</td></tr>
            </tbody>
        </table>
    </div>

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
        ] as $sectionTitle => $fields)
            <div class="section">
                <h2>{{ $sectionTitle }}</h2>
                <table class="content-table">
                    <tbody>
                        @foreach($fields as $label => $value)
                            <tr><th>{{ $label }}</th><td class="content">{{ $value ?: '-' }}</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    @else
        <div class="section">
            <h2>Rincian Layanan</h2>
            <table class="content-table">
                <tbody>
                    <tr><th>Tujuan</th><td class="content">{{ $rpl->tujuan ?: '-' }}</td></tr>
                    <tr><th>Materi</th><td class="content">{{ $rpl->materi ?: '-' }}</td></tr>
                    <tr><th>Masalah / Keluhan</th><td class="content">{{ $rpl->masalah ?: '-' }}</td></tr>
                    <tr><th>Teknik</th><td class="content">{{ $rpl->teknik ?: '-' }}</td></tr>
                    <tr><th>Tindak Lanjut</th><td class="content">{{ $rpl->tindak_lanjut ?: '-' }}</td></tr>
                </tbody>
            </table>
        </div>
    @endif
    @endif

    @if(!in_array($rpl->jenis_rpl, ['konseling_individual', 'konseling_kelompok']))
        <div class="section participants">
            <h2>Peserta</h2>
            <table>
                <thead><tr><th style="width:8%">No.</th><th>Nama</th><th>Kelas</th></tr></thead>
                <tbody>
                    @forelse($rpl->peserta as $index => $peserta)
                        <tr><td>{{ $index + 1 }}</td><td>{{ $peserta->nama }}</td><td>{{ $peserta->kelas->nama_kelas ?? '-' }}</td></tr>
                    @empty
                        <tr><td colspan="3">Tidak ada peserta tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if($rpl->jenis_rpl !== 'konseling_kelompok')
        <div class="section evaluation">
            <h2>Evaluasi Layanan</h2>
            <table>
                <thead><tr><th>Jenis</th><th>Tanggal</th><th>Hasil</th><th>Tindak Lanjut</th></tr></thead>
                <tbody>
                    @forelse($rpl->evaluasi as $evaluasi)
                        <tr>
                            <td>{{ strtoupper($evaluasi->jenis_evaluasi) }}</td>
                            <td>{{ \Carbon\Carbon::parse($evaluasi->tanggal)->format('d-m-Y') }}</td>
                            <td>{{ $evaluasi->hasil ?: '-' }}</td>
                            <td>{{ $evaluasi->tindak_lanjut ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Belum ada evaluasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if(in_array($rpl->jenis_rpl, ['konseling_individual', 'konseling_kelompok']))
        <table class="signatures">
            <tr><td style="width:100%">Guru Bimbingan dan Konseling,</td></tr>
            <tr><td class="sign-space"></td></tr>
            <tr><td><u>{{ $rpl->nama_guru_bk ?? $guruBk->nama ?? '.................................' }}</u></td></tr>
            <tr><td>NIP. {{ $rpl->nip_guru_bk ?? $guruBk->nip ?? '-' }}</td></tr>
        </table>
    @else
    <table class="signatures">
        <tr>
            <td>Kepala Sekolah,</td>
            <td>Guru Bimbingan dan Konseling,</td>
        </tr>
        <tr><td class="sign-space"></td><td class="sign-space"></td></tr>
        <tr>
            <td><u>{{ $rpl->nama_kepala_sekolah ?? $sekolah->kepala_sekolah ?? '.................................' }}</u></td>
            <td><u>{{ $rpl->nama_guru_bk ?? $guruBk->nama ?? '.................................' }}</u></td>
        </tr>
        <tr>
            <td>NIP. {{ $rpl->nip_kepala_sekolah ?? $sekolah->kepala_sekolah_nip ?? '-' }}</td>
            <td>NIP. {{ $rpl->nip_guru_bk ?? $guruBk->nip ?? '-' }}</td>
        </tr>
    </table>
    @endif
</body>
</html>
