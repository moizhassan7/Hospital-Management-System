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
        Schema::create('non_consumable_items', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Item Name (e.g., X-Ray Machine, Hospital Bed)
            $table->string('asset_tag')->unique(); // Unique identifier for the asset
            $table->string('unit')->nullable(); // Unit of measurement (e.g., pcs)
            $table->decimal('purchase_price', 10, 2);
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('company_name')->nullable(); // Manufacturer/Supplier
            $table->date('purchase_date');
            $table->date('warranty_expiry_date')->nullable();
            $table->decimal('depreciation_rate', 5, 2)->default(0.00); // e.g., 10.50 for 10.5%
            $table->string('current_status')->default('Operational'); // e.g., Operational, Under Maintenance, Disposed
            $table->foreignId('location_id')->nullable()->constrained('locations')->onDelete('set null');
            $table->string('barcode')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('non_consumable_items');
    }
};