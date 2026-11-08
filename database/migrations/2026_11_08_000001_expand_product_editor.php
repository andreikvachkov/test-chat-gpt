<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $t) {
            $t->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
        });
        Schema::create('category_product', function (Blueprint $t) {
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['category_id', 'product_id']);
        });
        Schema::create('product_collections', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('collection_product', function (Blueprint $t) {
            $t->foreignId('product_collection_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->primary(['product_collection_id', 'product_id']);
        });
        Schema::create('product_attributes', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });
        Schema::create('attribute_values', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->string('slug');
            $t->unique(['product_attribute_id', 'slug']);
            $t->timestamps();
        });
        Schema::table('products', function (Blueprint $t) {
            $t->string('sku')->nullable()->unique();
            $t->string('article')->nullable()->index();
            $t->string('type')->default('simple');
            $t->text('short_description')->nullable();
            $t->json('attribute_config')->nullable();
            $t->string('status')->default('draft')->index();
            $t->timestampTz('published_at')->nullable()->index();
            $t->softDeletesTz();
        });
        Schema::table('product_variants', function (Blueprint $t) {
            $t->string('article')->nullable();
            $t->json('attribute_value_ids')->nullable();
            $t->string('combination_key')->nullable();
            $t->timestampTz('retired_at')->nullable();
            $t->unique(['product_id', 'combination_key']);
        });
        $now = now();
        $attributeId = null;
        foreach (DB::table('products')->orderBy('id')->get() as $product) {
            $variants = DB::table('product_variants')->where('product_id', $product->id)->orderBy('id')->get();
            $sku = $variants->count() === 1 ? $variants->first()->sku : 'PRODUCT-'.$product->id;
            while (DB::table('products')->where('sku', $sku)->exists() || ($variants->count() !== 1 && DB::table('product_variants')->where('sku', $sku)->exists())) {
                $sku .= '-P';
            }
            $config = [];
            if ($variants->isNotEmpty()) {
                $attributeId ??= DB::table('product_attributes')->insertGetId(['name' => 'Вариант', 'slug' => 'variant', 'created_at' => $now, 'updated_at' => $now]);
                $values = [];
                foreach ($variants as $variant) {
                    $slug = Str::slug($variant->name) ?: 'value-'.$variant->id;
                    $term = DB::table('attribute_values')->where('product_attribute_id', $attributeId)->where('slug', $slug)->first();
                    $valueId = $term?->id ?? DB::table('attribute_values')->insertGetId(['product_attribute_id' => $attributeId, 'name' => $variant->name, 'slug' => $slug, 'created_at' => $now, 'updated_at' => $now]);
                    // Identical old labels still need distinct attribute combinations and inventory IDs.
                    if (in_array($valueId, $values, true)) {
                        $uniqueSlug = $slug.'-legacy-'.$variant->id;
                        $valueId = DB::table('attribute_values')->insertGetId(['product_attribute_id' => $attributeId, 'name' => $variant->name.' ('.$variant->sku.')', 'slug' => $uniqueSlug, 'created_at' => $now, 'updated_at' => $now]);
                    }
                    $key = $attributeId.':'.$valueId;
                    $values[] = $valueId;
                    DB::table('product_variants')->where('id', $variant->id)->update(['article' => $variant->sku, 'attribute_value_ids' => json_encode([$valueId]), 'combination_key' => $key]);
                }
                $config = [['attribute_id' => $attributeId, 'value_ids' => array_values(array_unique($values)), 'for_variations' => $variants->count() > 1, 'visible' => true]];
            }
            DB::table('products')->where('id', $product->id)->update(['sku' => $sku, 'article' => $variants->first()?->sku, 'type' => $variants->count() > 1 ? 'variable' : 'simple', 'short_description' => $product->description, 'attribute_config' => json_encode($config), 'status' => $product->published ? 'published' : 'draft', 'published_at' => $product->published ? $product->created_at : null]);
            if ($product->category_id) {
                DB::table('category_product')->insert(['category_id' => $product->category_id, 'product_id' => $product->id]);
            }
            if (filled($product->badge)) {
                $slug = Str::slug($product->badge) ?: 'collection-'.$product->id;
                $collection = DB::table('product_collections')->where('slug', $slug)->first();
                $id = $collection?->id ?? DB::table('product_collections')->insertGetId(['name' => $product->badge, 'slug' => $slug, 'created_at' => $now, 'updated_at' => $now]);
                DB::table('collection_product')->insert(['product_collection_id' => $id, 'product_id' => $product->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $t) {
            $t->dropUnique(['product_id', 'combination_key']);
            $t->dropColumn(['article', 'attribute_value_ids', 'combination_key', 'retired_at']);
        });
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['sku', 'article', 'type', 'short_description', 'attribute_config', 'status', 'published_at', 'deleted_at']));
        foreach (['attribute_values', 'product_attributes', 'collection_product', 'product_collections', 'category_product'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('categories', fn (Blueprint $t) => $t->dropConstrainedForeignId('parent_id'));
    }
};
