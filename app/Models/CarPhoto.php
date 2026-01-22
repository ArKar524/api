<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CarPhoto extends Model
{
    protected $guarded = [];

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }
}
