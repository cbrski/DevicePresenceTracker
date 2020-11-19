<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Refactor1CreateDeviceLinkTable extends Migration
{
    const table = 'device_links';

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
            $table->string('lladdr', 17)->comment('mac');
            $table->string('dev', 20)->comment('interface on router');
            $table->bigInteger('ipv4', false, true)->nullable();
            $table->binary('ipv6')->nullable();
            $table->string('hostname', 100)->nullable();
            $table->timestamps();
            $table->unique('lladdr');
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
