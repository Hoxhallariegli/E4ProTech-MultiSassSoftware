<?php

namespace App\Notifications;

use App\Models\BarberShop;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class SalonRegisteredNotification extends Notification
{
    use Queueable;

    public BarberShop $shop;

    public function __construct(BarberShop $shop)
    {
        $this->shop = $shop;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );

        $shopLandingUrl = url("s/{$this->shop->slug}");
        $fromAddress = config('mail.from.address') ?: env('MAIL_USERNAME', env('MAIL_FROM_ADDRESS', 'evtech@e4protech.com'));
        $fromName = config('mail.from.name') ?: env('APP_NAME', 'E4ProTech Engine');

        return (new MailMessage)
            ->from($fromAddress, $fromName)
            ->subject("Aktivizoni Sallonin Tuaj {$this->shop->name} 🚀 — {$fromName}")
            ->greeting("Përshëndetje {$notifiable->name}!")
            ->line("Urimë për regjistrimin e sallonit tuaj **{$this->shop->name}** në platformën {$fromName}.")
            ->line("Llogaria juaj ka përfituar **30 ditë provë falas (Trial)**. Faqja juaj e re e rezervimeve online është gati te adresa:")
            ->line("[{$shopLandingUrl}]({$shopLandingUrl})")
            ->action('Konfirmo E-mailin & Hyr në Panel ↗', $verificationUrl)
            ->line("Ju lutemi klikoni butonin e mësipërm për të verifikuar adresën tuaj të e-mailit dhe për të hapur panelin tuaj të menaxhimit.")
            ->line("Nëse nuk keni krijuar ju këtë llogari, mund ta anashkaloni këtë e-mail.")
            ->salutation("Me respekt, Ekipi i {$fromName}");
    }
}
