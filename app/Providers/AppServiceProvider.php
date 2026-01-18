<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Database\Eloquent\Model;

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
        // Prevent lazy loading in development
        Model::preventLazyLoading(!$this->app->isProduction());

        // Custom Blade directives
        Blade::directive('money', function ($expression) {
            return "<?php echo number_format($expression, 2); ?>";
        });

        Blade::directive('points', function ($expression) {
            return "<?php echo number_format($expression, 0); ?>";
        });

        // Share common data with all views
        view()->composer('*', function ($view) {
            $view->with('appName', config('app.name'));
        });
    }
}
