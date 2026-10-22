<?php

namespace App\Livewire\Admin\BulkCancellations;

use App\Models\BulkCancellation;
use App\Models\Booking;
use App\Domain\BulkCancellation\DTOs\BulkCancellationDTO;
use App\Domain\BulkCancellation\Actions\CreateBulkCancellationAction;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Carbon\Carbon;

#[Title('Add BulkCancellation')]
class Create extends Component
{
    use WithPagination;

    public $barber_shop_id = '';
    public $barber_id = '';
    public $start_at = '';
    public $end_at = '';
    public $reason = '';
    public $affected_count = 0;

    #[On('barber-shop-created')]
    public function refreshBarberShops($id) { $this->barber_shop_id = $id; $this->updatedBarberShopId($id); }

    #[On('barber-created')]
    public function refreshBarbers($id) { $this->barber_id = $id; $this->updatedBarberId($id); }

    public function updatedBarberShopId($value)
    {
        $this->barber_id = '';
    }

    public function mount()
    {
        if (auth()->check() && auth()->user()->barber_shop_id) {
            $this->barber_shop_id = auth()->user()->barber_shop_id;
        }

        $this->start_at = Carbon::now()->format('Y-m-d\TH:i');
        $this->end_at = Carbon::now()->addHours(6)->format('Y-m-d\TH:i');
    }

    public function getAffectedBookingsProperty()
    {
        if (!$this->barber_id || !$this->start_at || !$this->end_at) {
            return collect();
        }

        try {
            $start = Carbon::parse($this->start_at);
            $end = Carbon::parse($this->end_at);
        } catch (\Throwable $e) {
            return collect();
        }

        $query = Booking::query()
            ->with(['customer', 'service', 'barber', 'barberShop'])
            ->where('barber_id', $this->barber_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('appointment_at', [$start, $end]);

        if ($this->barber_shop_id) {
            $query->where('barber_shop_id', $this->barber_shop_id);
        }

        return $query->orderBy('appointment_at', 'asc')->get();
    }

    protected function getbarberShopsList() {
        $query = \App\Models\BarberShop::query();
        if (method_exists(\App\Models\BarberShop::class, 'scopeForActiveShop')) { $query->forActiveShop(); }
        return $query->pluck('name', 'id')->toArray();
    }

    protected function getbarbersList() {
        $query = \App\Models\Barber::query();
        if ($this->barber_shop_id) {
            $query->where('barber_shop_id', $this->barber_shop_id);
        }
        return $query->pluck('name', 'id')->toArray();
    }

    public function store(CreateBulkCancellationAction $action)
    {
        $this->validate();

        $affectedCount = $this->affectedBookings->count();

        $dto = BulkCancellationDTO::fromArray([
            'barber_shop_id' => $this->barber_shop_id,
            'barber_id' => $this->barber_id,
            'start_at' => $this->start_at,
            'end_at' => $this->end_at,
            'reason' => $this->reason ?: 'Mungesë emergjente stafi',
            'affected_count' => $affectedCount,
        ]);

        $action->execute($dto);

        session()->flash('success', __('bulk-cancellations.created'));
        return to_route('admin.bulk-cancellations.index');
    }

    public function render()
    {
        abort_if_cannot('add_bulk_cancellations');

        return view('livewire.admin.bulk-cancellations.create', [
            'barberShops' => $this->getbarberShopsList(),
            'barbers' => $this->getbarbersList(),
            'affectedBookings' => $this->affectedBookings,
        ])->layout('components.layouts.app');
    }

    protected function rules(): array
    {
        return [
            'barber_shop_id' => ['required', 'integer'],
            'barber_id' => ['required', 'integer'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
