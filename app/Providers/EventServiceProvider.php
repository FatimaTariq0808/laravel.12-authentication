<?php

namespace App\Providers;
use App\Events\UserRegistered;
use App\Events\UserRequestedPassword;
use App\Listeners\SendWelcomeEmailListener;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    protected $listen = [
        UserRegistered::class => [
            SendWelcomeEmailListener::class,
        ],
        UserRequestedPassword::class => [
            SendWelcomeEmailListener::class,
        ],
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
        //
    }
}
