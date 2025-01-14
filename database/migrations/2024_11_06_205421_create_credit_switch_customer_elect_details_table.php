<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCreditSwitchCustomerElectDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_switch_customer_elect_details', function (Blueprint $table) {
            $table->id();
            $table->string('customerAccountId')->unique();
            $table->string('customerName');
            $table->text('customerAddress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_switch_customer_elect_details');
    }
}
