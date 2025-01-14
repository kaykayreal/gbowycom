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
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->unsignedBigInteger('third_party_apis_id');
            $table->foreign('third_party_apis_id')->references('id')->on('third_party_apis');
        });
    }

    public function down()
    {
        Schema::table('api_credentials', function (Blueprint $table) {
            $table->dropForeign(['third_party_apis_id']);
            $table->dropColumn('third_party_apis_id');
        });
    }
};
