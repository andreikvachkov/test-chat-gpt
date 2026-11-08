<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Resources;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel->default()->id('admin')->path('admin')->authGuard('admin')->authPasswordBroker('admins')->login(Login::class)->brandName('Kamali')->pages([Dashboard::class])->colors(['primary' => Color::Rose])->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->navigation(fn (NavigationBuilder $builder) => $this->shopNavigation($builder))
            ->renderHook(PanelsRenderHook::HEAD_END, fn () => view('filament.admin-navigation'))
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class, AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class, SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])->authMiddleware([Authenticate::class], isPersistent: true);
    }

    private function shopNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        $builder->items([...Dashboard::getNavigationItems(), ...$this->resourceItems([
            Resources\ArticleResource::class, Resources\MediaAssetResource::class, Resources\PageResource::class,
        ])]);

        $products = $this->resourceItems([
            Resources\ProductResource::class, Resources\CategoryResource::class, Resources\ProductCollectionResource::class,
            Resources\ProductAttributeResource::class, Resources\ProductReviewResource::class,
        ]);
        $catalogue = $products ? [NavigationItem::make('Товары')->icon('heroicon-o-shopping-bag')->extraAttributes(['class' => 'kamali-products-navigation'])->childItems($products)] : [];
        array_push($catalogue, ...$this->resourceItems([Resources\OrderResource::class, Resources\UserResource::class]));
        if ($catalogue) {
            $builder->group(NavigationGroup::make('Каталог')->collapsible(false)->items($catalogue));
        }
        $administration = $this->resourceItems([Resources\AdminUserResource::class, Resources\AdminRoleResource::class]);
        if ($administration) {
            $builder->group(NavigationGroup::make('Администрирование')->collapsible(false)->items($administration));
        }

        return $builder;
    }

    private function resourceItems(array $resources): array
    {
        $items = [];
        foreach ($resources as $resource) {
            if ($resource::canAccess()) {
                array_push($items, ...$resource::getNavigationItems());
            }
        }

        return $items;
    }
}
