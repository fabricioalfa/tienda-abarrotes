<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('products')
            ->select(['id', 'code'])
            ->orderBy('id')
            ->get()
            ->each(function ($product): void {
                if (! preg_match('/^[0-9]+$/', (string) $product->code)) {
                    DB::table('products')
                        ->where('id', $product->id)
                        ->update([
                            'code' => str_pad((string) $product->id, 8, '0', STR_PAD_LEFT),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // No reversible transformation needed for normalized product codes.
    }
};
