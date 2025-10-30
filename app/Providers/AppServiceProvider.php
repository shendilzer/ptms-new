<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Rules\Password::defaults(function(){
            return Rules\Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols();
        });

        Inertia::share([
            'auth' => function () {
                return [
                    'user' => auth()->check() ? [
                        'id' => auth()->user()->id,
                        'name' => auth()->user()->name,
                        'role' => auth()->user()->role->value, // Pass the user's role
                    ] : null,
                ];
            },
        ]);

    }
}
