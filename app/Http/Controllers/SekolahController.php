<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

// Identitas sekolah dipakai di kop surat PDF. Hanya ada SATU baris di tabel sekolah.
class SekolahController extends Controller
{
    public function edit()
    {
        $sekolah = DB::table('sekolah')->first();
        return view('sekolah.edit', compact('sekolah'));
    }

    public function update(Request $request)
    {
        $sekolah = DB::table('sekolah')->first();

        $data = $request->validate([
            'nama_sekolah' => ['required', 'string', 'max:150'],
            'npsn' => ['nullable', 'string', 'max:20', Rule::unique('sekolah', 'npsn')->ignore($sekolah->id ?? null)],
            'alamat' => ['nullable', 'string'],
            'kepala_sekolah' => ['nullable', 'string', 'max:150'],
            'kepala_sekolah_nip' => ['nullable', 'string', 'max:30'],
        ]);

        if ($sekolah) {
            DB::table('sekolah')->where('id', $sekolah->id)->update($data + ['updated_at' => now()]);
        } else {
            DB::table('sekolah')->insert($data + ['created_at' => now(), 'updated_at' => now()]);
        }

        return redirect()->route('sekolah.edit')->with('status', 'Identitas sekolah berhasil disimpan. Kop surat PDF ikut berubah.');
    }
}
