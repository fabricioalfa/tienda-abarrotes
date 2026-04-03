<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('movement_type', ['entry', 'sale', 'adjustment']);
            $table->enum('direction', ['in', 'out']);
            $table->decimal('quantity', 10, 3);
            $table->decimal('stock_before', 10, 3);
            $table->decimal('stock_after', 10, 3);
            $table->string('reference')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['movement_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
