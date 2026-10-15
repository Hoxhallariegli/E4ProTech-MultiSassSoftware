<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'barber_shop_id',
        'user_id',
        'user_prompt',
        'status',
        'error_message',
        'results',
    ];

    protected $casts = [
        'results' => 'array',
    ];

    public function barberShop(): BelongsTo
    {
        return $this->belongsTo(BarberShop::class, 'barber_shop_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
