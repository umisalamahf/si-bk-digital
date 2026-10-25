<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapor BK - {{ $siswa->nama }}</title>
    <style>
        :root { color-scheme: light; font-family: Arial, sans-serif; color: #1f2937; }
        body { margin: 0; background: #f8fafc; }
        main { max-width: 900px; margin: 2rem auto; padding: 0 1rem 2rem; }
        h1, h2, h3 { color: #111827; }
        h1 { margin-bottom: .25rem; font-size: 1.6rem; }
        h2 { margin-top: 0; font-size: 1.2rem; }
        h3 { margin-bottom: .35rem; font-size: 1rem; }
        p { line-height: 1.5; }
        .muted { color: #4b5563; }
        .panel { margin-top: 1rem; padding: 1rem; border: 1px solid #d1d5db; border-radius: .6rem; background: #fff; }
        .notice { border-left: 4px solid #9f1239; }
        .item + .item { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; }
        .meta { display: flex; flex-wrap: wrap; gap: .35rem 1.25rem; margin: .4rem 0; }
        .text { white-space: pre-wrap; overflow-wrap: anywhere; }
        dl { display: grid; grid-template-columns: minmax(8rem, 12rem) 1fr; gap: .4rem .8rem; }
        dt { font-weight: 700; }
        dd { margin: 0; white-space: pre-wrap; overflow-wrap: anywhere; }
        .actions { display: flex; gap: .75rem; margin-bottom: 1rem; }
        .actions a, .actions button { padding: .55rem .8rem; border: 1px solid #9ca3af; border-radius: .4rem; background: #fff; color: #111827; font: inherit; text-decoration: none; cursor: pointer; }
        @media print {
            body { background: #fff; font-size: 10pt; }
            main { max-width: none; margin: 0; padding: 0; }
            .actions { display: none; }
            .panel { break-inside: avoid; border-color: #9ca3af; box-shadow: none; }
            a { color: inherit; }
        }
    </style>
</head>
<body>
<main>
    <div class="actions">
        <a href="{{ route('laporan.siswa-bk.index') }}">Kembali ke daftar siswa</a>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    <header>
        <h1>Rapor Bimbingan dan Konseling</h1>
        <p class="muted">Dokumen internal dan rahasia. Untuk penggunaan admin dan Guru BK.</p>
    </header>

    <section class="panel" aria-labelledby="identitas-heading">
        <h2 id="identitas-heading">Identitas siswa</h2>
        <dl>
            <dt>Nama</dt><dd>{{ $siswa->nama }}</dd>
            <dt>NIS</dt><dd>{{ $siswa->nis }}</dd>
            <dt>Kelas</dt><dd>{{ $siswa->kelas->nama_kelas ?? '—' }}</dd>
            <dt>Status</dt><dd>{{ str_replace('_', ' ', ucfirst($siswa->status)) }}</dd>
        </dl>
    </section>

    <section class="panel notice" aria-labelledby="catatan-heading">
        <h2 id="catatan-heading">Catatan pembacaan dan batas data</h2>
        <p>Rapor ini hanya merangkum data yang tersimpan; ringkasan bukan diagnosis atau penilaian otomatis. Catatan peserta berasal dari relasi siswa–RPL. Kehadiran layanan individual tidak tersedia/tidak dilacak: tabel pelaksanaan hanya menyimpan jumlah hadir untuk satu sesi secara keseluruhan, bukan siapa yang hadir. Absensi harian sekolah tidak dianggap sebagai kehadiran layanan BK.</p>
        <p>Hasil asesmen di bawah memakai hasil tersimpan pada saat pengisian dan tanggal pengisian. Data sosiometri tidak disertakan di rapor individu untuk menjaga kerahasiaan pilihan antarsiswa. Tindak lanjut menampilkan rencana RPL, catatan pelaksanaan, dan evaluasi layanan yang tersimpan; bagian yang kosong berarti belum tercatat, bukan berarti tidak dilakukan.</p>
        @if(auth()->user()->isGuruBk())
            <p>Daftar layanan dibatasi pada RPL yang dibuat oleh Guru BK yang sedang login.</p>
        @endif
    </section>

    <section class="panel" aria-labelledby="layanan-heading">
        <h2 id="layanan-heading">Partisipasi dan pelaksanaan layanan</h2>
        @forelse($layanan as $rpl)
            <article class="item">
                <h3>{{ $rpl->topik_layanan ?: $rpl->topik }}</h3>
                <div class="meta">
                    <span>{{ \Illuminate\Support\Carbon::parse($rpl->tanggal)->translatedFormat('d F Y') }}</span>
                    <span>{{ $rpl->labelJenis() }}</span>
                    <span>{{ $rpl->kelas->nama_kelas ?? $rpl->kelas_semester ?? 'Layanan individual/kelompok' }}</span>
                </div>
                <p><strong>Status partisipasi:</strong> tercatat sebagai peserta pada RPL ini.</p>
                @if($rpl->pelaksanaan)
                    <p><strong>Kehadiran sesi (agregat):</strong> {{ $rpl->pelaksanaan->jumlah_hadir }} dari {{ $rpl->peserta_count }} peserta. Angka ini tidak mengidentifikasi kehadiran siswa ini.</p>
                    <h3>Pelaksanaan</h3>
                    <p class="text">{{ $rpl->pelaksanaan->hasil_pelaksanaan }}</p>
                    @if($rpl->pelaksanaan->hasil_layanan)
                        <h3>Hasil layanan yang dicatat</h3>
                        <p class="text">{{ $rpl->pelaksanaan->hasil_layanan }}</p>
                    @endif
                @else
                    <p class="muted">Pelaksanaan aktual belum direkap.</p>
                @endif

                @if($rpl->tindak_lanjut)
                    <h3>Tindak lanjut pada rencana RPL</h3>
                    <p class="text">{{ $rpl->tindak_lanjut }}</p>
                @endif
                @if($rpl->pelaksanaan && $rpl->pelaksanaan->tindak_lanjut)
                    <h3>Tindak lanjut pelaksanaan</h3>
                    <p class="text">{{ $rpl->pelaksanaan->tindak_lanjut }}</p>
                @endif
                @foreach($rpl->evaluasi as $evaluasi)
                    <h3>Evaluasi {{ strtoupper($evaluasi->jenis_evaluasi) }} · {{ \Illuminate\Support\Carbon::parse($evaluasi->tanggal)->translatedFormat('d F Y') }}</h3>
                    @if($evaluasi->hasil)<p class="text">{{ $evaluasi->hasil }}</p>@endif
                    @if($evaluasi->tindak_lanjut)<p><strong>Tindak lanjut evaluasi:</strong> <span class="text">{{ $evaluasi->tindak_lanjut }}</span></p>@endif
                    @if(!$evaluasi->hasil && !$evaluasi->tindak_lanjut)<p class="muted">Belum ada uraian hasil atau tindak lanjut.</p>@endif
                @endforeach
            </article>
        @empty
            <p class="muted">Belum ada partisipasi RPL yang tercatat untuk siswa ini.</p>
        @endforelse
    </section>

    <section class="panel" aria-labelledby="asesmen-heading">
        <h2 id="asesmen-heading">Hasil asesmen</h2>
        @forelse($asesmen as $jawaban)
            <article class="item">
                <h3>{{ $jawaban->instrumen->nama_instrumen ?? 'Instrumen asesmen' }}</h3>
                <p class="muted">
                    {{ $jawaban->instrumen->jenis->nama ?? 'Jenis tidak tersedia' }}
                    · {{ \Illuminate\Support\Carbon::parse($jawaban->tanggal_isi)->translatedFormat('d F Y') }}
                </p>
                @if($jawaban->hasil)
                    <dl>
                        @if($jawaban->hasil->skor_total !== null)<dt>Skor total</dt><dd>{{ $jawaban->hasil->skor_total }}</dd>@endif
                        @foreach(['kategori_dominan_1' => 'Kategori dominan 1', 'kategori_dominan_2' => 'Kategori dominan 2', 'kategori_dominan_3' => 'Kategori dominan 3'] as $field => $label)
                            @if($jawaban->hasil->{$field})<dt>{{ $label }}</dt><dd>{{ $jawaban->hasil->{$field} }}</dd>@endif
                        @endforeach
                        @if($jawaban->hasil->kesimpulan)<dt>Kesimpulan</dt><dd>{{ $jawaban->hasil->kesimpulan }}</dd>@endif
                        @if($jawaban->hasil->rekomendasi)<dt>Rekomendasi tersimpan</dt><dd>{{ $jawaban->hasil->rekomendasi }}</dd>@endif
                    </dl>
                    @if($jawaban->hasil->detail_skor)
                        <h3>Rincian skor tersimpan</h3>
                        <dl>
                            @foreach($jawaban->hasil->detail_skor as $label => $value)
                                <dt>{{ is_string($label) ? $label : 'Rincian '.($loop->iteration) }}</dt>
                                <dd>{{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</dd>
                            @endforeach
                        </dl>
                    @endif
                @else
                    <p class="muted">Jawaban tersimpan, tetapi hasil asesmen belum tersedia.</p>
                @endif
            </article>
        @empty
            <p class="muted">Belum ada hasil asesmen yang tercatat (sosiometri tidak ditampilkan di rapor individu).</p>
        @endforelse
    </section>

    <footer class="muted" style="margin-top: 1rem">
        Dicetak pada {{ now()->translatedFormat('d F Y H:i') }}.
    </footer>
</main>
</body>
</html>
