@extends('layouts.app')
@section('title', 'Pengguna')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold text-slate-800">Pengguna (Akun Login)</h1>
    <a href="{{ route('pengguna.create') }}" class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded text-sm">+ Tambah Akun</a>
</div>
@if(session('status'))<div class="mb-4 p-3 bg-rose-50 text-rose-800 rounded text-sm">{{ session('status') }}</div>@endif

<div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-slate-100 text-left">
            <tr><th class="p-3">Nama</th><th class="p-3">Email</th><th class="p-3">Peran</th><th class="p-3">Aksi</th></tr>
        </thead>
        <tbody>
            @foreach($pengguna as $p)
            <tr class="border-t">
                <td class="p-3">{{ $p->name }}</td>
                <td class="p-3">{{ $p->email }}</td>
                <td class="p-3">
                    <span class="px-2 py-0.5 rounded text-xs bg-rose-50 text-rose-700">
                        {{ ['admin' => 'Admin', 'guru_bk' => 'Guru BK', 'wali_kelas' => 'Wali Kelas', 'kepala_sekolah' => 'Kepala Sekolah'][$p->role] ?? $p->role }}
                    </span>
                </td>
                <td class="p-3"><a href="{{ route('pengguna.edit', $p) }}" class="text-blue-600 hover:underline">Ubah / Reset Password</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="text-xs text-slate-500 mt-3">Akun tidak bisa dihapus karena menyimpan riwayat data. Ganti saja password atau datanya.</p>
@endsection
