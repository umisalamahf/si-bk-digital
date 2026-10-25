<?php
namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::withCount('siswa')->orderBy('nama_kelas')->get();
        $guruWali = DB::table('guru_wali')->orderBy('nama')->get();
        return view('siswa.kelas', compact('kelas', 'guruWali'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'integer', 'min:7', 'max:12'],
            'tahun_ajaran' => ['required', 'string', 'max:12'],
            'guru_wali_id' => ['nullable', 'exists:guru_wali,id'],
        ]);
        // sekolah_id disederhanakan: ambil sekolah pertama (karena 1 guru BK = 1 sekolah)
        $data['sekolah_id'] = DB::table('sekolah')->value('id');
        Kelas::create($data);
        return back()->with('status', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        $guruWali = DB::table('guru_wali')->orderBy('nama')->get();
        return view('siswa.kelas_edit', compact('kelas', 'guruWali'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $data = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50'],
            'tingkat' => ['required', 'integer', 'min:7', 'max:12'],
            'tahun_ajaran' => ['required', 'string', 'max:12'],
            'guru_wali_id' => ['nullable', 'exists:guru_wali,id'],
        ]);

        $kelas->update($data);

        return redirect()->route('kelas.index')->with('status', 'Data kelas berhasil diperbarui.');
    }
}
