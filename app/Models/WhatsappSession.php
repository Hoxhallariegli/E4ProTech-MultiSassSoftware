<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;

    protected $fillable = ['session_key', 'session_data'];

    protected function casts(): array
    {
        return [
            'session_data' => 'array',
        ];
    }
}
