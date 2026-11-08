<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_assets', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('path');
            $t->timestamps();
        });
        Schema::table('pages', fn (Blueprint $t) => $t->json('content')->nullable());
    }

    public function down(): void
    {
        Schema::table('pages', fn (Blueprint $t) => $t->dropColumn('content'));
        Schema::dropIfExists('media_assets');
    }
};
