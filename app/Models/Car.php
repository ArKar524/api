<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\QueryFilters\Filterable;

class Car extends Model
{
    use Filterable;
    protected $guarded = [];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CarPhoto::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(CarDocument::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function tripApplications(): HasMany
    {
        return $this->hasMany(TripApplication::class);
    }

    public function carInfos(){
       return $this->hasMany(CarInfo::class);
    }
}
