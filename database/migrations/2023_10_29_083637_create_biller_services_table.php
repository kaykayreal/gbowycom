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
        Schema::create('biller_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customerId');
            $table->string('transactionId');
            $table->string('category');
            $table->string('biller');
            $table->string('subscription')->nullable();
            $table->string('subscriptionMonth')->nullable();
            $table->string('addon')->nullable();
            $table->string('addonMonth')->nullable();
            $table->string('preferredVendor');
            $table->string('email')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('vendingStatus')->nullable();
            $table->unsignedSmallInteger('vendingRetrials')->nullable();
            $table->text('payload')->nullable();
            $table->string('plan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biller_services');
    }
};
