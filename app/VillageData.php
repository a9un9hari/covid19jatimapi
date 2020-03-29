<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VillageData extends Model
{
    // public $table = "data_village";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'city',
        'village',
        'odp',
        'odp_die',
        'odp_recover',
        'pdp',
        'pdp_die',
        'pdp_recover',
        'confirm',
        'confirm_die',
        'confirm_recover',
        'last_update',
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
