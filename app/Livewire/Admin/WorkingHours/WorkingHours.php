<?php

namespace App\Livewire\Admin\WorkingHours;

use App\Models\WorkingHour;
use App\Models\Barber;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;

#[Title('Menaxhimi i Orareve')]
class WorkingHours extends Component
{
    #[Url(history: true)]
    public $barber_id = '';

    // Array to store form data for the 7 days of the week
    public array $schedule = [];

    public array $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function mount()
    {
        if (!$this->barber_id) {
            $this->barber_id = Barber::value('id') ?? '';
        }
        $this->loadSchedule();
    }

    public function updatedBarberId()
    {
        $this->loadSchedule();
    }

    public function loadSchedule()
    {
        if (!$this->barber_id) {
            $this->schedule = [];
            return;
        }

        $existing = WorkingHour::where('barber_id', $this->barber_id)->get()->keyBy('day_of_week');

        foreach ($this->daysOfWeek as $day) {
            if ($existing->has($day)) {
                $record = $existing->get($day);
                $this->schedule[$day] = [
                    'open_time' => $record->open_time ? substr($record->open_time, 0, 5) : '',
                    'close_time' => $record->close_time ? substr($record->close_time, 0, 5) : '',
                    'lunch_start' => $record->lunch_start ? substr($record->lunch_start, 0, 5) : '',
                    'lunch_end' => $record->lunch_end ? substr($record->lunch_end, 0, 5) : '',
                    'is_closed' => (bool)$record->is_closed,
                ];
            } else {
                $this->schedule[$day] = [
                    'open_time' => '08:00',
                    'close_time' => '20:00',
                    'lunch_start' => '',
                    'lunch_end' => '',
                    'is_closed' => true,
                ];
            }
        }
    }

    public function save()
    {
        abort_if_cannot('edit_working_hours');

        if (!$this->barber_id) {
            $this->dispatch('toast', message: 'Ju lutem zgjidhni një berber.', type: 'error');
            return;
        }

        foreach ($this->schedule as $day => $data) {
            $isClosed = (bool)$data['is_closed'];

            WorkingHour::updateOrCreate(
                [
                    'barber_id' => $this->barber_id,
                    'day_of_week' => $day
                ],
                [
                    'open_time' => $isClosed ? null : ($data['open_time'] ?: null),
                    'close_time' => $isClosed ? null : ($data['close_time'] ?: null),
                    'lunch_start' => $isClosed ? null : ($data['lunch_start'] ?: null),
                    'lunch_end' => $isClosed ? null : ($data['lunch_end'] ?: null),
                    'is_closed' => $isClosed
                ]
            );
        }

        $this->dispatch('toast', message: 'Orari javor u ruajt me sukses!', type: 'success');
        $this->loadSchedule();
    }

    public function render()
    {
        abort_if_cannot('view_working_hours');

        return view('livewire.admin.working-hours.index', [
            'barbers' => Barber::pluck('name', 'id')->toArray(),
            'daysOfWeek' => $this->daysOfWeek,
        ])->layout('components.layouts.app');
    }
}
