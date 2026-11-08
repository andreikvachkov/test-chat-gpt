<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\PageResource;
use App\Filament\Resources\Pages\ManageArticle;
use App\Filament\Resources\Pages\ManageProductReview;
use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\ContentEntry;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([DatabaseSeeder::class, DemoCatalogSeeder::class]);
    }

    public function test_owner_navigation_has_ordered_fixed_sections_and_nested_products(): void
    {
        $this->actingAs(AdminUser::factory()->create(), 'admin');
        $groups = array_values(Filament::getPanel('admin')->getNavigation());
        $labels = fn ($items) => array_map(fn ($item) => $item->getLabel(), $items);
        $this->assertSame(['Инфопанель', 'Статьи', 'Медиафайлы', 'Страницы'], $labels($groups[0]->getItems()));
        $this->assertSame('Каталог', $groups[1]->getLabel());
        $this->assertFalse($groups[1]->isCollapsible());
        $this->assertSame(['Товары', 'Заказы', 'Клиенты'], $labels($groups[1]->getItems()));
        $products = $groups[1]->getItems()[0];
        $this->assertNull($products->getUrl());
        $this->assertSame(['Все товары', 'Категории', 'Подборки', 'Атрибуты', 'Отзывы'], $labels($products->getChildItems()));
        $this->assertFalse($groups[2]->isCollapsible());
        $this->get('/admin')->assertOk()->assertDontSee('fi-sidebar-group-collapse-btn', false)->assertDontSee('data-group-label=', false);
    }

    public function test_articles_are_separate_from_pages_and_cannot_change_template_or_bypass_permissions(): void
    {
        $role = AdminRole::create(['name' => 'Только статьи', 'permissions' => ['articles' => ['view', 'create', 'update']]]);
        $staff = AdminUser::factory()->create(['admin_role_id' => $role->id]);
        $this->actingAs($staff, 'admin');
        $article = Page::where('template', 'article')->firstOrFail();
        $page = Page::where('template', 'about')->firstOrFail();
        $this->get('/admin/articles')->assertOk();
        $this->get('/admin/pages')->assertForbidden();
        $this->assertTrue(Gate::forUser($staff)->allows('update', $article));
        $this->assertFalse(Gate::forUser($staff)->allows('update', $page));
        $this->assertFalse(ArticleResource::getEloquentQuery()->whereKey($page->id)->exists());
        $this->assertFalse(PageResource::getEloquentQuery()->whereKey($article->id)->exists());
        Livewire::test(ManageArticle::class)->callAction('create', data: ['title' => 'Отдельная статья', 'slug' => 'separate-article', 'template' => 'index', 'published' => true, 'content' => []])->assertHasNoActionErrors();
        $this->assertDatabaseHas('pages', ['slug' => 'separate-article', 'template' => 'article']);
    }

    public function test_reviews_can_be_moderated_but_read_only_staff_cannot_change_them(): void
    {
        $review = ProductReview::create(['product_id' => Product::first()->id, 'user_id' => User::factory()->create()->id, 'rating' => 5, 'body' => 'Текст клиента', 'status' => 'pending']);
        $manager = AdminUser::factory()->create(['admin_role_id' => AdminRole::where('code', 'catalog_manager')->value('id')]);
        $this->actingAs($manager, 'admin');
        $this->get('/admin/product-reviews')->assertOk();
        Livewire::test(ManageProductReview::class)->callAction(TestAction::make('edit')->table($review), data: ['status' => 'approved'])->assertHasNoActionErrors();
        $this->assertSame('approved', $review->fresh()->status);
        $this->assertSame('Текст клиента', $review->fresh()->body);
        $reader = AdminRole::create(['name' => 'Просмотр отзывов', 'permissions' => ['reviews' => ['view']]]);
        $manager->update(['admin_role_id' => $reader->id]);
        $this->actingAs($manager->fresh(), 'admin');
        Livewire::test(ManageProductReview::class)->assertActionHidden(TestAction::make('edit')->table($review))->assertActionVisible(TestAction::make('view')->table($review));
        $this->assertFalse(Gate::forUser($manager->fresh())->allows('update', $review));
    }

    public function test_section_migration_preserves_custom_role_limits_and_existing_content(): void
    {
        $role = AdminRole::create(['name' => 'Существующая роль', 'permissions' => []]);
        DB::table('admin_roles')->where('id', $role->id)->update(['permissions' => json_encode(['pages' => ['view', 'update'], 'products' => ['view'], 'content' => ['view']])]);
        $content = ContentEntry::first();
        $value = $content->value;
        Schema::drop('product_reviews');
        $migration = require database_path('migrations/2026_11_08_000003_reorganize_admin_sections.php');
        $migration->up();
        $permissions = $role->fresh()->permissions;
        $this->assertSame(['view', 'update'], $permissions['articles']);
        $this->assertSame(['view'], $permissions['reviews']);
        $this->assertArrayNotHasKey('content', $permissions);
        $this->assertSame($value, $content->fresh()->value);
        $this->assertSame('Существующая роль', $role->fresh()->name);
    }
}
