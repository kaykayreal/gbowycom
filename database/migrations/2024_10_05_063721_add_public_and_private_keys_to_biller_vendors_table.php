<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPublicAndPrivateKeysToBillerVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('biller_vendors', function (Blueprint $table) {
            $table->text('public_key')->nullable();
            $table->text('private_key')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('biller_vendors', function (Blueprint $table) {
            $table->dropColumn('public_key');
            $table->dropColumn('private_key');
        });
    }
}
