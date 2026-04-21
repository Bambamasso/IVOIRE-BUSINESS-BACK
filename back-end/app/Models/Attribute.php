<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attribute extends Model
{
    //
    use HasUuids, SoftDeletes;

     protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'name',
        'slug',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
    
}
