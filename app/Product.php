<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Seller;
use App\Transaction;
use App\Category;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    const PRODUCTO_DISPONIBLE = 'disponible';
    const PRODUCTO_NO_DISPONIBLE = 'no disponible';

    protected $fillable = [
        'name',
        'description',
        'quantity',
        'status',
        'image',
        'seller_id',
    ];

    protected $hidden = [
        'pivot'
    ];

    public function estaDisponible()
    {
        return $this->status == Product::PRODUCTO_DISPONIBLE;
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }
    
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
}
