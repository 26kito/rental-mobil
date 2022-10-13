<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Car;
use App\Observers\CarObserver;
use Laravel\Passport\Passport;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(\L5Swagger\L5SwaggerServiceProvider::class);
        Passport::ignoreMigrations();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Car::observe(CarObserver::class);
        config(['app.locale' => 'id']);
        Carbon::setLocale('id');
    }
}
