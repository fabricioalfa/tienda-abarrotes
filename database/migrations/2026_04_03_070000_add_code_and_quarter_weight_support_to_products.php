<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'code')) {
                $table->string('code')->nullable()->unique()->after('id');
            }
        });

        DB::table('products')
            ->select(['id', 'code'])
            ->orderBy('id')
            ->get()
            ->each(function ($product): void {
                if (! empty($product->code)) {
                    return;
                }

                DB::table('products')
                    ->where('id', $product->id)
                    ->update([
                        'code' => 'PRD-' . str_pad((string) $product->id, 6, '0', STR_PAD_LEFT),
                    ]);
            });

        if (
            DB::getDriverName() === 'mysql'
            && Schema::hasColumn('products', 'weight_unit')
            && Schema::hasColumn('sale_items', 'weight_unit')
        ) {
            DB::statement("ALTER TABLE products MODIFY weight_unit ENUM('kg', 'g', 'lb', 'quarter') NULL");
            DB::statement("ALTER TABLE sale_items MODIFY weight_unit ENUM('kg', 'g', 'lb', 'quarter') NULL");
        }
    }

    public function down(): void
    {
        if (
            DB::getDriverName() === 'mysql'
            && Schema::hasColumn('products', 'weight_unit')
            && Schema::hasColumn('sale_items', 'weight_unit')
        ) {
            DB::statement("ALTER TABLE sale_items MODIFY weight_unit ENUM('kg', 'g', 'lb') NULL");
            DB::statement("ALTER TABLE products MODIFY weight_unit ENUM('kg', 'g', 'lb') NULL");
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'code')) {
                $table->dropUnique('products_code_unique');
                $table->dropColumn('code');
            }
        });
    }
};