<?php

namespace App\Models;

use App\Models\Car;
use App\Models\Rental;
use App\Models\RentalRequest;
use App\Models\Verification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function rentalRequestsAsDriver(): HasMany
    {
        return $this->hasMany(RentalRequest::class, 'driver_id');
    }

    public function rentalRequestsAsOwner(): HasMany
    {
        return $this->hasMany(RentalRequest::class, 'owner_id');
    }

    public function rentalsAsDriver(): HasMany
    {
        return $this->hasMany(Rental::class, 'driver_id');
    }

    public function rentalsAsOwner(): HasMany
    {
        return $this->hasMany(Rental::class, 'owner_id');
    }
}
