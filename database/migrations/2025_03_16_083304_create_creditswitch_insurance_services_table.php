<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class createCreditswitchInsuranceServicesTable extends Migration

{
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            // Use serviceId as the primary key
            $table->string('service_id')->primary(); // e.g., "INS1", "INS2"
            $table->string('name');                 // e.g., "PHONE", "LAPTOP"
            // Store arrays as JSON
            $table->json('invoice_period')->nullable();  // e.g., ["6m", "1y"]
            $table->json('product_type')->nullable();      // e.g., ["basic", "full"]
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('services');
    }
}
