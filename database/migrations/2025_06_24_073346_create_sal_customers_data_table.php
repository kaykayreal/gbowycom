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
        Schema::create('salCustomersData', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('account_number')->unique();
            $table->string('bvn')->unique();
            $table->string('bank');
            $table->string('company_name')->nullable();
            $table->string('category')->nullable();
            $table->dateTime('first_payment_date')->nullable();
            $table->integer('salary_count')->nullable();
            $table->decimal('average_salary', 10, 2)->nullable();
            $table->integer('existing_loan')->nullable();
            $table->integer('tenure')->nullable();
            $table->string('original_customer_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salCustomersData');
    }
};
