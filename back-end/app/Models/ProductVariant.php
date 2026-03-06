<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    //
      use HasFactory, HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
       'status_id',
        "product_id",
        "stock_quantity",
        "sku",
        "price",
        'created_by',
        'updated_by',
        'deleted_by',
    ];
    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    // Produit parent
    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function attributValues(){
        return $this->belongsToMany(
            AttributeValue::class,
            'product_variant_attributes',
            'product_variant_id',
            'attribute_value_id'
        )->withTimestamps();
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
