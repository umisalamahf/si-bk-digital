@extends('layouts.app')
@section('title', $siswa->exists ? 'Ubah Siswa' : 'Tambah Siswa')

@section('content')
<div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <p class="text-sm font-medium tracking-wide text-brand-700">Guru BK hadir untuk memahami, mendampingi, dan membantu siswa berkembang.</p>
        <h1 class="mt-2 text-2xl font-bold tracking-wide text-slate-800">BIODATA SISWA</h1>
        <p class="mt-1 text-sm text-slate-600">Setiap Anak Unik, Setiap Potensi Berarti</p>
    </div>
    <a href="{{ route('siswa.index') }}" class="text-sm font-medium text-brand-700 hover:underline">&larr; Kembali ke Data Siswa</a>
</div>

@if($errors->any())
<div class="mb-4 p-3 bg-red-100 text-red-700 text-sm rounded">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $siswa->exists ? route('siswa.update',$siswa) : route('siswa.store') }}"
      class="bg-white rounded-2xl shadow-sm ring-1 ring-rose-100 p-5 md:p-6 space-y-6">
    @csrf
    @if($siswa->exists) @method('PUT') @endif

    <section class="space-y-4">
    <h2 class="text-lg font-semibold text-slate-800 border-b border-rose-100 pb-2">I. DATA PRIBADI SISWA</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Lengkap *</label>
            <input type="text" name="nama" value="{{ old('nama', $siswa->nama) }}" required maxlength="150"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nama Panggilan</label>
            <input type="text" name="nama_panggilan" value="{{ old('nama_panggilan', $siswa->nama_panggilan) }}" maxlength="100"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">NIS *</label>
            <input type="text" name="nis" value="{{ old('nis', $siswa->nis) }}" required
                class="w-full border border-slate-300 rounded px-3 py-2">
            <p class="text-xs text-slate-400 mt-1">Diambil dari data sekolah, bukan dibuat otomatis.</p>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">NISN</label>
            <input type="text" name="nisn" value="{{ old('nisn', $siswa->nisn) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Kelas *</label>
        <select name="kelas_id" required class="w-full border border-slate-300 rounded px-3 py-2">
            <option value="">-- Pilih --</option>
            @foreach($kelas as $k)
                <option value="{{ $k->id }}" @selected(old('kelas_id',$siswa->kelas_id)==$k->id)>{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">No. Absen</label>
        <input type="number" name="no_absen" min="1" max="65535" value="{{ old('no_absen', $siswa->no_absen) }}"
            class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Jenis Kelamin *</label>
            <select name="jenis_kelamin" required class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="L" @selected(old('jenis_kelamin',$siswa->jenis_kelamin)=='L')>Laki-laki</option>
                <option value="P" @selected(old('jenis_kelamin',$siswa->jenis_kelamin)=='P')>Perempuan</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Agama</label>
            <input type="text" name="agama" value="{{ old('agama', $siswa->agama) }}" maxlength="50"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tempat Lahir</label>
            <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}" maxlength="100"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Status *</label>
            <select name="status" required class="w-full border border-slate-300 rounded px-3 py-2">
                <option value="aktif" @selected(old('status',$siswa->status ?: 'aktif')=='aktif')>Aktif</option>
                <option value="tidak_aktif" @selected(old('status',$siswa->status)=='tidak_aktif')>Tidak Aktif (pindah/keluar)</option>
                <option value="alumni" @selected(old('status',$siswa->status)=='alumni')>Alumni</option>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Alamat Lengkap</label>
        <textarea name="alamat" rows="3" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('alamat', $siswa->alamat) }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Nomor Kontak Siswa</label>
        <input type="text" name="no_hp_siswa" value="{{ old('no_hp_siswa', $siswa->no_hp_siswa) }}"
            class="w-full border border-slate-300 rounded px-3 py-2">
    </div>
    </section>

    <section class="space-y-4">
    <h2 class="text-lg font-semibold text-slate-800 border-b border-rose-100 pb-2">II. DATA ORANG TUA / WALI</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Orang Tua (Umum)</label>
            <input type="text" name="nama_ortu" value="{{ old('nama_ortu', $siswa->nama_ortu) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nomor Kontak Orang Tua</label>
            <input type="text" name="no_hp_ortu" value="{{ old('no_hp_ortu', $siswa->no_hp_ortu) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium mb-1">Nama Ayah</label>
            <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $siswa->nama_ayah) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Pekerjaan Ayah</label>
            <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $siswa->pekerjaan_ayah) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. HP Ayah</label>
            <input type="text" name="no_hp_ayah" value="{{ old('no_hp_ayah', $siswa->no_hp_ayah) }}" maxlength="20"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nama Ibu</label>
            <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $siswa->nama_ibu) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Pekerjaan Ibu</label>
            <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $siswa->pekerjaan_ibu) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. HP Ibu</label>
            <input type="text" name="no_hp_ibu" value="{{ old('no_hp_ibu', $siswa->no_hp_ibu) }}" maxlength="20"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Nama Wali</label>
            <input type="text" name="nama_wali" value="{{ old('nama_wali', $siswa->nama_wali) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Hubungan dengan Siswa</label>
            <input type="text" name="hubungan_wali" value="{{ old('hubungan_wali', $siswa->hubungan_wali) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">No. HP Wali</label>
            <input type="text" name="no_hp_wali" value="{{ old('no_hp_wali', $siswa->no_hp_wali) }}" maxlength="20"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Pekerjaan Wali</label>
            <input type="text" name="pekerjaan_wali" value="{{ old('pekerjaan_wali', $siswa->pekerjaan_wali) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Penghasilan Orang Tua/Wali per Bulan (Rp)</label>
            <input type="number" name="penghasilan_orang_tua" min="0" step="0.01"
                value="{{ old('penghasilan_orang_tua', $siswa->penghasilan_orang_tua) }}"
                class="w-full border border-slate-300 rounded px-3 py-2">
        </div>
    </div>
    </section>

    <section class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-800 border-b border-rose-100 pb-2">III. DATA PENDUKUNG</h2>
        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">Hobi / Minat</label>
                <textarea name="hobi_minat" rows="2" maxlength="1000" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('hobi_minat', $siswa->hobi_minat) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Bakat / Potensi</label>
                <textarea name="bakat_potensi" rows="2" maxlength="1000" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('bakat_potensi', $siswa->bakat_potensi) }}</textarea>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Cita-cita</label>
                <input name="cita_cita" value="{{ old('cita_cita', $siswa->cita_cita) }}" maxlength="500" class="w-full border border-slate-300 rounded px-3 py-2">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Kondisi khusus yang perlu diperhatikan</label>
                <textarea name="kondisi_khusus" rows="3" maxlength="10000" class="w-full border border-slate-300 rounded px-3 py-2">{{ old('kondisi_khusus', $siswa->kondisi_khusus) }}</textarea>
            </div>
        </div>
    </section>

    <section class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-800 border-b border-rose-100 pb-2">IV. CATATAN GURU BK</h2>
        <textarea name="catatan_guru_bk" rows="4" maxlength="10000" class="w-full border border-slate-300 rounded px-3 py-2"
            placeholder="Catatan perkembangan atau pengamatan Guru BK">{{ old('catatan_guru_bk', $siswa->catatan_guru_bk) }}</textarea>
    </section>

    <section class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-800 border-b border-rose-100 pb-2">Catatan Biodata Tambahan</h2>
        <textarea name="catatan_penting" rows="4" class="w-full border border-slate-300 rounded px-3 py-2"
            placeholder="Catatan biodata yang perlu diperhatikan">{{ old('catatan_penting', $siswa->catatan_penting) }}</textarea>
    </section>

    <div class="flex gap-2 pt-2">
        <button class="bg-rose-600 hover:bg-rose-700 text-white px-5 py-2 rounded">Simpan</button>
        <a href="{{ route('siswa.index') }}" class="px-5 py-2 rounded border border-slate-300">Batal</a>
    </div>
</form>
<p class="mt-5 text-sm text-slate-600">Bimbingan hari ini, kesuksesan esok hari.</p>
@endsection
