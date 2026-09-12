<?php

namespace App\Models\Sanctum;

use App\Models\Traits\HasUuid;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;
}
