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
        //        Schema::create('sales', function (Blueprint $table) {
        //            $table->id();
        //            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
        //            $table->string('sale_number');
        //            $table->decimal('total_amount', 15, 2)->default(0);
        //            $table->decimal('total_cost', 15, 2)->default(0);
        //            $table->decimal('total_profit', 15, 2)->default(0);
        //            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
        //            $table->timestamp('sale_date')->useCurrent();
        //            $table->timestamp('completed_at')->nullable();
        //            $table->text('notes')->nullable();
        //            $table->timestamps();
        //            $table->softDeletes();
        //            $table->primary(['company_id', 'id']);
        //            $table->engine('PARTITION BY RANGE (sale_date)');
        //            dd($table->toSql());
        //
        //            // Indexes for performance
        // //            $table->index(['company_id', 'status']);
        // //            $table->index('sale_date');
        // //            $table->index(['company_id', 'sale_date']);
        // //            $table->index(['company_id', 'sale_date', 'status']);
        // //            $table->index('status');
        // //            $table->index('created_at');
        //
        //        });
        DB::statement(<<<'EOT'
            create table "sales"
            (
                "id"           bigserial not null,
                "company_id"   bigint not null references "companies" ("id") on delete cascade,
                "sale_number"  varchar(255) not null,
                "total_amount" decimal(15, 2) not null default '0',
                "total_cost"   decimal(15, 2) not null default '0',
                "total_profit" decimal(15, 2) not null default '0',
                "status"       varchar(255) check ("status" in ('pending', 'processing', 'completed', 'failed')) not null default 'pending',
                "sale_date"    timestamp(0) without time zone not null default CURRENT_TIMESTAMP,
                "completed_at" timestamp(0) without time zone null,
                "notes"        text null,
                "created_at"   timestamp(0) without time zone null,
                "updated_at"   timestamp(0) without time zone null,
                "deleted_at"   timestamp(0) without time zone null
            ) partition by range (sale_date);
            EOT
        );
        //        DB::statement('alter table "sales" add constraint "sales_company_id_foreign" foreign key ("company_id") references "companies" ("id") on delete cascade');
        DB::statement('alter table "sales" add primary key ("company_id", "id", "sale_date")');
        DB::statement('CREATE INDEX idx_sales_company_status_date_desc ON sales (company_id, status, sale_date DESC) WHERE deleted_at IS NULL');
        DB::statement('alter table "sales" add constraint "sales_total_amount_check" check (total_amount >= 0)');
        DB::statement('alter table "sales" add constraint "sales_total_cost_check" check (total_cost >= 0)');
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
                            part_name   := format('sales_%s', to_char(month_start, 'YYYY_MM'));

                            EXECUTE format(
                                    'CREATE TABLE IF NOT EXISTS %I PARTITION OF sales
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
        Schema::dropIfExists('sales');
    }
};
