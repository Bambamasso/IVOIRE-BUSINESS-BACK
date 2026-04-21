<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    //
     protected $keyType="string";
    public $incrementing=false;

    public function city(){
        return $this->belongsTo(City::class,'city_id ');
    }
}
