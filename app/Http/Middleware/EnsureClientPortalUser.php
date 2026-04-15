<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class EnsureClientPortalUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->client_id) {
            abort(403, 'Access denied. Client account required.');
        }

        // Share $client with all portal views and the app layout
        $client = $user->client;
        View::share('client', $client);

        return $next($request);
    }
}
