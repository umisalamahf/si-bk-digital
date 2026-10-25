<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

// Middleware ini WAJIB dipasang di web.php (grup 'auth') supaya trigger
// trg_audit_update_siswa / trg_audit_delete_siswa tahu SIAPA yang mengubah data.
// @app_user_id itu "variabel sesi" di level KONEKSI DATABASE, bukan session Laravel biasa,
// makanya harus di-set ulang di SETIAP request.
class SetAuditUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET @app_user_id = ?', [$request->user()->id]);
        }
        return $next($request);
    }
}
