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
        Schema::create('inventory_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->bigInteger('product_id');
            $table->enum('type', ['entry', 'exit'])->index();
            $table->integer('quantity');
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->bigInteger('sale_id')->nullable();
            $table->timestamp('entry_date')->useCurrent();
            $table->timestamps();
            $table->primary(['company_id', 'id']);

            // Indexes for performance and queries
            $table->index(['company_id', 'product_id']);
            $table->index(['product_id', 'type']);
            $table->index('entry_date');
            $table->index(['company_id', 'entry_date']);
            $table->index('updated_at');

        });

        DB::statement('alter table "inventory_entries" add constraint "inventory_entries_product_id_foreign" foreign key ("company_id", "product_id") references "products" ("company_id", "id")');
        DB::statement('alter table "inventory_entries" add constraint "inventory_entries_sale_id_foreign" foreign key ("company_id", "sale_id") references "sales" ("company_id", "id")');
        DB::statement('alter table "inventory_entries" add constraint "inventory_entries_quantity_check" check (quantity != 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_entries');
    }
};

