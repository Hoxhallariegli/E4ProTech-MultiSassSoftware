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

        return (new MailMessage)
            ->from('evtech@e4protech.com', 'E4ProTech Engine')
            ->subject("Aktivizoni Sallonin Tuaj {$this->shop->name} 🚀 — E4ProTech Engine")
            ->greeting("Përshëndetje {$notifiable->name}!")
            ->line("Urimë për regjistrimin e sallonit tuaj **{$this->shop->name}** në platformën E4ProTech Engine.")
            ->line("Llogaria juaj ka përfituar **30 ditë provë falas (Trial)**. Faqja juaj e re e rezervimeve online është gati te adresa:")
            ->line("[{$shopLandingUrl}]({$shopLandingUrl})")
            ->action('Konfirmo E-mailin & Hyr në Panel ↗', $verificationUrl)
            ->line("Ju lutemi klikoni butonin e mësipërm për të verifikuar adresën tuaj të e-mailit dhe për të hapur panelin tuaj të menaxhimit.")
            ->line("Nëse nuk keni krijuar ju këtë llogari, mund ta anashkaloni këtë e-mail.")
            ->salutation("Me respekt, Ekipi i E4ProTech Engine");
    }
}
