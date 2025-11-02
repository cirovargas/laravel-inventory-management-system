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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('sale_number');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->timestamp('sale_date')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['company_id', 'id']);

            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index('sale_date');
            $table->index(['company_id', 'sale_date']);
            $table->index(['company_id', 'sale_date', 'status']);
            $table->index('status');
            $table->index('created_at');

        });
        DB::statement('alter table "sales" add constraint "sales_total_amount_check" check (total_amount >= 0)');
        DB::statement('alter table "sales" add constraint "sales_total_cost_check" check (total_cost >= 0)');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};

