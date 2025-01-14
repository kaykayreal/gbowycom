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
        Schema::create('create_creditswitch_api_keys_tables', function (Blueprint $table) {
            $table->id();
            $table->text('service_name');
            $table->text('baseUrl')->nullable();
            $table->text('merchantName')->nullable();
            $table->text('loginId')->nullable();
            $table->text('publicKey')->nullable();
            $table->text('privateKey')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('create_creditswitch_api_keys_tables');
    }
};
