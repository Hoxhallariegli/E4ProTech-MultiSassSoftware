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
    /**
     * Resolves a booking by obfuscated short code (e.g. 23e207) or numeric ID.
     */
    public static function resolveBooking(string $code): ?Booking
    {
        $code = trim($code);
        if (empty($code)) return null;

        $bookingId = null;

        // Try decoding obfuscated hex code (ID * 7919)
        if (ctype_xdigit($code)) {
            $val = hexdec($code);
            if ($val > 0 && ($val % 7919 === 0)) {
                $bookingId = (int) ($val / 7919);
            }
        }

        // Fallback if numeric
        if (!$bookingId && is_numeric($code)) {
            $bookingId = (int) $code;
        }

        if (!$bookingId) return null;

        return Booking::withoutGlobalScope('barber_shop_access')
            ->where('id', $bookingId)
            ->first();
    }

    public function showShort(string $code)
    {
        $booking = static::resolveBooking($code);

        if (!$booking) {
            abort(404, 'Rezervimi nuk u gjet.');
        }

        return $this->renderView($booking, $code);
    }

    public function confirmShort(Request $request, string $code)
    {
        $booking = static::resolveBooking($code);

        if (!$booking) {
            return redirect()->back()->with('error', 'Rezervimi nuk u gjet.');
        }

        return $this->processConfirmation($booking);
    }

    public function cancelShort(Request $request, string $code)
    {
        $booking = static::resolveBooking($code);

        if (!$booking) {
            return redirect()->back()->with('error', 'Rezervimi nuk u gjet.');
        }

        return $this->processCancellation($booking);
    }

    public function show(string $slug, int $id)
    {
        $booking = Booking::withoutGlobalScope('barber_shop_access')
            ->where('id', $id)
            ->first();

        if (!$booking) {
            abort(404, 'Rezervimi nuk u gjet.');
        }

        $code = dechex($booking->id * 7919);
        return $this->renderView($booking, $code);
    }

    public function confirm(Request $request, string $slug, int $id)
    {
        $booking = Booking::withoutGlobalScope('barber_shop_access')
            ->where('id', $id)
            ->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Rezervimi nuk u gjet.');
        }

        return $this->processConfirmation($booking);
    }

    public function cancel(Request $request, string $slug, int $id)
    {
        $booking = Booking::withoutGlobalScope('barber_shop_access')
            ->where('id', $id)
            ->first();

        if (!$booking) {
            return redirect()->back()->with('error', 'Rezervimi nuk u gjet.');
        }

        return $this->processCancellation($booking);
    }

    protected function renderView(Booking $booking, string $code)
    {
        $shop = $booking->barberShop;
        $appointmentAt = $booking->appointment_at ? Carbon::parse($booking->appointment_at) : null;

        // Check if expired: appointment in the past OR cancelled/completed status
        $isExpired = false;
        if (in_array($booking->status, ['cancelled', 'completed'], true) || ($appointmentAt && $appointmentAt->isPast())) {
            $isExpired = true;
        }

        return view('public-booking', [
            'shop' => $shop,
            'booking' => $booking,
            'isExpired' => $isExpired,
            'appointmentAt' => $appointmentAt,
            'shortCode' => $code,
        ]);
    }

    protected function processConfirmation(Booking $booking)
    {
        $appointmentAt = $booking->appointment_at ? Carbon::parse($booking->appointment_at) : null;

        if (in_array($booking->status, ['cancelled', 'completed'], true) || ($appointmentAt && $appointmentAt->isPast())) {
            return redirect()->back()->with('error', 'Kjo lidhje ka skaduar. Nuk mund ta konfirmoni më këtë takim.');
        }

        if ($booking->status !== 'confirmed') {
            $booking->update(['status' => 'confirmed']);
            AuditTrail::logCustom($booking, 'update', 'Bookings', "Konfirmuar nga klienti nëpërmjet linkut publik (#{$booking->id})");
        }

        return redirect()->back()->with('success', 'Takimi juaj u konfirmua me sukses. Ju mirëpresim!');
    }

    protected function processCancellation(Booking $booking)
    {
        $appointmentAt = $booking->appointment_at ? Carbon::parse($booking->appointment_at) : null;

        if (in_array($booking->status, ['cancelled', 'completed'], true) || ($appointmentAt && $appointmentAt->isPast())) {
            return redirect()->back()->with('error', 'Kjo lidhje ka skaduar. Nuk mund ta anuloni më këtë takim.');
        }

        $booking->update(['status' => 'cancelled']);
        AuditTrail::logCustom($booking, 'update', 'Bookings', "Anuluar nga klienti nëpërmjet linkut publik (#{$booking->id})");

        return redirect()->back()->with('success', 'Takimi juaj u anulua me sukses.');
    }
}
