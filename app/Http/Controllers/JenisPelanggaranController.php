<?php

namespace App\Http\Controllers;

use App\Models\JenisPelanggaran;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisPelanggaranController extends Controller
{
    public function index()
    {
        $pelanggaran = JenisPelanggaran::withCount('poinSiswa')
            ->orderBy('kode')
            ->paginate(15);
        $aturanAkumulasi = DB::table('aturan_akumulasi_poin')->orderBy('poin_min')->get();

        return view('jenis-pelanggaran.index', compact('pelanggaran', 'aturanAkumulasi'));
    }

    public function create()
    {
        return view('jenis-pelanggaran.form', ['pelanggaran' => new JenisPelanggaran()]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        JenisPelanggaran::create([
            'kode' => strtoupper($data['kode_pelanggaran']),
            'nama_pelanggaran' => $data['nama_pelanggaran'],
            'kategori' => $data['kategori'],
            'kategori_kelompok' => $data['kategori_kelompok'],
            'penanganan' => $data['penanganan'],
            'poin' => $data['bobot_point'],
        ]);

        return redirect()->route('jenis-pelanggaran.index')
            ->with('status', 'Kode pelanggaran berhasil ditambahkan.');
    }

    public function edit(JenisPelanggaran $jenisPelanggaran)
    {
        return view('jenis-pelanggaran.form', ['pelanggaran' => $jenisPelanggaran]);
    }

    public function update(Request $request, JenisPelanggaran $jenisPelanggaran)
    {
        $data = $this->validatedData($request, $jenisPelanggaran);

        $jenisPelanggaran->update([
            'kode' => strtoupper($data['kode_pelanggaran']),
            'nama_pelanggaran' => $data['nama_pelanggaran'],
            'kategori' => $data['kategori'],
            'kategori_kelompok' => $data['kategori_kelompok'],
            'penanganan' => $data['penanganan'],
            'poin' => $data['bobot_point'],
        ]);

        return redirect()->route('jenis-pelanggaran.index')
            ->with('status', 'Kode pelanggaran berhasil diperbarui.');
    }

    public function destroy(JenisPelanggaran $jenisPelanggaran)
    {
        if ($jenisPelanggaran->poinSiswa()->exists()) {
            return redirect()->route('jenis-pelanggaran.index')
                ->with('error', 'Kode tidak dapat dihapus karena sudah digunakan dalam catatan poin siswa.');
        }

        $jenisPelanggaran->delete();

        return redirect()->route('jenis-pelanggaran.index')
            ->with('status', 'Kode pelanggaran berhasil dihapus.');
    }

    private function validatedData(Request $request, ?JenisPelanggaran $current = null): array
    {
        return $request->validate([
            'kode_pelanggaran' => [
                'required',
                'string',
                'max:20',
                Rule::unique('jenis_pelanggaran', 'kode')->ignore($current?->id),
            ],
            'nama_pelanggaran' => ['required', 'string', 'max:150'],
            'kategori' => ['required', Rule::in(['ringan', 'sedang', 'berat'])],
            'kategori_kelompok' => ['required', 'string', 'max:100'],
            'bobot_point' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'penanganan' => ['required', 'string', 'max:2000'],
        ]);
    }
}
