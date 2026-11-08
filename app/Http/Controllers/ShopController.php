<?php

namespace App\Http\Controllers;

use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\ContentEntry;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Cart;
use App\Services\Checkout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class ShopController extends Controller
{
    public function render(string $template, array $props = [], ?Page $page = null)
    {
        $page ??= Page::where('template', $template)->where('published', true)->firstOrFail();

        return Inertia::render('Store', [...$props, 'template' => $template, 'meta' => $props['meta'] ?? ['title' => $page->title, 'description' => $page->seo_description],
            'content' => ContentEntry::whereIn('group', ['shared', $template])->pluck('value', 'key')->merge($page->content ?? [])]);
    }

    public function page(string $slug = 'index')
    {
        $page = Page::where('slug', $slug)->where('published', true)->firstOrFail();
        abort_unless(in_array($page->template, ['index', 'about', 'history', 'contact', 'blog', 'article', 'document', 'document2', 'document-gift-card', 'gift', '404']), 404);

        return $this->render($page->template, [], $page);
    }

    public function catalog(Request $r)
    {
        $d = $r->validate(['category' => 'nullable|string|max:100', 'collection' => 'nullable|string|max:100', 'q' => 'nullable|string|max:100', 'sort' => ['nullable', Rule::in(['popular', 'price_asc', 'price_desc', 'stock'])]]);
        $query = Product::with(['collections', 'variants' => fn ($q) => $q->where('active', true)])->visible()
            ->when($d['category'] ?? null, fn ($q, $v) => $q->whereHas('categories', fn ($c) => $c->where('slug', $v)))
            ->when($d['collection'] ?? null, fn ($q, $v) => $q->whereHas('collections', fn ($c) => $c->where('slug', $v)))
            ->when($d['q'] ?? null, fn ($q, $v) => $q->where(fn ($n) => $n->whereLike('name', '%'.$v.'%')->orWhereLike('short_description', '%'.$v.'%')->orWhereLike('description', '%'.$v.'%')->orWhereLike('article', '%'.$v.'%')));
        if (($d['sort'] ?? '') === 'stock') {
            $query->whereHas('variants', fn ($q) => $q->where('active', true)->whereRaw("CASE
                WHEN products.type = 'simple' THEN CASE
                    WHEN COALESCE((products.details->>'manage_stock')::boolean, true) THEN (product_variants.stock > 0 OR COALESCE(products.details->>'backorders', 'no') != 'no')
                    ELSE COALESCE(products.details->>'stock_status', 'instock') != 'outofstock' END
                WHEN COALESCE((product_variants.details->>'manage_stock')::boolean, true) THEN (product_variants.stock > 0 OR COALESCE(product_variants.details->>'backorders', 'no') != 'no')
                WHEN COALESCE((products.details->>'manage_stock')::boolean, false) THEN (products.inventory_stock > 0 OR COALESCE(products.details->>'backorders', 'no') != 'no')
                ELSE COALESCE(product_variants.details->>'stock_status', 'instock') != 'outofstock' END"));
        }
        $sort = $d['sort'] ?? 'popular';
        if (in_array($sort, ['price_asc', 'price_desc'])) {
            $query->orderBy(ProductVariant::selectRaw('MIN(COALESCE(sale_price_minor, price_minor))')->whereColumn('product_id', 'products.id')->whereNull('retired_at')->where('active', true), $sort === 'price_desc' ? 'desc' : 'asc');
        } else {
            $query->orderByRaw("COALESCE((details->>'menu_order')::integer, 0)")->orderBy('id');
        }

        return $this->render('catalog', ['products' => $query->paginate(12)->withQueryString(), 'categories' => Category::orderBy('id')->get(), 'filters' => $d]);
    }

    public function product(string $slug)
    {
        $product = Product::with(['collections', 'variants' => fn ($q) => $q->where('active', true)])->where('slug', $slug)->visible()->firstOrFail();
        $values = AttributeValue::with('attribute')->whereIn('id', collect($product->attribute_config ?? [])->pluck('value_ids')->flatten())->get()->keyBy('id');
        $attributes = collect($product->attribute_config ?? [])->map(fn ($row) => ['id' => $row['attribute_id'], 'name' => $values->first(fn ($v) => $v->product_attribute_id === (int) $row['attribute_id'])?->attribute->name, 'visible' => $row['visible'] ?? true, 'for_variations' => $row['for_variations'] ?? false,
            'values' => collect($row['value_ids'])->map(fn ($id) => $values->get($id))->filter()->map(fn ($v) => ['id' => $v->id, 'name' => $v->name])->values()]);

        return $this->render('product-page', ['product' => $product, 'attributes' => $attributes->values(), 'meta' => ['title' => $product->name, 'description' => Str::limit($product->short_description ?? strip_tags($product->description ?? ''), 160)]]);
    }

    public function cart(Request $r, Cart $cart)
    {
        $d = $r->validate(['variant_id' => 'required|integer', 'quantity' => 'required|integer|min:0|max:99']);
        $cart->set($d['variant_id'], $d['quantity']);

        return back();
    }

    public function checkout(Request $r)
    {
        if (! $r->session()->has('checkout_token') || $r->user()->orders()->where('checkout_token', $r->session()->get('checkout_token'))->exists()) {
            $r->session()->put('checkout_token', (string) Str::uuid());
        }

        return $this->render('order', ['checkoutToken' => $r->session()->get('checkout_token')]);
    }

    public function place(Request $r, Cart $cart, Checkout $checkout)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'email' => 'required|email|max:255', 'phone' => 'required|string|max:30', 'address' => 'required|string|max:1000', 'comment' => 'nullable|string|max:2000', 'delivery_method' => ['required', Rule::in(['cdek', 'courier'])], 'consent' => 'accepted', 'checkout_token' => 'required|uuid', 'expected_subtotal_minor' => 'required|integer|min:0']);
        abort_unless($d['checkout_token'] === $r->session()->get('checkout_token'), 419);
        $order = $checkout->place($r->user(), $d, $cart->quantities());
        $r->session()->forget('cart');

        return redirect('/account/orders/'.$order->number);
    }

    public function account(Request $r)
    {
        return $this->render('lk-personal-info', ['orders' => $r->user()->orders()->with('items')->latest()->get()]);
    }

    public function order(Request $r, string $number)
    {
        return $this->render('lk-order-page', ['order' => $r->user()->orders()->with('items')->where('number', $number)->firstOrFail()]);
    }

    public function profile(Request $r)
    {
        $d = $r->validate(['name' => 'required|string|max:120', 'phone' => 'nullable|string|max:30', 'last_name' => 'nullable|string|max:80', 'middle_name' => 'nullable|string|max:80', 'birthdate' => 'nullable|date_format:Y-m-d|before_or_equal:today']);
        $r->user()->update($d);

        return back()->with('status', 'Данные сохранены.');
    }

    public function password(Request $r)
    {
        $d = $r->validate(['current_password' => 'required|current_password', 'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()]]);
        $r->user()->update(['password' => $d['password']]);

        return back()->with('status', 'Пароль изменён.');
    }

    public function favourites(Request $r)
    {
        return $this->render('catalog', ['products' => ['data' => $r->user()->favourites()->with(['collections', 'variants' => fn ($q) => $q->where('active', true)])->visible()->get()], 'categories' => [], 'filters' => [], 'favouritesPage' => true]);
    }

    public function favourite(Request $r, Product $product)
    {
        abort_unless($product->isVisible(), 404);
        $r->user()->favourites()->toggle($product->id);

        return back();
    }
}
