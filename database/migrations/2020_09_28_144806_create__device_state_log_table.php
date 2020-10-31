<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeviceStateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('device_state_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('device_id')->unsigned();
            $table->timestamp('timestamp');
            $table->enum('state',
                [
                    'permament',
                    'noarp',
                    'reachable',
                    'stale',
                    'none',
                    'incomplete',
                    'delay',
                    'probe',
                    'failed',
                ]
            );
//            $table->timestamps();
            $table->foreign('device_id')->on('devices')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('device_state_logs');
    }
}
