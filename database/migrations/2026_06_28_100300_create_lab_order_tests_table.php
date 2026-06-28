<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_order_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->unsignedBigInteger('desktop_line_id')->unique();
            $table->unsignedBigInteger('desktop_test_id')->nullable()->index();
            $table->foreignId('test_id')->nullable()->constrained('tests')->nullOnDelete();
            $table->string('test_name_snapshot');
            $table->decimal('price_snapshot', 12, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->boolean('is_cancelled')->default(false);
            $table->string('source_hash', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_order_tests');
    }
};
