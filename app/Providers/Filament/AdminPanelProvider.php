<?php

namespace App\Providers\Filament;

use App\Filament\Resources;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
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
        return $panel->default()->id('admin')->path('admin')->login()->brandName('Kamali')->pages([Dashboard::class])->colors(['primary' => Color::Rose])->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->navigation(fn (NavigationBuilder $builder) => $this->shopNavigation($builder))
            ->middleware([
                EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class, AuthenticateSession::class, ShareErrorsFromSession::class, VerifyCsrfToken::class, SubstituteBindings::class, DisableBladeIconComponents::class, DispatchServingFilamentEvent::class,
            ])->authMiddleware([Authenticate::class]);
    }

    private function shopNavigation(NavigationBuilder $builder): NavigationBuilder
    {
        $builder->items(Dashboard::getNavigationItems());

        foreach ([
            'Каталог' => [Resources\ProductResource::class, Resources\CategoryResource::class, Resources\ProductAttributeResource::class, Resources\ProductCollectionResource::class],
            'Продажи' => [Resources\OrderResource::class, Resources\UserResource::class],
            'Контент' => [Resources\PageResource::class, Resources\ContentEntryResource::class, Resources\MediaAssetResource::class],
        ] as $label => $resources) {
            $items = [];
            foreach ($resources as $resource) {
                if ($resource::canAccess()) {
                    array_push($items, ...$resource::getNavigationItems());
                }
            }
            $builder->group(NavigationGroup::make($label)->items($items));
        }

        return $builder;
    }
}
