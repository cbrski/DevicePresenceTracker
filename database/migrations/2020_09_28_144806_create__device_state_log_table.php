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
            $table->integer('id_device');
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_device_state_log');
    }
}
