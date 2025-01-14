<?php 

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToBillerServicesTable extends Migration
{
    public function up()
    {
        Schema::table('biller_services', function (Blueprint $table) {
            $table->text('service_description')->nullable(); // Add new column here
        });
    }

    public function down()
    {
        Schema::table('biller_services', function (Blueprint $table) {
            $table->dropColumn('service_description'); // Remove the column in case of rollback
        });
    }
}
