<?php

use App\Models\AdminRole;
use App\Models\AdminUser;
use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\ProductCollection;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Str;

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
if (! app()->environment(['local', 'testing'])) {
    throw new RuntimeException('Browser fixtures require a local/test database.');
}
$suffix = Str::lower(Str::random(10));
$password = Str::random(32);
$user = new AdminUser;
$user->forceFill(['name' => 'Проверка редактора', 'email' => 'editor-'.$suffix.'@example.test', 'password' => $password, 'admin_role_id' => AdminRole::where('code', 'owner')->firstOrFail()->id, 'active' => true])->save();
$categories = [];
$collections = [];
foreach (['A', 'B'] as $label) {
    $categories[] = Category::create(['name' => 'Категория '.$suffix.' '.$label, 'slug' => 'category-'.$suffix.'-'.strtolower($label)])->only(['id', 'name', 'slug']);
    $collections[] = ProductCollection::create(['name' => 'Подборка '.$suffix.' '.$label, 'slug' => 'collection-'.$suffix.'-'.strtolower($label)])->only(['id', 'name', 'slug']);
}
$attributes = [];
foreach (['Объём' => ['100 мл', '250 мл'], 'Аромат' => ['Sultan', 'Vanilla']] as $name => $values) {
    $attribute = ProductAttribute::create(['name' => $name.' '.$suffix, 'slug' => Str::slug($name).'-'.$suffix]);
    foreach ($values as $value) {
        $attribute->values()->create(['name' => $value, 'slug' => Str::slug($value)]);
    }
    $attributes[] = [...$attribute->only(['id', 'name']), 'values' => $attribute->values()->get(['id', 'name'])->toArray()];
}
// Redirect stdout to a private temporary file; never log these generated credentials.
echo json_encode(['email' => $user->email, 'password' => $password, 'suffix' => $suffix, 'categories' => $categories, 'collections' => $collections, 'attributes' => $attributes], JSON_UNESCAPED_UNICODE);
