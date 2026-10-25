<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AkunSiswaController extends Controller
{
    public function edit(Siswa $siswa)
    {
        $siswa->load('user');

        return view('siswa.akun', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        abort_unless($siswa->status === 'aktif', 422, 'Akun hanya dapat dibuat untuk siswa berstatus aktif.');

        $akun = $siswa->user;
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($akun?->id)],
            'password' => [$akun ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($akun, $data, $siswa) {
            if ($akun) {
                $akun->name = $siswa->nama;
                $akun->email = $data['email'];
                if (!empty($data['password'])) {
                    $akun->password = Hash::make($data['password']);
                }
                $akun->save();
            } else {
                $akun = User::create([
                    'name' => $siswa->nama,
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role' => 'siswa',
                ]);
                $siswa->user()->associate($akun);
                $siswa->save();
            }
        });

        return redirect()->route('siswa.show', $siswa)->with('status', 'Akun siswa berhasil disimpan.');
    }
}
