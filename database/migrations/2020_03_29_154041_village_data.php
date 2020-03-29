<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class VillageData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('village_data', function (Blueprint $table) {
            $table->id();
            $table->string('city',50);
            $table->string('village',50);
            $table->integer('odp');
            $table->integer('odp_die')->nullable();
            $table->integer('odp_recover')->nullable();
            $table->integer('pdp');
            $table->integer('pdp_die')->nullable();
            $table->integer('pdp_recover')->nullable();
            $table->integer('confirm');
            $table->integer('confirm_die')->nullable();
            $table->integer('confirm_recover')->nullable();
            $table->string('last_update',30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
