<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Hasil Asesmen</title>
    <style>
        @page { margin: 1.4cm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #1f2937; }
        .kop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 9px; margin-bottom: 15px; }
        .kop strong { display: block; font-size: 15pt; text-transform: uppercase; }
        h1, .tanggal { text-align: center; margin: 0 0 10px; }
        h1 { font-size: 14pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #9ca3af; padding: 5px; vertical-align: top; }
        th { background: #e5e7eb; text-align: left; }
    </style>
</head>
<body>
    <div class="kop"><strong>{{ $sekolah->nama_sekolah ?? 'Nama Sekolah' }}</strong>{{ $sekolah->alamat ?? '' }}</div>
    <h1>Rekap Hasil Asesmen BK</h1>
    <p class="tanggal">Dicetak {{ $tanggalCetak }}</p>
    <table>
        <thead><tr><th>No.</th><th>Siswa</th><th>Kelas</th><th>Instrumen</th><th>Tanggal Isi</th><th>Skor</th><th>Kategori Dominan</th><th>Kesimpulan</th><th>Rekomendasi</th></tr></thead>
        <tbody>
            @forelse($jawaban as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->siswa->nama ?? '-' }}</td>
                    <td>{{ $item->siswa->kelas->nama_kelas ?? '-' }}</td>
                    <td>{{ $item->instrumen->nama_instrumen ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->tanggal_isi)->format('d-m-Y') }}</td>
                    <td>
                        @if(($item->hasil->detail_skor['mode'] ?? null) === 'profile')
                            Skor per aspek
                        @else
                            {{ $item->hasil->skor_total ?? '-' }}
                        @endif
                        @if($item->hasil && !empty($item->hasil->detail_skor['aspek']))
                            <br>
                            @foreach($item->hasil->detail_skor['aspek'] as $aspek => $skor)
                                {{ $aspek }}: {{ $skor }}
                                @if(($item->hasil->detail_skor['mode'] ?? null) === 'profile')
                                    / 16 ({{ $item->hasil->detail_skor['interpretasi_aspek'][$aspek] ?? '' }})
                                @endif
                                <br>
                            @endforeach
                            @if(($item->hasil->detail_skor['mode'] ?? null) === 'profile')
                                Area yang paling terasa sesuai: {{ implode(', ', $item->hasil->detail_skor['aspek_disorot'] ?? []) }}
                            @else
                                Aspek prioritas: {{ implode(', ', $item->hasil->detail_skor['aspek_prioritas'] ?? []) }}
                            @endif
                        @endif
                    </td>
                    <td>{{ $item->hasil->kategori_dominan_1 ?? '-' }}</td>
                    <td>
                        {{ $item->hasil->kesimpulan ?? 'Belum dihitung' }}
                        @if(!empty($item->hasil->detail_skor['disclaimer']))
                            <br><small>{{ $item->hasil->detail_skor['disclaimer'] }}</small>
                        @endif
                    </td>
                    <td>{{ $item->hasil->rekomendasi ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Belum ada hasil asesmen.</td></tr>
            @endforelse
        </tbody>
    </table>
    <h2 style="font-size: 12pt; margin-top: 20px;">Rekap Pilihan Sosiometri</h2>
    <p>Jumlah pemilihan merupakan data mentah; interpretasi mengikuti pedoman Guru BK.</p>
    <table>
        <thead><tr><th>Sesi</th><th>Kelas</th><th>Instrumen</th><th>Jenis Pilihan</th><th>Butir</th><th>Siswa Terpilih</th><th>Pilihan I / II / III</th><th>Jumlah Dipilih</th></tr></thead>
        <tbody>
            @forelse($rekapSosiometri as $baris)
                <tr>
                    <td>#{{ $baris->link_id }}</td>
                    <td>{{ $baris->nama_kelas }}</td>
                    <td>{{ $baris->nama_instrumen }}</td>
                    <td>{{ $baris->kategori }}</td>
                    <td>{{ $baris->pertanyaan }}</td>
                    <td>{{ $baris->siswa_dipilih }}</td>
                    <td>{{ $baris->pilihan_pertama }} / {{ $baris->pilihan_kedua }} / {{ $baris->pilihan_ketiga }}</td>
                    <td>{{ $baris->jumlah_dipilih }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Belum ada pilihan sosiometri.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
