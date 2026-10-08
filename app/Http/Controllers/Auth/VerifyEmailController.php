<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified and activate user.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->markEmailAsVerified()) {
            /** @phpstan-ignore-next-line */
            event(new Verified($user));
        }

        // Activate User upon email verification
        $user->update(['is_active' => true]);

        auth()->loginUsingId($user->id, true);

        return redirect()->route('dashboard')->with('success', 'Adresa juaj e emailit u verifikua me sukses!');
    }
}
