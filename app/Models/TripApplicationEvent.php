<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripApplicationEvent extends Model
{
    protected $guarded = [];

    public function tripApplication(): BelongsTo
    {
        return $this->belongsTo(TripApplication::class, 'trip_application_id');
    }
}
