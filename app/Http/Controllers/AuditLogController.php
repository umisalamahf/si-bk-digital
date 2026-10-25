<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Penampil riwayat perubahan data siswa yang dicatat TRIGGER (trg_audit_update_siswa / trg_audit_delete_siswa).
class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('audit_log as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.*', 'u.name as nama_user')
            ->orderByDesc('a.id');

        if ($request->filled('aksi')) {
            $query->where('a.aksi', $request->aksi);
        }

        $log = $query->paginate(20)->withQueryString();

        // Ubah JSON lama/baru jadi daftar "kolom: lama -> baru", hanya kolom yang BENAR-BENAR berubah
        $log->getCollection()->transform(function ($baris) {
            $lama = json_decode($baris->data_lama ?? 'null', true) ?: [];
            $baru = json_decode($baris->data_baru ?? 'null', true) ?: [];
            $baris->perubahan = [];

            if ($baris->aksi === 'update') {
                foreach ($baru as $kolom => $nilaiBaru) {
                    if (($lama[$kolom] ?? null) != $nilaiBaru) {
                        $baris->perubahan[] = [$kolom, $lama[$kolom] ?? '-', $nilaiBaru ?? '-'];
                    }
                }
            } else {
                foreach ($lama as $kolom => $nilai) {
                    $baris->perubahan[] = [$kolom, $nilai ?? '-', 'dihapus'];
                }
            }
            return $baris;
        });

        return view('audit.index', compact('log'));
    }
}
