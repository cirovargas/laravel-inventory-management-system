<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //        Schema::create('sale_items', function (Blueprint $table) {
        //            $table->id();
        //            $table->bigInteger('sale_id');
        //            $table->bigInteger('product_id');
        //            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        //            $table->integer('quantity');
        //            $table->decimal('unit_price', 15, 2);
        //            $table->decimal('unit_cost', 15, 2);
        //            $table->decimal('subtotal', 15, 2);
        //            $table->decimal('cost_total', 15, 2);
        //            $table->decimal('profit', 15, 2);
        //            $table->timestamp('sale_date')->nullable();
        //            $table->timestamps();
        //
        //            // Indexes for performance
        //            $table->index('sale_id');
        //            $table->index('product_id');
        //            $table->index('company_id');
        //            $table->index(['sale_id', 'product_id', 'company_id', 'sale_date']);
        //            $table->primary(['company_id', 'id']);
        //
        //            dd($table->toSql());
        //
        //        });

        DB::statement(<<<'EOT'
            create table "sale_items"
            (
                "id"         bigserial not null,
                "sale_id"    bigint not null,
                "product_id" bigint not null,
                "company_id" bigint not null,
                "quantity"   integer not null,
                "unit_price" decimal(15, 2) not null,
                "unit_cost"  decimal(15, 2) not null,
                "subtotal"   decimal(15, 2) not null,
                "cost_total" decimal(15, 2) not null,
                "profit"     decimal(15, 2) not null,
                "sale_date"  timestamp(0) without time zone null,
                "created_at" timestamp(0) without time zone null,
                "updated_at" timestamp(0) without time zone null
            ) PARTITION BY RANGE (sale_date)
            EOT
        );

        DB::statement('alter table "sale_items" add constraint "sale_items_company_id_foreign" foreign key ("company_id") references "companies" ("id") on delete cascade');
        DB::statement('create index "sale_items_sale_id_index" on "sale_items" ("sale_id")');
        DB::statement('create index "sale_items_product_id_index" on "sale_items" ("product_id")');
        DB::statement('create index "sale_items_company_id_index" on "sale_items" ("company_id")');
        DB::statement('create index "sale_items_sale_id_product_id_company_id_sale_date_index" on "sale_items" ("sale_id", "product_id", "company_id", "sale_date")');
        DB::statement('alter table "sale_items" add primary key ("company_id", "id", "sale_date")');

        DB::statement('alter table "sale_items" add constraint "sale_items_product_id_foreign" foreign key ("company_id", "product_id") references "products" ("company_id", "id")');
        DB::statement('alter table "sale_items" add constraint "sale_items_sale_id_foreign" foreign key ("company_id", "sale_id","sale_date") references "sales" ("company_id", "id","sale_date")');

        DB::statement('alter table "sale_items" add constraint "sale_items_quantity_check" check (quantity > 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_unit_price_check" check (unit_price >= 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_unit_cost_check" check (unit_cost >= 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_subtotal_check" check (subtotal >= 0)');
        DB::statement('alter table "sale_items" add constraint "sale_items_cost_total_check" check (cost_total >= 0)');
        DB::statement(<<<'EOT'
            DO $$
                DECLARE
                    start_month date := date_trunc('month', current_date)::date - INTERVAL '12 months';
                    month_start date;
                    month_end   date;
                    part_name   text;
                BEGIN
                    FOR i IN 0..12 LOOP
                            month_start := (start_month + (i || ' months')::interval)::date;
                            month_end   := (month_start + INTERVAL '1 month')::date;
                            part_name   := format('sales_items_%s', to_char(month_start, 'YYYY_MM'));

                            EXECUTE format(
                                    'CREATE TABLE IF NOT EXISTS %I PARTITION OF sale_items
                                       FOR VALUES FROM (%L) TO (%L);',
                                    part_name, month_start, month_end
                                    );

                        END LOOP;
                END
            $$ LANGUAGE plpgsql;
            EOT
        );

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
