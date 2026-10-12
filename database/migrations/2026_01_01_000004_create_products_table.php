<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 64)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('price_minor');
            $table->char('currency', 3)->default('EUR');
            $table->unsignedInteger('stock')->default(0);
            $table->string('brand')->nullable();
            $table->string('gtin', 14)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('enriched_at')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->string('sync_hash', 64)->nullable();
            $table->timestamps();

            $table->index('synced_at');
            $table->index('gtin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
