<?php

namespace App\Livewire\Admin\TestModules;

use App\Models\TestModule;
use App\Domain\TestModule\DTOs\TestModuleDTO;
use App\Domain\TestModule\Actions\CreateTestModuleAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class QuickCreate extends Component
{
        use WithPagination, WithFileUploads;
     public $name = '';
    public $description = '';
    public $qty = '';
    public $price = '';
    public $is_active = '';
    public $due_date = '';
    public $event_at = '';
    public $user_id = '';
    public $priority = '';
    public $image = '';
    public $cover_photo = '';
 
    #[On('user-created')] 
    public function refreshUsers($id) { $this->user_id = $id; $this->updatedUserId($id); }
 
    public function updatedUserId($value)
    {
        if (!$value) return;
        $related = \App\Models\User::find($value);
        if (!$related) return;
    }
 
    protected function getusersList() {
        return \App\Models\User::pluck('name', 'id')->toArray();
    }

    public bool $created = false;
    public ?int $createdId = null;
    public string $createdLabel = '';

    public function render() { return view('livewire.admin.test-modules.quick-create', [
            'users' => $this->getusersList(),
        ]); }

    public function store(CreateTestModuleAction $action)
    {
        $this->validate();
        if ($this->image && !is_string($this->image)) { $this->image = app(\App\Services\ImageUploadService::class)->upload($this->image, 'uploads/test-modules'); }
        if ($this->cover_photo && !is_string($this->cover_photo)) { $this->cover_photo = app(\App\Services\ImageUploadService::class)->upload($this->cover_photo, 'uploads/test-modules'); }
        $dto = TestModuleDTO::fromArray([
            'name' => $this->name,
            'description' => $this->description,
            'qty' => $this->qty,
            'price' => $this->price,
            'is_active' => $this->is_active,
            'due_date' => $this->due_date,
            'event_at' => $this->event_at,
            'user_id' => $this->user_id,
            'priority' => $this->priority,
            'image' => $this->image,
            'cover_photo' => $this->cover_photo,
        ]);
        $item = $action->execute($dto);
        $this->dispatch('test-module-created', id: $item->id);
        $this->js("Livewire.dispatch('test-module-created', { id: {$item->id} })");
        $this->dispatch('toast', message: __('test-modules.created'), type: 'success');
        $this->created = true;
        $this->createdId = $item->id;
        $this->createdLabel = (string) ($item->name ?? $item->id);
        $this->reset(['name', 'description', 'qty', 'price', 'is_active', 'due_date', 'event_at', 'user_id', 'priority', 'image', 'cover_photo']);
    }

    public function addAnother()
    {
        $this->created = false;
        $this->createdId = null;
        $this->createdLabel = '';
    }

    protected function rules(): array { $rules = TestModule::rules();
        if ($this->image instanceof \Illuminate\Http\UploadedFile) { $rules['image'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->image) && $this->image !== '') { $rules['image'] = ['nullable', 'string', 'max:255']; } else { $rules['image'] = ['nullable']; }
        if ($this->cover_photo instanceof \Illuminate\Http\UploadedFile) { $rules['cover_photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->cover_photo) && $this->cover_photo !== '') { $rules['cover_photo'] = ['nullable', 'string', 'max:255']; } else { $rules['cover_photo'] = ['nullable']; }
        return $rules; }
}