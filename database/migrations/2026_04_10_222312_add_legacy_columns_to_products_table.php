<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add columns that exist in production but were not included in the original
     * migration files (they were added manually to the DB at some point).
     * This migration ensures the test database schema matches production.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (! Schema::hasColumn('products', 'category')) {
                $table->string('category')->nullable()->after('category_id');
            }
            if (! Schema::hasColumn('products', 'brand')) {
                // Already present in some test DBs via extend migration — guard it
                if (! Schema::hasColumn('products', 'brand')) {
                    $table->string('brand')->nullable()->after('description');
                }
            }
            if (! Schema::hasColumn('products', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('brand');
            }
            if (! Schema::hasColumn('products', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('products', 'purchase_price')) {
                $table->decimal('purchase_price', 10, 2)->default(0)->after('sale_price');
            }
            if (! Schema::hasColumn('products', 'unit')) {
                $table->string('unit')->default('unidad')->after('purchase_price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('products', 'description') ? 'description' : null,
                Schema::hasColumn('products', 'category') ? 'category' : null,
                Schema::hasColumn('products', 'sale_price') ? 'sale_price' : null,
                Schema::hasColumn('products', 'purchase_price') ? 'purchase_price' : null,
                Schema::hasColumn('products', 'unit') ? 'unit' : null,
            ]));
        });
    }
};
