<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Refactor1CreateDeviceLinkStateLogTable extends Migration
{
    const table = 'device_link_state_logs';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(self::table, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_id');
            $table->unsignedBigInteger('device_link_id');
            $table->unsignedBigInteger('timestamp');
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
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::table);
    }
}
