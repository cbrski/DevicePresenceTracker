<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Refactor1DeletePreviousTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $a = new CreateDeviceMacTable();
        $a->down();
        $b = new CreateDeviceTable();
        $b->down();
        $c = new CreateDeviceStateLogTable();
        $c->down();
        $d = new CreateLogTable();
        $d->down();
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        $a = new CreateDeviceMacTable();
        $a->up();
        $b = new CreateDeviceTable();
        $b->up();
        $c = new CreateDeviceStateLogTable();
        $c->up();
        $d = new CreateLogTable();
        $d->up();
    }
}
