<?php

namespace App\Livewire\Admin\MessageTemplates;

use App\Models\MessageTemplate;
use App\Domain\MessageTemplate\DTOs\MessageTemplateDTO;
use App\Domain\MessageTemplate\Actions\CreateMessageTemplateAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;

#[Title('Add MessageTemplate')]
class Create extends Component
{
        use WithPagination;
     public $barber_shop_id = '';
    public $channel = '';
    public $type = '';
    public $content = '';
 
    #[On('barber-shop-created')] 
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }
 
    public function updatedBarberShopId($value)
    {
        if (!$value) return;
        $related = \App\Models\BarberShop::find($value);
        if (!$related) return;
    }
 
    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }
 
    public function mount() { if (auth()->check() && auth()->user()->barber_shop_id) { $this->barber_shop_id = auth()->user()->barber_shop_id; } }

    public function render() { abort_if_cannot('add_message_templates'); return view('livewire.admin.message-templates.create', [
            'barberShops' => $this->getbarberShopsList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateMessageTemplateAction $action) { $this->validate();  $dto = MessageTemplateDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'channel' => $this->channel,
            'type' => $this->type,
            'content' => $this->content,
        ]); $action->execute($dto); session()->flash('success', __('message-templates.created')); return to_route('admin.message-templates.index'); }
    protected function rules(): array { $rules = MessageTemplate::rules();
        return $rules; }
}