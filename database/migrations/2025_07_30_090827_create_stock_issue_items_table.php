<?php
// database/migrations/xxxx_xx_xx_create_stock_issue_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_issue_id')->constrained('stock_issues')->onDelete('cascade'); // Link to the main issue transaction
            $table->string('item_id'); // ID of the item (Consumable or Non-Consumable)
            $table->string('item_name');
            $table->string('item_unit');
            $table->decimal('item_price', 10, 2); // Price at the time of issue
            $table->integer('issued_quantity');
            $table->decimal('total_price', 10, 2); // Calculated total for this item line
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_issue_items');
    }
};