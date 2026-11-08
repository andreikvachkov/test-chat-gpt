<?php

namespace App\Http\Middleware;

use App\Services\Cart;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return [...parent::share($request),
            'auth' => ['user' => fn () => $request->user()?->only('id', 'name', 'email', 'phone', 'last_name', 'middle_name', 'birthdate')],
            'cart' => fn () => app(Cart::class)->summary(),
            'favourites' => fn () => $request->user()?->favourites()->pluck('products.id') ?? [],
            'flash' => ['status' => fn () => $request->session()->get('status')],
        ];
    }
}
