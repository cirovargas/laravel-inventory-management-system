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
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sale_id');
            $table->bigInteger('product_id');
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('cost_total', 15, 2);
            $table->decimal('profit', 15, 2);
            $table->timestamps();

            // Indexes for performance
            $table->index('sale_id');
            $table->index('product_id');
            $table->index('company_id');
            $table->index(['sale_id', 'product_id']);
            $table->primary(['company_id', 'id']);

        });

        DB::statement('alter table "sale_items" add constraint "sale_items_product_id_foreign" foreign key ("company_id", "product_id") references "products" ("company_id", "id")');
        DB::statement('alter table "sale_items" add constraint "sale_items_sale_id_foreign" foreign key ("company_id", "sale_id") references "sales" ("company_id", "id")');

        DB::statement('alter table "sale_items" add constraint "sale_items_quantity_check" check (quantity > 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_unit_price_check" check (unit_price >= 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_unit_cost_check" check (unit_cost >= 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_subtotal_check" check (subtotal >= 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_cost_total_check" check (cost_total >= 0)');


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};

