<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\DemoCatalogSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class PostgresConcurrencyTest extends TestCase
{
    // Committed fixtures must be visible to the independent connections below.
    use DatabaseMigrations;

    private function submitTogether(array $attempts, int $variantId): array
    {
        $db = config('database.connections.pgsql');
        $environment = [
            'APP_ENV' => 'testing', 'DB_CONNECTION' => 'pgsql', 'DB_URL' => '',
            'DB_HOST' => $db['host'], 'DB_PORT' => (string) $db['port'],
            'DB_DATABASE' => $db['database'], 'DB_USERNAME' => $db['username'],
            'DB_PASSWORD' => $db['password'] ?? '', 'DB_SSLMODE' => $db['sslmode'],
            'DB_SSLROOTCERT' => $db['sslrootcert'] ?? '',
        ];
        $gate = storage_path('framework/testing/checkout-'.Str::uuid().'.go');
        $processes = [];
        try {
            foreach ($attempts as [$user, $token]) {
                $process = new Process([PHP_BINARY, base_path('tests/Fixtures/checkout-worker.php'), (string) $user->id, (string) $variantId, $token, $gate], base_path(), $environment);
                $process->setTimeout(25);
                $process->start();
                $processes[] = $process;
            }
            foreach ($processes as $process) {
                $this->assertTrue($process->waitUntil(fn () => str_contains($process->getOutput(), 'READY')), $process->getErrorOutput());
            }
            touch($gate);
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
                $results[] = $process->getOutput();
            }

            return $results;
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop(1);
                }
            }
            if (is_file($gate)) {
                unlink($gate);
            }
        }
    }

    public function test_two_buyers_cannot_both_purchase_the_last_item(): void
    {
        $this->seed(DemoCatalogSeeder::class);
        $variant = Product::first()->variants()->first();
        $variant->update(['stock' => 1]);
        $results = $this->submitTogether([
            [User::factory()->create(), (string) Str::uuid()],
            [User::factory()->create(), (string) Str::uuid()],
        ], $variant->id);
        $this->assertSame(1, count(array_filter($results, fn ($r) => str_contains($r, 'CREATED:'))));
        $this->assertSame(1, count(array_filter($results, fn ($r) => str_contains($r, 'REJECTED'))));
        $this->assertSame(0, $variant->fresh()->stock);
        $this->assertSame(1, Order::count());
    }

    public function test_simultaneous_duplicate_submissions_create_one_order(): void
    {
        $this->seed(DemoCatalogSeeder::class);
        $variant = Product::first()->variants()->first();
        $variant->update(['stock' => 1]);
        $user = User::factory()->create();
        $token = (string) Str::uuid();
        $results = $this->submitTogether([[$user, $token], [$user, $token]], $variant->id);
        $this->assertSame(2, count(array_filter($results, fn ($r) => str_contains($r, 'CREATED:'))));
        $this->assertSame(1, Order::count());
        $this->assertSame(0, $variant->fresh()->stock);
    }
}
