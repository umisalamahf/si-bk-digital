<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Cara pakai di routes: ->middleware('role:admin,guru_bk')
// Artinya hanya user dengan role admin ATAU guru_bk yang boleh lewat.
// Ini sesuai aturan PROMPT: akses dicek lewat middleware, BUKAN cuma sembunyikan menu di Blade.
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak punya akses ke halaman ini.');
        }

        return $next($request);
    }
}
