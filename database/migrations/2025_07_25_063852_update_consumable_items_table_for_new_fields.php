<?php
// database/migrations/xxxx_xx_xx_update_consumable_items_table_for_new_fields.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consumable_items', function (Blueprint $table) {
            // --- IMPORTANT: Ensure old 'price' column is dropped if it exists ---
            if (Schema::hasColumn('consumable_items', 'price')) {
                $table->dropColumn('price');
            }
            // --- IMPORTANT: Ensure old 'current_stock' column is dropped if it exists ---
            if (Schema::hasColumn('consumable_items', 'current_stock')) {
                $table->dropColumn('current_stock');
            }

            // Add new columns (with checks to prevent re-adding if migration partially ran)
            if (!Schema::hasColumn('consumable_items', 'purchase_price')) {
                $table->decimal('purchase_price', 10, 2)->after('unit');
            }
            if (!Schema::hasColumn('consumable_items', 'sale_price')) {
                $table->decimal('sale_price', 10, 2)->after('purchase_price');
            }
            if (!Schema::hasColumn('consumable_items', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null')->after('sale_price');
            }
            if (!Schema::hasColumn('consumable_items', 'company_name')) {
                $table->string('company_name')->nullable()->after('category_id');
            }
            if (!Schema::hasColumn('consumable_items', 'location_id')) {
                $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null')->after('min_stock_level');
            }
            if (!Schema::hasColumn('consumable_items', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('location_id');
            }
            if (!Schema::hasColumn('consumable_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('barcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('consumable_items', function (Blueprint $table) {
            // Revert changes (for rollback)
            // Drop new columns if they exist
            if (Schema::hasColumn('consumable_items', 'purchase_price')) {
                $table->dropColumn([
                    'purchase_price',
                    'sale_price',
                    'category_id',
                    'company_name',
                    'location_id',
                    'barcode',
                    'is_active'
                ]);
            }
            // Re-add 'current_stock' if it was originally there
            if (!Schema::hasColumn('consumable_items', 'current_stock')) {
                $table->integer('current_stock')->default(0);
            }
            // Re-add 'price' if it was originally there and you want it back on rollback
            // This depends on your original create_consumable_items_table migration
            // If your original migration created 'price', you might need to add it back here
            // Example: $table->decimal('price', 10, 2)->nullable();
        });
    }
};