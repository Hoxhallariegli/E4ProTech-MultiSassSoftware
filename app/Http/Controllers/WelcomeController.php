<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\BarberShop;
use Illuminate\Contracts\View\View;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        $shops = BarberShop::where('active', true)->orderBy('id', 'asc')->get();

        return view('welcome', [
            'shops' => $shops,
        ]);
    }
}
