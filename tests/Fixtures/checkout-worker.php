<?php

use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Checkout;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Separate process for real PostgreSQL lock tests; never run against the application database.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (DB::getDriverName() !== 'pgsql' || ! preg_match('/(?:^|_)test(?:_|$)/', DB::connection()->getDatabaseName())) {
    throw new RuntimeException('A dedicated PostgreSQL test database is required.');
}
[$script, $userId, $variantId, $token, $gate] = $argv;
$user = User::findOrFail($userId);
$variant = ProductVariant::findOrFail($variantId);
echo "READY\n";
flush();
$deadline = microtime(true) + 15;
while (! is_file($gate)) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Checkout test barrier timed out.');
    }
    usleep(10000);
    clearstatcache(true, $gate);
}
try {
    $order = app(Checkout::class)->place($user, [
        'checkout_token' => $token,
        'name' => $user->name,
        'email' => $user->email,
        'phone' => '+79990000000',
        'address' => 'Тестовый адрес',
        'delivery_method' => 'cdek',
        'expected_subtotal_minor' => $variant->price_minor,
    ], [(int) $variantId => 1]);
    echo 'CREATED:'.$order->number."\n";
} catch (ValidationException) {
    echo "REJECTED\n";
}
