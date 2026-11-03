<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\BarberShop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currentUser = auth()->user();
        if (!$currentUser) {
            return response()->json(['data' => []], 401);
        }

        $shopId = $request->input('barber_shop_id')
            ?? $request->input('shop_id')
            ?? $currentUser->barber_shop_id;

        $query = User::query()->where('is_active', 1);

        // Filter users strictly by shop_id / team
        if ($shopId) {
            $query->where(function($q) use ($shopId) {
                $q->where('barber_shop_id', $shopId)
                  ->orWhereHas('barberShops', function($bq) use ($shopId) {
                      $bq->where('barber_shops.id', $shopId);
                  });
            });
        } elseif (!$currentUser->is_global_admin) {
            // Non-global admins without explicit shop_id get empty list
            return response()->json(['data' => []]);
        }

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name', 'asc')->get(['id', 'name', 'email', 'barber_shop_id']);

        return response()->json([
            'data' => $users,
        ]);
    }
}
