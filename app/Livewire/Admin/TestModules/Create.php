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

#[Title('Add TestModule')]
class Create extends Component
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

    public function render() { abort_if_cannot('add_test_modules'); return view('livewire.admin.test-modules.create', [
            'users' => $this->getusersList(),
        ])->layout('components.layouts.app'); }
    public function store(CreateTestModuleAction $action) { $this->validate();         if ($this->image && !is_string($this->image)) { $this->image = app(\App\Services\ImageUploadService::class)->upload($this->image, 'uploads/test-modules'); }
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
        ]); $action->execute($dto); session()->flash('success', __('test-modules.created')); return to_route('admin.test-modules.index'); }
    protected function rules(): array { $rules = TestModule::rules();
        if ($this->image instanceof \Illuminate\Http\UploadedFile) { $rules['image'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->image) && $this->image !== '') { $rules['image'] = ['nullable', 'string', 'max:255']; } else { $rules['image'] = ['nullable']; }
        if ($this->cover_photo instanceof \Illuminate\Http\UploadedFile) { $rules['cover_photo'] = ['nullable', 'image', 'max:10240']; } elseif (is_string($this->cover_photo) && $this->cover_photo !== '') { $rules['cover_photo'] = ['nullable', 'string', 'max:255']; } else { $rules['cover_photo'] = ['nullable']; }
        return $rules; }
}