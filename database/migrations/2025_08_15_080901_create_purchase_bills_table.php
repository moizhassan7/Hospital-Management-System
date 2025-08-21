<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_bills', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_id')->unique();
            $table->date('purchase_date');
            $table->string('bill_id')->unique();
            $table->date('bill_date');
            $table->string('supplier_name');
            $table->decimal('sub_total', 10, 2);
            $table->decimal('total_tax', 10, 2);
            $table->decimal('total_bill_discount', 10, 2);
            $table->decimal('grand_total', 10, 2);
            $table->decimal('received_payment', 10, 2);
            $table->decimal('balance', 10, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_bills');
    }
};