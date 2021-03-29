<?php

namespace App\Providers;

use App\Address;
use Laravel\Passport\Passport;
use App\Policies\AddressPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        // Address::class => AddressPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
        // Gate::define('create-stripePaymentMethod', function ($user, $stripePaymentMethod) {
        //     return $user->id === $post->user_id;
        // });

        Passport::routes();
    }
}
