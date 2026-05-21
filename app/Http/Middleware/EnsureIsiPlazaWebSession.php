<?php

namespace App\Http\Middleware;

use App\Models\AdminToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsiPlazaWebSession
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('isi_plaza_admin_token_id');

        if (! is_numeric($id)) {
            return redirect()->route('isi-plaza.access');
        }

        $token = AdminToken::query()->where('id', (int) $id)->where('is_active', true)->first();

        if ($token === null) {
            $request->session()->forget('isi_plaza_admin_token_id');

            return redirect()->route('isi-plaza.access');
        }

        $request->attributes->set('isi_plaza_admin_token', $token);

        return $next($request);
    }
}
