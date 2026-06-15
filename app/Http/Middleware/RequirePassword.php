<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && is_null($user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'password_required',
            ], 403);
        }

        return $next($request);
    }
}
