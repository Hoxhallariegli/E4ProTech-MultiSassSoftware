<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\BarberShop;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicBookingController extends Controller
{
    public function show(string $slug, int $id)
    {
        $shop = BarberShop::where('slug', $slug)->first();

        $booking = Booking::withoutGlobalScope('barber_shop_access')
            ->where('id', $id)
            ->first();

        if (!$booking) {
            abort(404, 'Rezervimi nuk u gjet.');
        }

        $shop = $shop ?: $booking->barberShop;

        // Check if expired: appointment in the past OR cancelled/completed status
        $isExpired = false;
        $appointmentAt = $booking->appointment_at ? Carbon::parse($booking->appointment_at) : null;

        if (in_array($booking->status, ['cancelled', 'completed'], true) || ($appointmentAt && $appointmentAt->isPast())) {
            $isExpired = true;
        }

        return view('public-booking', [
            'shop' => $shop,
            'booking' => $booking,
            'isExpired' => $isExpired,
            'appointmentAt' => $appointmentAt,
        ]);
    }

    public function cancel(Request $request, string $slug, int $id)
    {
        $booking = Booking::withoutGlobalScope('barber_shop_access')
            ->where('id', $id)
            ->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Rezervimi nuk u gjet.');
        }

        $appointmentAt = $booking->appointment_at ? Carbon::parse($booking->appointment_at) : null;

        if (in_array($booking->status, ['cancelled', 'completed'], true) || ($appointmentAt && $appointmentAt->isPast())) {
            return redirect()->back()->with('error', 'Kjo lidhje ka skaduar. Nuk mund ta anuloni më këtë takim.');
        }

        $booking->update(['status' => 'cancelled']);
        AuditTrail::logCustom($booking, 'update', 'Bookings', "Anuluar nga klienti nëpërmjet linkut publik (#{$booking->id})");

        return redirect()->back()->with('success', 'Takimi juaj u anulua me sukses.');
    }
}
