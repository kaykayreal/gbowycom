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
        Schema::create('etranzact_details', function (Blueprint $table) {
            $table->id();
            $table->text('username');
            $table->text('password');
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->string('authURL');
            $table->string('getCorporate');
            $table->string('getsalaryInfo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etranzact_details');
    }
};
