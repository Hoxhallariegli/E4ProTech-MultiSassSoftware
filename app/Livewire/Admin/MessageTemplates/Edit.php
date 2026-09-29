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
    public $content = '';

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
        $this->fill($messageTemplate->toArray());

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

        $this->validate();

        $dto = MessageTemplateDTO::fromArray([
            'barber_shop_id' => (int) $this->barber_shop_id,
            'channel' => $this->channel,
            'type' => $this->type,
            'content' => $this->content,
        ]);

        $action->execute($this->item, $dto);
        session()->flash('success', __('message-templates.updated'));
        return to_route('admin.message-templates.index');
    }

    protected function rules(): array
    {
        return MessageTemplate::rules($this->item->id);
    }
}
