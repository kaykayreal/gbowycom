<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('payGateName');
            $table->string('txnRef');
            $table->decimal('amount', 10, 2);
            $table->timestamp('created_at');
            $table->decimal('fees', 10, 2);
            $table->string('gatewayResponse');
            $table->string('gateId');
            $table->string('ipAddress');
            $table->string('status');
            $table->string('bank');
            $table->string('bin');
            $table->string('brand');
            $table->string('channel');
            $table->string('expMonth');
            $table->string('expYear');
            $table->string('lastFour');
            $table->text('dump');
            $table->string('transactionId');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}
