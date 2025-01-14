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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->constrained();
            $table->decimal('loan_amount', 10, 2);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->integer('loan_term');
            $table->enum('loan_status', ['pending', 'approved', 'rejected', 'active', 'closed'])->default('pending');
            $table->dateTime('date_approved')->nullable();
            $table->dateTime('date_disbursed')->nullable();
            $table->enum('repayment_frequency', ['weekly', 'bi-weekly', 'monthly', 'yearly'])->default('monthly');
            $table->foreignId('transaction_id')->constrained(); 
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
