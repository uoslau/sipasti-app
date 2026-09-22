<?php

/*
 * SEMENTARA — fitur Sensus Ekonomi.
 *
 * Aturan akses fitur ini: admin, ATAU user yang tergabung di tim kerja PEMEJA.
 * Aturan disimpan di satu tempat (method allows) supaya middleware dan menu sidebar
 * tidak pernah berbeda. Ubah nama timnya di konstanta ALLOWED_ALIAS bila perlu.
 */

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSensusEkonomiAccess
{
    private const ALLOWED_ALIAS = 'PEMEJA';

    public static function allows(?User $user): bool
    {
        if ($user === null) {
            return false;
        }

        return $user->isAdmin()
            || $user->timKerja()->where('alias_tim_kerja', self::ALLOWED_ALIAS)->exists();
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! self::allows(Auth::user())) {
            abort(403, 'Halaman ini hanya untuk admin dan tim kerja ' . self::ALLOWED_ALIAS . '.');
        }

        return $next($request);
    }
}
