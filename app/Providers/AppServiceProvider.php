<?php

namespace App\Providers;

use App\Contracts\DeliveryProvider;
use App\Contracts\PaymentGateway;
use App\Contracts\SmsSender;
use App\Services\UnavailableIntegrations;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ([PaymentGateway::class, SmsSender::class, DeliveryProvider::class] as $contract) {
            $this->app->bind($contract, UnavailableIntegrations::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
