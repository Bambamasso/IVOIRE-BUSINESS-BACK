<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Municipality;

class City extends Model
{
    //
    protected $keyType="string";
    public $incrementing=false;
    protected $fillable = ['name'];

    public function municipalities(){
        return $this->hasMany(Municipality::class);
    }
    public function orders(){
        return $this->hasMany(Order::class);
    }

}
