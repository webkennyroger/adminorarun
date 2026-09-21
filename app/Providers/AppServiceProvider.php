<?php

namespace App\Providers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load helpers
        if (file_exists(app_path('Helpers/helpers.php'))) {
            require_once app_path('Helpers/helpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        App::setLocale('pt_BR');
        Carbon::setLocale('pt_BR');
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        Gate::define('access-admin-panel', function (User $user) {
            return $user->isAdmin() || $user->isManager();
        });

        Gate::define('access-goals', function (User $user) {
            return $user->isAdmin() || ! $user->isManager();
        });

        Gate::define('access-subscriptions', function (User $user) {
            return $user->isAdmin() || ! $user->isManager();
        });

        Gate::define('manage-subscriptions', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-roles', function (User $user) {
            return $user->isSuperAdmin();
        });

        Gate::define('manage-everything', function (User $user) {
            return $user->isSuperAdmin();
        });
    }
}
