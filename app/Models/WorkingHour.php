<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    use HasFactory;
    protected $fillable = ['barber_id', 'day_of_week', 'open_time', 'close_time', 'lunch_start', 'lunch_end', 'is_closed'];
    protected function casts(): array { return [
            'is_closed' => 'boolean',
        ]; }
    public static function rules($id = null): array { return [
            'barber_id' => ['required', 'integer'],
            'day_of_week' => ['required', \Illuminate\Validation\Rule::in(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])],
            'open_time' => ['required_if:is_closed,false,0', 'nullable', 'string'],
            'close_time' => ['required_if:is_closed,false,0', 'nullable', 'string'],
            'lunch_start' => ['nullable', 'string'],
            'lunch_end' => ['nullable', 'string'],
            'is_closed' => ['boolean'],
        ]; }
    public static function sortable(): array { return ['id', 'barber_id', 'day_of_week', 'open_time', 'close_time', 'lunch_start', 'lunch_end', 'is_closed']; }

    protected static function booted(): void
    {
        static::observe(\App\Observers\WorkingHourObserver::class);
    }

    public function barber(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return $this->belongsTo(\App\Models\Barber::class, 'barber_id'); }

}
