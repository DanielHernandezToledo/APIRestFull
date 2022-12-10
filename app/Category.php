<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;
use App\Transformers\CategoryTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{


    public $transformer = CategoryTransformer::class;

    use SoftDeletes;
    
    protected $dates = ['deleted_at'];

    protected $fillable =[
        'name',
        'description',
    ];

    protected $hidden = [
        'pivot'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

}
