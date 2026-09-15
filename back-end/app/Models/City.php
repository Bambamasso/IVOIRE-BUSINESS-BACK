<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Municipality;

class City extends Model
{
    use HasUuids, SoftDeletes;

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
