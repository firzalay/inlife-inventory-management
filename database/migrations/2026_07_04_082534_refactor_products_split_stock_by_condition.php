<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Replace single stock + condition columns with 3 condition-specific stock columns.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'condition']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock_baik')->default(0)->after('category_id');
            $table->integer('stock_rusak')->default(0)->after('stock_baik');
            $table->integer('stock_perlu_perbaikan')->default(0)->after('stock_rusak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_baik', 'stock_rusak', 'stock_perlu_perbaikan']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('category_id');
            $table->enum('condition', ['good', 'damaged', 'lost'])->default('good')->after('location');
        });
    }
};
