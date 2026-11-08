<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
foreach (['storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/framework/testing', 'bootstrap/cache'] as $directory) {
    if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
        throw new RuntimeException('Cannot create '.$directory);
    }
}
if (is_file(public_path('hot'))) {
    unlink(public_path('hot'));
}
$link = public_path('storage');
if (is_link($link) && readlink($link) !== '../storage/app/public') {
    unlink($link);
}
if (! is_link($link)) {
    if (file_exists($link)) {
        throw new RuntimeException('public/storage is an existing file or directory; move it manually before creating the storage link.');
    }
    symlink('../storage/app/public', $link);
}
foreach ([['config:clear', []], ['migrate', ['--seed' => true, '--force' => true]], ['filament:assets', []]] as [$command, $arguments]) {
    $result = $kernel->call($command, $arguments);
    echo $kernel->output();
    if ($result !== 0) {
        exit($result);
    }
}
