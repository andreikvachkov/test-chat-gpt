<?php

use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! app()->environment(['local', 'testing'])) {
    throw new RuntimeException('Local/test database required.');
}
$suffix = Str::lower(Str::random(10));
$ownerPassword = Str::random(28).'A1x9';
$customerPassword = Str::random(28).'B2x8';
$staffPassword = Str::random(28).'C3x7';
$email = 'access-owner-'.$suffix.'@example.test';
AdminUser::create(['name' => 'Владелец '.$suffix, 'email' => $email, 'password' => $ownerPassword, 'admin_role_id' => AdminRole::where('code', 'owner')->firstOrFail()->id, 'active' => true]);
User::create(['name' => 'Покупатель '.$suffix, 'email' => $email, 'password' => $customerPassword]);
echo json_encode(['suffix' => $suffix, 'owner_email' => $email, 'owner_password' => $ownerPassword, 'customer_password' => $customerPassword, 'staff_email' => 'access-staff-'.$suffix.'@example.test', 'staff_password' => $staffPassword]);
