<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestLogsTable extends Migration
{
    public function up()
    {
        Schema::create('request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('request_url')->nullable();
            $table->string('request_method')->nullable();
            $table->text('request_headers')->nullable();
            $table->text('request_body')->nullable();
            $table->string('merchant_ref')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('merchant_code')->nullable();
            $table->integer('response_status')->nullable();
            $table->text('response_body')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_logs');
    }
}
