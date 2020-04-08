<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DataJatim extends Model
{
    public $timestamps = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'id_old',
        'kode',
        'kabko',
        'lat',
        'lon',
        'lat1',
        'lon1',
        'lat2',
        'lon2',
        'lat3',
        'lon3',
        'id_kabko',
        'odr',
        'otg',
        'odp',
        'odp_pantau',
        'odp_belumdipantau',
        'odp_isolasidirumah',
        'odp_isolasidigedung',
        'odp_isolasidirs',
        'odp_selesai',
        'odp_meninggal',
        'pdp',
        'pdp_belumdiawasi',
        'pdp_dirawat',
        'pdp_sehat',
        'pdp_meninggal',
        'pdp_isolasidirumah',
        'pdp_isolasidigedung',
        'pdp_isolasidirs',
        'confirm',
        'confirm_dirawat',
        'confirm_isolasidirumah',
        'confirm_isolasidigedung',
        'confirm_isolasidirs',
        'latitude',
        'longitude',
        'id_user',
        'label_lat',
        'label_lon',
        'sembuh',
        'meninggal',
        'created_at',
        'updated_at'
    ];
}
