<?php

// database/migrations/xxxx_xx_xx_create_stock_issues_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_issues', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->date('issue_date');
            $table->time('issue_time');
            $table->string('employee_id'); // Assuming employee ID is a string (e.g., EMP001)
            $table->string('employee_name');
            $table->string('employee_department');
            $table->string('employee_designation');
            $table->integer('total_issued_quantity');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_issues');
    }
};