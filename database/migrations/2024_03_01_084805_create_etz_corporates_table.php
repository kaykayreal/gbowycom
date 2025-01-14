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
        Schema::create('etz_corporates', function (Blueprint $table) {
            $table->id();
            $table->string('businessName')->nullable();
            $table->string('businessId')->nullable();
            $table->string('businessEmail')->nullable();
            $table->string('businessPhone')->nullable();
            $table->string('businessWebsite')->nullable();
            $table->timestamps();
                    });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etz_corporates');
    }
};
