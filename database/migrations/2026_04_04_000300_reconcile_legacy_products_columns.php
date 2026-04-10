<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }

            if (! Schema::hasColumn('products', 'sale_type')) {
                $table->enum('sale_type', ['weight', 'unit'])->default('unit');
            }

            if (! Schema::hasColumn('products', 'weight_unit')) {
                $table->enum('weight_unit', ['kg', 'g', 'lb', 'quarter'])->nullable();
            }
        });

        if (Schema::hasColumn('products', 'sale_price')) {
            DB::statement('UPDATE products SET price = COALESCE(sale_price, price, 0) WHERE sale_price IS NOT NULL');
        }

        if (Schema::hasColumn('products', 'unit')) {
            DB::statement(<<<'SQL'
                UPDATE products
                SET
                    sale_type = CASE
                        WHEN LOWER(unit) IN ('kg', 'kilo', 'kilos', 'lb', 'libra', 'libras', 'quarter', 'cuarta', 'cuartilla', 'g', 'gramo', 'gramos') THEN 'weight'
                        ELSE 'unit'
                    END,
                    weight_unit = CASE
                        WHEN LOWER(unit) IN ('kg', 'kilo', 'kilos') THEN 'kg'
                        WHEN LOWER(unit) IN ('lb', 'libra', 'libras') THEN 'lb'
                        WHEN LOWER(unit) IN ('quarter', 'cuarta', 'cuartilla') THEN 'quarter'
                        WHEN LOWER(unit) IN ('g', 'gramo', 'gramos') THEN 'g'
                        ELSE NULL
                    END
            SQL);
        }

        DB::statement("UPDATE products SET sale_type = 'unit' WHERE sale_type IS NULL OR sale_type = ''");
    }

    public function down(): void
    {
        // Compatibility migration: no destructive rollback.
    }
};
