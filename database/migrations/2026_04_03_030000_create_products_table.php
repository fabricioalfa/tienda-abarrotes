<?php
// filepath: /home/fabri/Documentos/tienda/database/migrations/2026_04_03_030000_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('barcode')->nullable()->unique();
            $table->enum('sale_type', ['weight', 'unit']);
            $table->enum('weight_unit', ['kg', 'g'])->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('stock', 10, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};