<?php

namespace App\Models;

use App\Models\Car;
use App\Models\Trip;
use App\Models\TripApplication;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function cars(): HasMany
    {
        return $this->hasMany(Car::class, 'owner_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class);
    }

    public function ownerVerifications(): HasMany
    {
        return $this->verifications()->where('entity_type', 'owner');
    }

    public function driverVerifications(): HasMany
    {
        return $this->verifications()->where('entity_type', 'driver');
    }

    public function latestOwnerVerification(): HasOne
    {
        return $this->hasOne(Verification::class)
            ->where('entity_type', 'owner')
            ->latestOfMany();
    }

    public function latestDriverVerification(): HasOne
    {
        return $this->hasOne(Verification::class)
            ->where('entity_type', 'driver')
            ->latestOfMany();
    }

    public function tripsAsDriver(): HasMany
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function tripsAsOwner(): HasMany
    {
        return $this->hasMany(Trip::class, 'owner_id');
    }

    public function tripApplicationsAsDriver(): HasMany
    {
        return $this->hasMany(TripApplication::class, 'driver_id');
    }

    public function tripApplicationsAsOwner(): HasMany
    {
        return $this->hasMany(TripApplication::class, 'owner_id');
    }
}
