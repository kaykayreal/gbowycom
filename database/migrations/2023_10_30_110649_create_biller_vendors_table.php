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
        Schema::create('biller_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('billerVendorName');
            $table->text('billerVendorKey');
            $table->text('billerVendorStatus');
            $table->text('agentId');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biller_vendors');
    }
};
