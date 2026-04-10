<?php

namespace App\Http\Middleware;

use App\Helpers\MenuHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockHiddenModules
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.hide_legacy_modules', false)) {
            return $next($request);
        }

        if (MenuHelper::isAdminPathHidden('/' . ltrim($request->path(), '/'))) {
            abort(404);
        }

        return $next($request);
    }
}
