<?php

namespace App\Providers;

use App\Contracts\DeliveryProvider;
use App\Contracts\PaymentGateway;
use App\Contracts\SmsSender;
use App\Models;
use App\Policies;
use App\Services\UnavailableIntegrations;
use Illuminate\Support\Facades\Gate;
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
        foreach ([
            Models\Product::class => Policies\ProductPolicy::class,
            Models\ProductReview::class => Policies\ProductReviewPolicy::class,
            Models\Category::class => Policies\CategoryPolicy::class,
            Models\ProductAttribute::class => Policies\ProductAttributePolicy::class,
            Models\ProductCollection::class => Policies\ProductCollectionPolicy::class,
            Models\Order::class => Policies\OrderPolicy::class,
            Models\User::class => Policies\UserPolicy::class,
            Models\Page::class => Policies\PagePolicy::class,
            Models\ContentEntry::class => Policies\ContentEntryPolicy::class,
            Models\MediaAsset::class => Policies\MediaAssetPolicy::class,
            Models\AdminUser::class => Policies\AdminUserPolicy::class,
            Models\AdminRole::class => Policies\AdminRolePolicy::class,
        ] as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
