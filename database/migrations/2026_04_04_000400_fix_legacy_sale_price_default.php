<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'sale_price')) {
            return;
        }

        DB::statement('UPDATE products SET sale_price = COALESCE(sale_price, price, 0)');

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products MODIFY sale_price DECIMAL(10,2) NOT NULL DEFAULT 0.00');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'sale_price')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products MODIFY sale_price DECIMAL(10,2) NOT NULL');
        }
    }
};
