@extends('layouts.app')
@section('title', 'Instrumen BK')

@section('content')
<h1 class="text-2xl font-bold text-slate-800 mb-1">Bank Instrumen BK</h1>
<p class="text-slate-500 mb-4">Simpan template, dokumen, dan instrumen pendukung layanan BK. File disimpan secara privat dan hanya dapat diunduh setelah login.</p>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
    <ul class="list-disc list-inside">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('instrumen.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow p-5 space-y-3 mb-6 max-w-3xl">
    @csrf
    <h2 class="font-semibold">Tambah Dokumen Instrumen</h2>
    <div class="grid md:grid-cols-2 gap-3">
        <div>
            <label for="judul" class="block text-sm font-medium mb-1">Judul</label>
            <input id="judul" name="judul" value="{{ old('judul') }}" maxlength="150" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
        </div>
        <div>
            <label for="kategori" class="block text-sm font-medium mb-1">Kategori</label>
            <input id="kategori" name="kategori" value="{{ old('kategori') }}" maxlength="100" placeholder="Mis. asesmen, template, dokumen pendukung" required class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
        </div>
    </div>
    <textarea name="keterangan" rows="2" placeholder="Keterangan (opsional)" class="w-full border border-slate-300 rounded px-3 py-2 text-sm">{{ old('keterangan') }}</textarea>
    <div>
        <label for="file" class="block text-sm font-medium mb-1">File (PDF, Word, Excel, atau PowerPoint; maks. 10 MB)</label>
        <input id="file" type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="w-full border border-slate-300 rounded px-3 py-2 text-sm">
    </div>
    <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded text-sm">Unggah</button>
</form>

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Judul</th><th class="p-3">Kategori</th><th class="p-3">File</th><th class="p-3">Diunggah</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
            @forelse($instrumen as $item)
                <tr class="border-t">
                    <td class="p-3">{{ $item->judul }}@if($item->keterangan)<p class="text-xs text-slate-500 mt-1">{{ $item->keterangan }}</p>@endif</td>
                    <td class="p-3">{{ $item->kategori }}</td>
                    <td class="p-3">{{ $item->nama_file }}<p class="text-xs text-slate-500">{{ number_format($item->ukuran_file / 1024, 1, ',', '.') }} KB</p></td>
                    <td class="p-3">{{ $item->created_at?->format('d-m-Y') ?? '-' }}</td>
                    <td class="p-3 whitespace-nowrap">
                        <a href="{{ route('instrumen.download', $item) }}" class="text-rose-700 hover:underline">Unduh</a>
                        <form method="POST" action="{{ route('instrumen.destroy', $item) }}" class="inline" onsubmit="return confirm('Hapus dokumen instrumen ini?')">
                            @csrf @method('DELETE')
                            <button class="ml-2 text-red-600 hover:underline">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-6 text-center text-slate-400">Belum ada dokumen instrumen tersimpan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $instrumen->links() }}</div>
@endsection
