<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Refactor1AddForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('device_links', function(Blueprint $table) {
            $table->foreign('device_id')->on('devices')->references('id')->onDelete('cascade');
        });

        Schema::table('device_link_state_logs', function(Blueprint $table) {
            $table->foreign('device_id')->on('devices')->references('id')->onDelete('cascade');
        });

        Schema::table('device_link_state_logs', function(Blueprint $table) {
            $table->foreign('device_link_id')->on('device_links')->references('id')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('device_links', function(Blueprint $table) {
            $table->dropForeign('device_id');
        });

        Schema::table('device_link_state_logs', function(Blueprint $table) {
            $table->dropForeign('device_id');
        });

        Schema::table('device_link_state_logs', function(Blueprint $table) {
            $table->dropForeign('device_link_id');
        });
    }
}
