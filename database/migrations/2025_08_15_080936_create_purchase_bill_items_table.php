<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_bill_id')->constrained('purchase_bills')->onDelete('cascade');
            $table->string('item_id'); // ID of the purchased item
            $table->string('item_name');
            $table->string('item_unit');
            $table->integer('quantity');
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('tax_percentage', 5, 2);
            $table->decimal('discount_value', 10, 2);
            $table->string('discount_type'); // 'percentage' or 'flat'
            $table->decimal('item_total', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bill_items');
    }
};