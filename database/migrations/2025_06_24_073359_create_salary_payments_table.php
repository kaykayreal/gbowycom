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
        Schema::create('salaryPayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sal_customer_id')->constrained('salCustomers')->onDelete('cascade');
            $table->dateTime('payment_date');
            $table->decimal('amount', 10, 2);
            $table->string('account_number');
            $table->string('bank_code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaryPayments');
    }
};
