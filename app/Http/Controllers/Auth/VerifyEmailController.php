<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class VerifyEmailController extends Controller
{
    /**
     * Verify the user's email address using the signed URL, activate user and shop.
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        // 1. Verify URL signature
        if (!$request->hasValidSignature()) {
            abort(403, 'Lidhja e verifikimit ka skaduar ose nuk është e vlefshme.');
        }

        // 2. Find User by ID
        $user = User::findOrFail($id);

        // 3. Verify hash matches user email
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Kodi i verifikimit nuk përputhet.');
        }

        // 4. Mark email as verified
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // 5. Activate User and BarberShop
        $user->update(['is_active' => true]);

        if ($user->barberShop) {
            $user->barberShop->update(['active' => true]);
        }

        // 6. Login user
        Auth::login($user, true);

        return redirect()->route('dashboard')->with('success', 'Adresa juaj e e-mailit u konfirmua dhe llogaria juaj u aktivizua me sukses!');
    }
}
