<?php

namespace App\Events;

use App\Models\{Payment};
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Payment $item,
        public string $action,
    ) {}

    public function broadcastOn(): array
    {
        $tenantId = $this->item->barber_shop_id ?? $this->item->id;
        return [new PrivateChannel('mobile.' . $tenantId . '.payments')];
    }

    public function broadcastAs(): string
    {
        return 'payments.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'data' => $this->action === 'deleted'
                ? ['id' => $this->item->getKey()]
                : $this->item->fresh()->toArray(),
        ];
    }
}