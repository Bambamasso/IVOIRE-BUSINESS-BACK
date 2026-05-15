<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model
{
    //
     use HasFactory, HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'status_type_id',
        'name',
        'code'  
    ];

    public function statusType()
    {
        return $this->belongsTo(StatusType::class, 'status_type_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class,);
    }

}
