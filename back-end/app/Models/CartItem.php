<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Products;

class CartItem extends Model
{
    //
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'cart_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'price',
    ];
    protected $keyType = 'string';
    public $incrementing = false;

    public function cart()
    {
        return $this->belongsTo(Cart::class,'cart_id');
    }
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_id');
    }
    public function product(){
        return $this->belongsTo(Products::class, 'product_id');
    }
}
