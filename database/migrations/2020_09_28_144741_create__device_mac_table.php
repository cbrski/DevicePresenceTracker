<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceMacTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_macs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('device_id')->unsigned();
            $table->string('mac', 17);
            $table->enum('link_layer', ['ethernet', 'wifi']);
            $table->timestamps();
            $table->foreign('device_id')->references('id')->on('devices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_macs');
    }
}
