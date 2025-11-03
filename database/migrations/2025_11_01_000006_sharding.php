<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::statement("SELECT create_distributed_table('companies',   'id');");
            DB::statement("SELECT create_distributed_table('products',   'company_id');");
            DB::statement("SELECT create_distributed_table('sales',   'company_id');");
            DB::statement("SELECT create_distributed_table('sale_items',   'company_id');");
            DB::statement("SELECT create_distributed_table('inventory_entries',   'company_id');");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
