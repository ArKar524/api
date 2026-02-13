<?php

namespace App\Models;

use App\Support\QueryFilters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TripApplication extends Model
{
    use Filterable;

    protected $guarded = [];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(TripApplicationEvent::class, 'trip_application_id');
    }

    public function locationUpdates(): HasMany
    {
        return $this->hasMany(LocationUpdate::class, 'trip_application_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'trip_application_id');
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class, 'trip_application_id');
    }
}
