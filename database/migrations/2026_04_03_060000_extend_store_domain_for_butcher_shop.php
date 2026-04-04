<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cash_registers')) {
            Schema::create('cash_registers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('opened_by')->constrained('users')->restrictOnDelete();
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('opened_at');
                $table->timestamp('closed_at')->nullable();
                $table->decimal('opening_amount', 10, 2);
                $table->decimal('cash_sales_total', 10, 2)->default(0);
                $table->decimal('qr_sales_total', 10, 2)->default(0);
                $table->decimal('credit_sales_total', 10, 2)->default(0);
                $table->decimal('counted_cash', 10, 2)->nullable();
                $table->decimal('difference_amount', 10, 2)->nullable();
                $table->enum('status', ['open', 'closed'])->default('open');
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['status', 'opened_at']);
            });
        }

        if (! Schema::hasTable('product_batches')) {
            Schema::create('product_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->decimal('quantity', 10, 3);
                $table->decimal('remaining_quantity', 10, 3);
                $table->decimal('unit_cost', 10, 2)->nullable();
                $table->date('expires_at')->nullable();
                $table->string('supplier_name')->nullable();
                $table->string('reference')->nullable();
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'expires_at']);
            });
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable();
            }
            if (! Schema::hasColumn('products', 'supplier_name')) {
                $table->string('supplier_name')->nullable();
            }
            if (! Schema::hasColumn('products', 'allows_package_sale')) {
                $table->boolean('allows_package_sale')->default(false);
            }
            if (! Schema::hasColumn('products', 'package_name')) {
                $table->string('package_name')->nullable();
            }
            if (! Schema::hasColumn('products', 'units_per_package')) {
                $table->unsignedInteger('units_per_package')->nullable();
            }
            if (! Schema::hasColumn('products', 'package_price')) {
                $table->decimal('package_price', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('products', 'minimum_stock')) {
                $table->decimal('minimum_stock', 10, 3)->default(0);
            }
            if (! Schema::hasColumn('products', 'track_expiration')) {
                $table->boolean('track_expiration')->default(false);
            }
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_movements', 'unit_cost')) {
                $table->decimal('unit_cost', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('inventory_movements', 'expires_at')) {
                $table->date('expires_at')->nullable();
            }
        });

        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'cash_register_id')) {
                $table->foreignId('cash_register_id')->nullable()->constrained('cash_registers')->nullOnDelete();
            }
            if (! Schema::hasColumn('sales', 'customer_name')) {
                $table->string('customer_name')->nullable();
            }
            if (! Schema::hasColumn('sales', 'customer_phone')) {
                $table->string('customer_phone')->nullable();
            }
            if (! Schema::hasColumn('sales', 'payment_method')) {
                $table->enum('payment_method', ['cash', 'qr', 'mixed', 'credit'])->default('cash');
            }
            if (! Schema::hasColumn('sales', 'cash_amount')) {
                $table->decimal('cash_amount', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('sales', 'qr_amount')) {
                $table->decimal('qr_amount', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('sales', 'cash_received')) {
                $table->decimal('cash_received', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('sales', 'change_amount')) {
                $table->decimal('change_amount', 10, 2)->default(0);
            }
            if (! Schema::hasColumn('sales', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0);
            }
        });

        Schema::table('sale_items', function (Blueprint $table) {
            if (! Schema::hasColumn('sale_items', 'stock_quantity')) {
                $table->decimal('stock_quantity', 10, 3)->nullable();
            }
            if (! Schema::hasColumn('sale_items', 'pricing_mode')) {
                $table->string('pricing_mode')->nullable();
            }
            if (! Schema::hasColumn('sale_items', 'unit_label')) {
                $table->string('unit_label')->nullable();
            }
        });

        if (Schema::hasTable('sale_items')) {
            DB::table('sale_items')->update([
                'stock_quantity' => DB::raw('quantity'),
            ]);
        }

        if (
            DB::getDriverName() === 'mysql'
            && Schema::hasColumn('products', 'weight_unit')
            && Schema::hasColumn('sale_items', 'weight_unit')
        ) {
            DB::statement("ALTER TABLE products MODIFY weight_unit ENUM('kg', 'g', 'lb') NULL");
            DB::statement("ALTER TABLE sale_items MODIFY weight_unit ENUM('kg', 'g', 'lb') NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE sale_items MODIFY weight_unit ENUM('kg', 'g') NULL");
            DB::statement("ALTER TABLE products MODIFY weight_unit ENUM('kg', 'g') NULL");
        }

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'pricing_mode', 'unit_label']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_register_id');
            $table->dropColumn([
                'customer_name',
                'customer_phone',
                'payment_method',
                'cash_amount',
                'qr_amount',
                'cash_received',
                'change_amount',
                'discount_amount',
            ]);
        });

        Schema::table('inventory_movements', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'expires_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'brand',
                'supplier_name',
                'allows_package_sale',
                'package_name',
                'units_per_package',
                'package_price',
                'minimum_stock',
                'track_expiration',
            ]);
        });

        Schema::dropIfExists('product_batches');
        Schema::dropIfExists('cash_registers');
    }
};