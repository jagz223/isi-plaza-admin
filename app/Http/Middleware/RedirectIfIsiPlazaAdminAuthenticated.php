<?php

namespace App\Http\Middleware;

use App\Models\AdminToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfIsiPlazaAdminAuthenticated
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET') && $request->query('iniciar') !== null) {
            $request->session()->forget('isi_plaza_admin_token_id');
        }

        $id = $request->session()->get('isi_plaza_admin_token_id');

        if (is_numeric($id)) {
            $exists = AdminToken::query()->where('id', (int) $id)->where('is_active', true)->exists();
            if ($exists) {
                return redirect()->route('isi-plaza.gestion');
            }
            $request->session()->forget('isi_plaza_admin_token_id');
        }

        return $next($request);
    }
}
