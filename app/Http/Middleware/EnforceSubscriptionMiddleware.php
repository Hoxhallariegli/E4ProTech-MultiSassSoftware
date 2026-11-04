<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceSubscriptionMiddleware
{
    /**
     * Globally enforce subscription expiration for non-global-admin users and shops.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            // Skip check for Global Super Admins
            if (!$user->is_global_admin) {
                $shop = $user->barberShop;

                if ($shop && $shop->is_expired) {
                    // Block write actions (POST, PUT, PATCH, DELETE) for expired subscriptions
                    if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
                        // Allow renewal and logout/login routes
                        $allowedRoutes = ['admin.subscriptions', 'admin.subscription-renewals', 'logout', 'login', 'verification', 'language'];
                        $currentRoute = $request->route()?->getName() ?? '';
                        $uri = strtolower($request->getRequestUri());
                        $path = strtolower($request->path());

                        $isAllowed = str_contains($uri, 'logout') ||
                                     str_contains($path, 'logout') ||
                                     str_contains($currentRoute, 'logout') ||
                                     str_contains($uri, 'login') ||
                                     str_contains($path, 'login') ||
                                     str_contains($uri, 'subscription-renewals') ||
                                     str_contains($path, 'subscription-renewals') ||
                                     str_contains($uri, 'subscriptions') ||
                                     str_contains($path, 'subscriptions') ||
                                     str_contains($uri, 'livewire') ||
                                     str_contains($path, 'livewire');

                        if (!$isAllowed) {
                            foreach ($allowedRoutes as $allowed) {
                                if (str_contains($currentRoute, $allowed)) {
                                    $isAllowed = true;
                                    break;
                                }
                            }
                        }

                        if (!$isAllowed) {
                            if ($request->expectsJson() || $request->is('api/*')) {
                                return response()->json([
                                    'message' => "Abonimi për sallonin '{$shop->name}' ka skaduar (0 Ditë Mbetura). Ju lutemi renovoni abonimin tuaj për të vazhduar.",
                                    'is_expired' => true,
                                ], 402);
                            }
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
