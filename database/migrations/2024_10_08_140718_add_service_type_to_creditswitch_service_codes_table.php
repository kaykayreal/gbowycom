<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceTypeToCreditswitchServiceCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('creditswitch_service_codes', function (Blueprint $table) {
            $table->string('service_type')->nullable()->after('biller'); // Adjust 'existing_column_name' based on your table's structure
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('creditswitch_service_codes', function (Blueprint $table) {
            $table->dropColumn('service_type');
        });
    }
}
