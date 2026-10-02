<?php

namespace App\Livewire\Admin\MessageTemplates;

use App\Models\MessageTemplate;
use App\Domain\MessageTemplate\DTOs\MessageTemplateDTO;
use App\Domain\MessageTemplate\Actions\UpdateMessageTemplateAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;

#[Title('Edit MessageTemplate')]
class Edit extends Component
{
    use WithPagination;

    public MessageTemplate $item;
    public $barber_shop_id = '';
    public $channel = '';
    public $type = '';
    public $content_sq = '';
    public $content_en = '';

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

    public function mount(MessageTemplate $messageTemplate)
    {
        $this->item = $messageTemplate;
        $this->barber_shop_id = $messageTemplate->barber_shop_id;
        $this->channel = $messageTemplate->channel;
        $this->type = $messageTemplate->type;

        $rawContent = $messageTemplate->content;
        if (is_array($rawContent)) {
            $this->content_sq = $rawContent['sq'] ?? '';
            $this->content_en = $rawContent['en'] ?? '';
        } elseif (is_string($rawContent)) {
            $decoded = json_decode($rawContent, true);
            if (is_array($decoded)) {
                $this->content_sq = $decoded['sq'] ?? '';
                $this->content_en = $decoded['en'] ?? '';
            } else {
                $this->content_sq = $rawContent;
                $this->content_en = $rawContent;
            }
        }

        if (empty($this->barber_shop_id) && auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = (int) auth()->user()->barber_shop_id;
        }
    }

    public function render()
    {
        abort_if_cannot('edit_message_templates');
        return view('livewire.admin.message-templates.edit', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app');
    }

    public function update(UpdateMessageTemplateAction $action)
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

        $action->execute($this->item, $dto);
        session()->flash('success', __('message-templates.updated'));
        return to_route('admin.message-templates.index');
    }
}
