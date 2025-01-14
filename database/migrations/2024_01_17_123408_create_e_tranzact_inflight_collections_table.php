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
        Schema::create('e_tranzact_inflight_collections', function (Blueprint $table) {
            $table->id();
            $table->string('paymentID')->nullable();
            $table->string('loanRef')->nullable();
            $table->string('payeeID')->nullable();
            $table->string('accountNo')->nullable();
            $table->string('bankCode')->nullable();
            $table->string('businessId')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('source')->nullable();
            $table->date('datePaid')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_tranzact_inflight_collections');
    }
};
