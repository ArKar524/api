<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalEvent extends Model
{
    protected $guarded = [];

    public function rental(): BelongsTo
    {
        return $this->belongsTo(Rental::class);
    }
}
