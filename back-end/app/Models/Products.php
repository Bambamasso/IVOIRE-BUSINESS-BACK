<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Products extends Model
{
    //

    use HasFactory, HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'status_id',
        'category_id',
        'title',
        'description',
        'price',
        'stock_quantity',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'category_id');
    }
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id');
    }
    public function media()
    {
        return $this->morphMany(Medias::class, 'mediable');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, "product_id");
    }
    public function stockMouvements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
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
