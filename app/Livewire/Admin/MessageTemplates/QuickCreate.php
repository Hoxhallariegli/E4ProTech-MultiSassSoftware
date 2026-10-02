<?php

namespace App\Livewire\Admin\MessageTemplates;

use App\Models\MessageTemplate;
use App\Domain\MessageTemplate\DTOs\MessageTemplateDTO;
use App\Domain\MessageTemplate\Actions\CreateMessageTemplateAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class QuickCreate extends Component
{
    use WithPagination;

    public $barber_shop_id = '';
    public $channel = 'sms';
    public $type = 'confirmation';
    public $content_sq = '';
    public $content_en = '';

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    #[On('barber-shop-created')]
    public function refreshBarberShops($id)
    {
        $this->barber_shop_id = $id;
    }

    protected function getbarberShopsList()
    {
        $query = \App\Models\BarberShop::query();
        return $query->pluck('name', 'id')->toArray();
    }

    public function mount()
    {
        if (auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = (int) auth()->user()->barber_shop_id;
        }
    }

    public function render()
    {
        return view('livewire.admin.message-templates.quick-create', [
            'barberShops' => $this->getbarberShopsList(),
        ]);
    }

    public function store(CreateMessageTemplateAction $action)
    {
        if (empty($this->barber_shop_id) && auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = (int) auth()->user()->barber_shop_id;
        }

        $this->validate([
            'content_sq' => ['required', 'string', 'max:160'],
            'content_en' => ['nullable', 'string', 'max:160'],
        ]);

        $contentArr = [
            'sq' => (string) $this->content_sq,
            'en' => (string) $this->content_en,
        ];

        $dto = MessageTemplateDTO::fromArray([
            'barber_shop_id' => (int) $this->barber_shop_id,
            'channel' => $this->channel,
            'type' => $this->type,
            'content' => $contentArr,
        ]);

        $item = $action->execute($dto);
        $this->dispatch('message-template-created', id: $item->id);
        $this->js("Livewire.dispatch('message-template-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('message-templates.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->id ?? $item->id);
        $this->reset(['channel', 'type', 'content_sq', 'content_en']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }
}
