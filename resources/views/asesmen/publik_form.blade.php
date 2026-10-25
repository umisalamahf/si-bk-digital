<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Isi Asesmen - SI-BK Digital</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-linear-to-br from-rose-50 to-pink-50 min-h-screen px-4 py-8">
<div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-xl mx-auto">
    @php
        $jenisKode = $link->instrumen->jenis->kode;
        $konfigurasiSkor = $link->instrumen->konfigurasi_skor ?? [];
        $opsiJawaban = $konfigurasiSkor['response_options'] ?? null;
        $opsiLikert = ['SS', 'S', 'TS', 'STS'];
        $isLikertFourPoint = is_array($opsiJawaban)
            && count(array_intersect($opsiLikert, array_values($opsiJawaban))) === count($opsiLikert);
        $catatanInterpretasi = $konfigurasiSkor['disclaimer'] ?? null;
        $konfigurasiSosiometri = $konfigurasiSkor;
        $batasPilihan = isset($konfigurasiSosiometri['choices_per_question'])
            ? min((int) $konfigurasiSosiometri['choices_per_question'], $siswaPilihan->count())
            : null;
    @endphp
    <h1 class="text-lg font-bold text-rose-700 mb-1">{{ $link->instrumen->nama_instrumen }}</h1>
    @if($errors->any())
        <div class="my-3 p-3 bg-red-100 text-red-700 rounded text-sm">
            <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    @if($jenisKode === 'SOSIOMETRI')
        <p class="text-sm text-slate-500 mb-4">Halo, {{ $siswa->nama }}! Pilih siswa lain di kelas sesuai petunjuk pada setiap butir.</p>
    @elseif($jenisKode === 'DCM')
        <p class="text-sm text-slate-500 mb-4">Halo, {{ $siswa->nama }}! Tandai Ya jika masalah pada pernyataan dialami, atau Tidak jika tidak dialami.</p>
    @elseif(($konfigurasiSkor['scoring_mode'] ?? null) === 'profile')
        <p class="text-sm text-slate-600 mb-4">Halo, {{ $siswa->nama }}! Pilih satu jawaban untuk setiap pernyataan berdasarkan pengalaman atau preferensimu saat ini.</p>
    @elseif($opsiJawaban)
        <p class="text-sm text-slate-600 mb-4">Halo, {{ $siswa->nama }}! Pilih satu jawaban yang paling sesuai: {{ implode(', ', array_values($opsiJawaban)) }}.</p>
    @else
        <p class="text-sm text-slate-500 mb-4">Halo, {{ $siswa->nama }}! Jawab sesuai dirimu (1 = sangat tidak setuju, 5 = sangat setuju).</p>
    @endif
    @if($isLikertFourPoint)
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-950" aria-label="Keterangan pilihan jawaban">
            <p class="mb-2 font-semibold text-rose-800">Keterangan pilihan jawaban:</p>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-1 sm:grid-cols-4">
                <div><dt class="inline font-bold">SS</dt><dd class="inline"> = Sangat Setuju</dd></div>
                <div><dt class="inline font-bold">S</dt><dd class="inline"> = Setuju</dd></div>
                <div><dt class="inline font-bold">TS</dt><dd class="inline"> = Tidak Setuju</dd></div>
                <div><dt class="inline font-bold">STS</dt><dd class="inline"> = Sangat Tidak Setuju</dd></div>
            </dl>
        </div>
    @endif
    @if($catatanInterpretasi)
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs leading-relaxed text-amber-900">
            <strong>Catatan penggunaan:</strong> {{ $catatanInterpretasi }}
        </div>
    @endif

    <form method="POST" action="{{ route('asesmen.publik.simpan', $token) }}" class="space-y-4">
        @csrf
        @if($jenisKode === 'SOSIOMETRI')
            @foreach($butirPilihan as $b)
            <fieldset class="border-b pb-3">
                <legend class="text-sm font-medium mb-2">{{ $b->nomor }}. {{ $b->pertanyaan }}</legend>
                @if($batasPilihan)
                    <p class="mb-2 text-xs text-slate-500">Pilih tepat {{ $batasPilihan }} teman.</p>
                @endif
                @if($batasPilihan)
                    <div class="grid gap-3 sm:grid-cols-3">
                        @for($position = 0; $position < $batasPilihan; $position++)
                            <label class="block text-xs font-medium text-slate-600">
                                Pilihan {{ ['I', 'II', 'III'][$position] ?? ($position + 1) }}
                                <select name="pilihan[{{ $b->id }}][]" required class="mt-1 w-full rounded-lg border border-slate-300 px-2 py-2 text-sm text-slate-900">
                                    <option value="">Pilih teman</option>
                                    @foreach($siswaPilihan as $siswaLain)
                                        <option value="{{ $siswaLain->id }}" @selected((old('pilihan.' . $b->id)[$position] ?? '') == $siswaLain->id)>{{ $siswaLain->nama }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endfor
                    </div>
                @else
                    <div class="grid sm:grid-cols-2 gap-2">
                        @foreach($siswaPilihan as $siswaLain)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="pilihan[{{ $b->id }}][]" value="{{ $siswaLain->id }}"
                                    @checked(is_array(old('pilihan.' . $b->id)) && in_array((string) $siswaLain->id, old('pilihan.' . $b->id)))>
                                {{ $siswaLain->nama }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </fieldset>
            @endforeach
            @foreach($butirTerbuka as $b)
            <div class="border-b pb-3">
                <label for="respons-teks-{{ $b->nomor }}" class="block text-sm font-medium mb-2">{{ $b->nomor }}. {{ $b->pertanyaan }}</label>
                <textarea id="respons-teks-{{ $b->nomor }}" name="respons_teks[{{ $b->nomor }}]" rows="3" required
                    class="w-full border border-slate-300 rounded px-3 py-2 text-sm">{{ old('respons_teks.' . $b->nomor) }}</textarea>
            </div>
            @endforeach
        @else
        @foreach($link->instrumen->butir as $b)
        <div class="border-b pb-3">
            <p class="text-sm font-medium mb-2">{{ $b->nomor }}. {{ $b->pertanyaan }}</p>
            @if($jenisKode === 'DCM')
                <div class="flex gap-5">
                    <label class="flex items-center gap-1 text-sm"><input type="radio" name="jawaban[{{ $b->id }}]" value="1" required @checked(old('jawaban.' . $b->id) == '1')> Ya</label>
                    <label class="flex items-center gap-1 text-sm"><input type="radio" name="jawaban[{{ $b->id }}]" value="0" required @checked(old('jawaban.' . $b->id) === '0')> Tidak</label>
                </div>
            @elseif($opsiJawaban)
                <div class="flex flex-wrap gap-4">
                    @foreach($opsiJawaban as $nilai => $label)
                    <label class="flex items-center gap-1 text-sm">
                        <input type="radio" name="jawaban[{{ $b->id }}]" value="{{ $nilai }}" required @checked(old('jawaban.' . $b->id) == (string) $nilai)>
                        {{ $label }}
                    </label>
                    @endforeach
                </div>
            @else
                <div class="flex gap-3">
                    @for($n=1;$n<=5;$n++)
                    <label class="flex items-center gap-1 text-sm">
                        <input type="radio" name="jawaban[{{ $b->id }}]" value="{{ $n }}" required @checked(old('jawaban.' . $b->id) == (string) $n)> {{ $n }}
                    </label>
                    @endfor
                </div>
            @endif
        </div>
        @endforeach
        @foreach($responsTerbuka as $key => $label)
        <div>
            <label for="respons-teks-{{ $key }}" class="block text-sm font-medium mb-1">{{ $label }}</label>
            <textarea id="respons-teks-{{ $key }}" name="respons_teks[{{ $key }}]" rows="3" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">{{ old('respons_teks.' . $key) }}</textarea>
        </div>
        @endforeach
        @endif
        <button class="w-full bg-rose-600 hover:bg-rose-700 text-white py-2 rounded">Kirim Jawaban</button>
    </form>
</div>
<script>
    document.querySelectorAll('fieldset').forEach((fieldset) => {
        const selects = fieldset.querySelectorAll('select[name^="pilihan["]');
        selects.forEach((current) => {
            current.addEventListener('change', () => {
                const values = [...selects].map((select) => select.value).filter(Boolean);
                selects.forEach((select) => {
                    [...select.options].forEach((option) => {
                        option.disabled = option.value !== '' && values.includes(option.value) && select.value !== option.value;
                    });
                });
            });
        });
    });
</script>
</body></html>
