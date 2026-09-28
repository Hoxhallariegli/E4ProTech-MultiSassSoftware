<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPermissionsTeamId
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // 1. If Global Admin, we UNSET the team ID so Spatie sees everything
            if ($user->is_global_admin) {
                setPermissionsTeamId(null);
            }
            // 2. For others, use their active shop ID
            elseif ($user->barber_shop_id) {
                setPermissionsTeamId($user->barber_shop_id);
            }
        }

        return $next($request);
    }
}
