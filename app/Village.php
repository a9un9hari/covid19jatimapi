<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Village extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'city',
        'odp',
        'pdp',
        'confirm',
        'last_update'
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
}
