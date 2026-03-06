<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    //
    use HasFactory, HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded=[];
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orderItems(){
        return $this->hasMany(OrderItem::class);
    }

}
