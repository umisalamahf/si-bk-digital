<?php

namespace App\Http\Controllers;

use App\Models\AsesmenButir;
use App\Models\AsesmenInstrumen;
use App\Models\AsesmenJenis;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsesmenInstrumenController extends Controller
{
    public function index()
    {
        $instrumen = AsesmenInstrumen::with('jenis')
            ->withCount('butir')
            ->orderBy('nama_instrumen')
            ->paginate(20);

        return view('asesmen.instrumen.index', compact('instrumen'));
    }

    public function create()
    {
        $jenis = AsesmenJenis::orderBy('nama')->get();

        return view('asesmen.instrumen.form', compact('jenis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'asesmen_jenis_id' => ['required', 'integer', 'exists:asesmen_jenis,id'],
            'nama_instrumen' => ['required', 'string', 'max:150'],
            'jenjang' => ['nullable', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string'],
            'butir' => ['required', 'array', 'min:1'],
            'butir.*.pertanyaan' => ['required', 'string'],
            'butir.*.kategori' => ['required', 'string', 'max:50'],
            'butir.*.bobot' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($data) {
            $instrumen = AsesmenInstrumen::create([
                'asesmen_jenis_id' => $data['asesmen_jenis_id'],
                'nama_instrumen' => $data['nama_instrumen'],
                'jenjang' => $data['jenjang'] ?? null,
                'keterangan' => $data['keterangan'] ?? null,
            ]);

            foreach (array_values($data['butir']) as $nomor => $butir) {
                AsesmenButir::create([
                    'instrumen_id' => $instrumen->id,
                    'nomor' => $nomor + 1,
                    'pertanyaan' => $butir['pertanyaan'],
                    'kategori' => $butir['kategori'],
                    'bobot' => $butir['bobot'],
                ]);
            }
        });

        return redirect()->route('asesmen.instrumen.index')->with('status', 'Instrumen dan butir asesmen berhasil disimpan.');
    }

    public function destroy(AsesmenInstrumen $instrumen)
    {
        try {
            DB::transaction(function () use ($instrumen) {
                AsesmenButir::where('instrumen_id', $instrumen->id)->delete();
                $instrumen->delete();
            });
        } catch (QueryException) {
            return back()->with('error', 'Instrumen tidak dapat dihapus karena sudah digunakan pada link atau jawaban asesmen.');
        }

        return redirect()->route('asesmen.instrumen.index')->with('status', 'Instrumen asesmen berhasil dihapus.');
    }
}
