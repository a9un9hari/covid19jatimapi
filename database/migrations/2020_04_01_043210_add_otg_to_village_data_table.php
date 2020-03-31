<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtgToVillageDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('village_data', function (Blueprint $table) {
            $table->integer('otg')->nullable();
            $table->integer('otg_die')->nullable();
            $table->integer('otg_recover')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('village_data', function (Blueprint $table) {
            $table->dropColumn(['otg', 'otg_die', 'otg_recover']);
        });
    }
}
