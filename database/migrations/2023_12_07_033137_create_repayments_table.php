<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('repayments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained();
            $table->decimal('repayment_amount', 10, 2);
            $table->dateTime('repayment_date');
            $table->enum('payment_status', ['paid', 'pending'])->default('pending');
            $table->enum('repayment_agent', ['remita', 'etranzact', 'other'])->default('remita');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('repayments');
    }
}
