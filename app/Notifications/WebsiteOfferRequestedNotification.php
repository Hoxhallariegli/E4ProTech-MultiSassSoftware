<?php

namespace App\Notifications;

use App\Models\WebsiteOfferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WebsiteOfferRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(public WebsiteOfferRequest $offerRequest)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $shop = $this->offerRequest->barberShop;
        $user = $this->offerRequest->user;
        $appName = config('app.name', 'E4ProTech Engine');

        return (new MailMessage)
            ->subject("🌐 Kërkesë e Re për Uebsajt të Personalizuar - {$shop?->name}")
            ->greeting("Përshëndetje Administrator!")
            ->line("Një kërkesë e re për uebsajt të personalizuar me domain është dërguar nga salloni **{$shop?->name}**.")
            ->line("**Detajet e Kërkesës:**")
            ->line("• **Salloni:** {$shop?->name} (ID: #{$shop?->id})")
            ->line("• **Pronari / Përdoruesi:** {$user?->name} ({$user?->email})")
            ->line("• **Numri i Telefonit:** " . ($this->offerRequest->phone ?: $shop?->phone ?: 'I pa specifikuar'))
            ->line("• **Domain-i i Dëshiruar:** " . ($this->offerRequest->desired_domain ?: 'I pa specifikuar'))
            ->line("• **Koha e Kërkesës:** " . $this->offerRequest->created_at->format('d/m/Y H:i'))
            ->line("• **Shënime / Udhëzime:** " . ($this->offerRequest->notes ?: 'Asnjë shënim shtesë'))
            ->action('Menaxho Kërkesat te Paneli Admin', url('/admin/subscriptions'))
            ->line("Faleminderit që përdorni {$appName}!");
    }
}
