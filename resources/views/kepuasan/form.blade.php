<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Angket Kepuasan Layanan BK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-rose-50 px-4 py-8 text-slate-800">
<main class="mx-auto max-w-3xl rounded-2xl bg-white p-6 shadow">
    <h1 class="text-2xl font-bold">Angket Kepuasan Layanan BK</h1>
    <p class="mt-2 text-sm text-slate-600">Jenis layanan: {{ $angket->rpl->labelJenis() }} · {{ \Carbon\Carbon::parse($angket->rpl->tanggal)->format('d-m-Y') }}</p>
    <p class="mt-1 text-sm text-slate-600">Pilih satu jawaban untuk setiap pernyataan. Jawaban tidak meminta nama dan hanya disajikan sebagai rekap.</p>
    <div class="mt-4 rounded-lg bg-rose-50 p-3 text-sm">
        Skala: <strong>1 = STS</strong>, <strong>2 = TS</strong>, <strong>3 = S</strong>, <strong>4 = SS</strong>.
    </div>
    @if($errors->any())
        <div class="mt-4 rounded bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('kepuasan.publik.simpan', request()->route('token')) }}" class="mt-6 space-y-5">
        @csrf
        <div>
            <label for="kategori_responden" class="mb-1 block text-sm font-semibold">Kategori responden</label>
            <select id="kategori_responden" name="kategori_responden" required class="w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Pilih kategori</option>
                <option value="peserta_didik">Peserta didik</option>
                <option value="orang_tua">Orang tua</option>
                <option value="kepala_sekolah">Kepala sekolah</option>
                <option value="guru">Guru</option>
            </select>
        </div>
        @foreach($questions as $key => $question)
            <fieldset class="rounded-lg border border-slate-200 p-4">
                <legend class="px-1 text-sm font-semibold">{{ $question }}</legend>
                <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    @foreach($labels as $score => $label)
                        <label class="flex items-center gap-2 rounded bg-slate-50 p-2 text-sm">
                            <input type="radio" name="jawaban[{{ $key }}]" value="{{ $score }}" required>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        @endforeach
        <button class="rounded bg-rose-700 px-5 py-2.5 font-semibold text-white">Kirim jawaban</button>
    </form>
</main>
</body>
</html>
