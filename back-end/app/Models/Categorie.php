<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorie extends Model
{
    //
    use HasFactory, HasUuids, SoftDeletes;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'image',
        'slug',
        'order'
    ];

    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }
    public function children()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }
    public function products()
    {
        return $this->hasMany(Products::class);
    }
}
