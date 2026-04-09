<?php

namespace App\Http\Middleware;

use App\Helpers\Permission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Usage: ->middleware('perm:inventory.manage')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! Permission::can($user, $permission)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}

