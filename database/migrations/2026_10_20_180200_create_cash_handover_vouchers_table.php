<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_handover_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('voucher_date');
            $table->decimal('total_collected', 12, 2)->default(0);
            $table->decimal('previously_handed_over', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->json('breakdown')->nullable();
            $table->string('status')->default('pending'); // pending|received|rejected
            $table->decimal('received_amount', 12, 2)->nullable();
            $table->decimal('difference', 12, 2)->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->text('receiver_remarks')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_handover_vouchers');
    }
};
