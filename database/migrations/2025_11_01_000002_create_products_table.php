<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('cost_price', 15, 2);
            $table->decimal('sale_price', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['company_id', 'id']);

            // Indexes for performance
            $table->index('is_active');
            $table->index(['company_id', 'is_active']);
            $table->index('created_at');
        });

        DB::statement('alter table "products" add constraint "products_cost_price_check" check (cost_price >= 0)');
        DB::statement('alter table "products" add constraint "products_sale_price_check" check (sale_price >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

