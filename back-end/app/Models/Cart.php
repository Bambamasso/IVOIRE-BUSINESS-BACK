<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    //
     use HasFactory, HasUuids, SoftDeletes;
     protected $fillable = [
        'user_id',
        'session_id',];
    protected $keyType = 'string';
    public $incrementing = false;

    public function items(){
        return $this->hasMany(CartItem::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
    

}
