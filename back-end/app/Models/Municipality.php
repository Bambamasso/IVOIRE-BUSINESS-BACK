<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Municipality extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType="string";
    public $incrementing=false;

    protected $fillable = ['city_id', 'name', 'shipping_fee'];

    public function city(){
        return $this->belongsTo(City::class, 'city_id');
    }
}
