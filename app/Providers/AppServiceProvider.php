<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;

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
        Model::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });

        Gate::define('is-admin',   fn($user) => $user->role === 'admin');
        Gate::define('is-borrower', fn($user) => $user->role === 'borrower');

        // Gate untuk aksi spesifik
        Gate::define('review-loan', fn($user) => in_array($user->role, ['admin']));
        Gate::define('approve-loan', fn($user) => $user->role === 'admin');
    }
}
