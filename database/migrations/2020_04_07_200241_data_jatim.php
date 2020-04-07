<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DataJatim extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dataJatim', function (Blueprint $table) {
            $table->id();
            $table->string('kode',10);
            $table->string('kabko',30);
            $table->string('lat',25);
            $table->string('lon',25);
            $table->string('lat1',25);
            $table->string('lon1',25);
            $table->string('lat2',25);
            $table->string('lon2',25);
            $table->string('lat3',25);
            $table->string('lon3',25);
            $table->integer('id_kabko');
            $table->integer('odr');
            $table->integer('otg');
            $table->integer('odp');
            $table->integer('odp_pantau');
            $table->integer('odp_belumdipantau');
            $table->integer('odp_isolasidirumah');
            $table->integer('odp_isolasidigedung');
            $table->integer('odp_isolasidirs');
            $table->integer('odp_selesai');
            $table->integer('odp_meninggal');
            $table->integer('pdp');
            $table->integer('pdp_belumdiawasi');
            $table->integer('pdp_dirawat');
            $table->integer('pdp_sehat');
            $table->integer('pdp_meninggal');
            $table->integer('pdp_isolasidirumah');
            $table->integer('pdp_isolasidigedung');
            $table->integer('pdp_isolasidirs');
            $table->integer('confirm');
            $table->integer('confirm_dirawat');
            $table->integer('confirm_isolasidirumah');
            $table->integer('confirm_isolasidigedung');
            $table->integer('confirm_isolasidirs');
            $table->string('latitude',25);
            $table->string('longitude',25);
            $table->integer('id_user');
            $table->string('label_lat',50);
            $table->string('label_lon',50);
            $table->integer('sembuh');
            $table->integer('meninggal');
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
        Schema::dropIfExists('dataJatim');
    }
}
