<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('consumable_items', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('name')->unique(); // Item Name (e.g., Bandage, Syringe)
            $table->string('unit')->nullable(); // Unit of measurement (e.g., pcs, box, ml)
            $table->decimal('price', 10, 2); // Price per unit
            $table->integer('current_stock')->default(0); // Current quantity in stock
            $table->integer('min_stock_level')->default(0); // Minimum stock level for alerts
            $table->timestamps(); // created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_items');
    }
};