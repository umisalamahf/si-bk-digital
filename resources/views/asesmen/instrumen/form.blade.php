@extends('layouts.app')
@section('title', 'Tambah Instrumen Asesmen')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-4">Tambah Instrumen Asesmen</h1>
<p class="text-sm text-slate-500 mb-4">Masukkan materi dan kategori sesuai instrumen resmi sekolah. DCM memakai jawaban Ya/Tidak. Sosiometri memakai pilihan siswa lain; rekap jumlah pilihan adalah data mentah dan harus ditafsirkan sesuai pedoman Guru BK.</p>
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
    <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif
<form method="POST" action="{{ route('asesmen.instrumen.store') }}" class="bg-white rounded-xl shadow p-5 space-y-4">
    @csrf
    <div class="grid md:grid-cols-2 gap-3">
        <div>
            <label for="asesmen_jenis_id" class="block text-sm font-medium mb-1">Jenis Asesmen</label>
            <select id="asesmen_jenis_id" name="asesmen_jenis_id" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
                <option value="">-- Pilih Jenis --</option>
                @foreach($jenis as $item)<option value="{{ $item->id }}" @selected(old('asesmen_jenis_id') == $item->id)>{{ $item->nama }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="nama_instrumen" class="block text-sm font-medium mb-1">Nama Instrumen</label>
            <input id="nama_instrumen" name="nama_instrumen" value="{{ old('nama_instrumen') }}" maxlength="150" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label for="jenjang" class="block text-sm font-medium mb-1">Jenjang</label>
            <input id="jenjang" name="jenjang" value="{{ old('jenjang') }}" maxlength="50" class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label for="keterangan" class="block text-sm font-medium mb-1">Keterangan</label>
            <input id="keterangan" name="keterangan" value="{{ old('keterangan') }}" class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
        </div>
    </div>

    <section>
        <div class="flex flex-wrap justify-between items-center gap-2 mb-2">
            <div>
                <h2 class="font-semibold">Butir Pertanyaan</h2>
                <p class="text-xs text-slate-500">Kategori dan bobot harus mengikuti instrumen serta pedoman skoring sekolah.</p>
            </div>
            <button type="button" id="add-butir" class="border border-slate-300 rounded px-3 py-1.5 text-sm">Tambah Butir</button>
        </div>
        <div id="butir-list" class="space-y-3">
            @foreach(old('butir', [['pertanyaan' => '', 'kategori' => '', 'bobot' => 1]]) as $index => $butir)
                <div class="butir-item border rounded-lg p-3 grid md:grid-cols-12 gap-2">
                    <div class="md:col-span-6">
                        <label class="block text-xs text-slate-500 mb-1">Pertanyaan</label>
                        <textarea name="butir[{{ $index }}][pertanyaan]" rows="2" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">{{ $butir['pertanyaan'] ?? '' }}</textarea>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs text-slate-500 mb-1">Kategori Skor</label>
                        <input name="butir[{{ $index }}][kategori]" value="{{ $butir['kategori'] ?? '' }}" maxlength="50" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs text-slate-500 mb-1">Bobot (0–100)</label>
                        <input type="number" name="butir[{{ $index }}][bobot]" value="{{ $butir['bobot'] ?? 1 }}" min="0" max="100" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                    </div>
                    <div class="md:col-span-1 flex items-end">
                        <button type="button" class="remove-butir text-red-600 text-sm" aria-label="Hapus butir">Hapus</button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
    <div class="flex gap-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan Instrumen</button>
        <a href="{{ route('asesmen.instrumen.index') }}" class="border border-slate-300 px-5 py-2 rounded">Batal</a>
    </div>
</form>
<script>
    (() => {
        const list = document.getElementById('butir-list');
        let nextIndex = list.children.length;

        document.getElementById('add-butir').addEventListener('click', () => {
            const index = nextIndex++;
            const row = document.createElement('div');
            row.className = 'butir-item border rounded-lg p-3 grid md:grid-cols-12 gap-2';
            row.innerHTML = `
                <div class="md:col-span-6">
                    <label class="block text-xs text-slate-500 mb-1">Pertanyaan</label>
                    <textarea name="butir[${index}][pertanyaan]" rows="2" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm"></textarea>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs text-slate-500 mb-1">Kategori Skor</label>
                    <input name="butir[${index}][kategori]" maxlength="50" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs text-slate-500 mb-1">Bobot (0–100)</label>
                    <input type="number" name="butir[${index}][bobot]" value="1" min="0" max="100" required class="w-full border border-slate-300 rounded px-2 py-1.5 text-sm">
                </div>
                <div class="md:col-span-1 flex items-end">
                    <button type="button" class="remove-butir text-red-600 text-sm" aria-label="Hapus butir">Hapus</button>
                </div>`;
            list.appendChild(row);
        });

        list.addEventListener('click', (event) => {
            if (event.target.closest('.remove-butir') && list.children.length > 1) {
                event.target.closest('.butir-item').remove();
            }
        });
    })();
</script>
@endsection
