<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_presentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->unsignedInteger('units_per_presentation');
            $table->decimal('price', 10, 2);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['product_id', 'name']);
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'presentation_id')) {
                $table->foreignId('presentation_id')->nullable()->constrained('product_presentations')->nullOnDelete()->after('product_id');
            }

            if (! Schema::hasColumn('sale_items', 'presentation_name')) {
                $table->string('presentation_name', 100)->nullable()->after('pricing_mode');
            }
        });

        $this->migrateLegacyPackages();
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('sale_items', 'presentation_name')) {
                $table->dropColumn('presentation_name');
            }

            if (Schema::hasColumn('sale_items', 'presentation_id')) {
                $table->dropConstrainedForeignId('presentation_id');
            }
        });

        Schema::dropIfExists('product_presentations');
    }

    protected function migrateLegacyPackages(): void
    {
        if (! Schema::hasColumn('products', 'allows_package_sale')) {
            return;
        }

        $now = now();

        DB::table('products')
            ->where('allows_package_sale', true)
            ->whereNotNull('units_per_package')
            ->where('units_per_package', '>', 0)
            ->whereNotNull('package_price')
            ->where('package_price', '>', 0)
            ->orderBy('id')
            ->chunkById(200, function ($products) use ($now) {
                foreach ($products as $product) {
                    $name = $product->package_name ?: 'Paquete';
                    $exists = DB::table('product_presentations')
                        ->where('product_id', $product->id)
                        ->where('name', $name)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    DB::table('product_presentations')->insert([
                        'product_id' => $product->id,
                        'name' => $name,
                        'units_per_presentation' => (int) $product->units_per_package,
                        'price' => (float) $product->package_price,
                        'sort_order' => 0,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            });
    }
};
