<?php

namespace App\Providers;

use App\Models\Car;
use App\Models\Verification;
use App\Policies\CarPolicy;
use App\Policies\VerificationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Verification::class => VerificationPolicy::class,
        Car::class => CarPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
