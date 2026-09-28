<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasUuid;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasUuid;

    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $casts = [
        'id' => 'string',
    ];

    public function barberShop(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BarberShop::class, 'barber_shop_id');
    }
}
