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
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('third_party_apis_id');
            $table->string('endpoint');
            $table->text('description')->nullable();
            $table->string('api_token');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('third_party_apis_id')->references('id')->on('third_party_apis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->dropForeign(['third_party_apis_id']);
            $table->dropColumn('third_party_apis_id');
        });
    }
};
