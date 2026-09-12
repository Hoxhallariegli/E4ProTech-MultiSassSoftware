<?php

namespace App\Events;

use App\Models\{TestModule};
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestModuleChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public TestModule $item,
        public string $action,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('mobile.test-modules')];
    }

    public function broadcastAs(): string
    {
        return 'test-modules.changed';
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