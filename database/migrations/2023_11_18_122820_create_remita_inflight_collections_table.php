<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('remita_inflight_collections', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 10, 2, ['unsigned' => true]);
            $table->string('modulename')->nullable();;
            $table->boolean('notificationSent')->default(false);
            $table->date('dateNotificationSent')->nullable();
            $table->boolean('firstNotificationSent')->nullable();
            $table->date('dateFirstNotificationSent')->nullable();
            $table->decimal('netSalary', 10, 2)->nullable();;
            $table->decimal('totalCredit', 10, 2)->nullable();
            $table->string('customerPhoneNumber')->nullable();
            $table->string('mandateRef')->nullable();
            $table->decimal('balanceDue', 10, 2)->nullable();
            $table->string('customer_id')->nullable();
            $table->string('request_id')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('status_reason')->nullable();
            $table->enum('don_net_notification', ['closed', 'pending', 'retry'])->default('pending');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remita_inflight_collections');
    }
};
