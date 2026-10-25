<?php
namespace App\Http\Controllers;

use App\Models\EvaluasiLayanan;
use App\Models\Rpl;
use Illuminate\Http\Request;

class EvaluasiLayananController extends Controller
{
    public function store(Request $request, Rpl $rpl)
    {
        $data = $request->validate([
            'jenis_evaluasi' => ['required', 'in:laiseg,laijapen,laijapan'],
            'tanggal' => ['required', 'date'],
            'hasil' => ['nullable', 'string'],
            'tindak_lanjut' => ['nullable', 'string'],
        ]);
        $data['rpl_id'] = $rpl->id;
        EvaluasiLayanan::create($data);

        return back()->with('status', 'Evaluasi berhasil ditambahkan.');
    }
}
