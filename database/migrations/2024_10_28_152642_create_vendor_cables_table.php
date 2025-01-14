<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorCablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_cables', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->integer('amount');
            $table->string('code', 255)->nullable();
            $table->string('addon_code', 255)->nullable();
            $table->string('service_type', 255);
            $table->unsignedBigInteger('vendor_id');
            $table->timestamps();
            
            // Optional: Add foreign key constraint if vendor_id references an id in vendors table
            // $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_cables');
    }
}
