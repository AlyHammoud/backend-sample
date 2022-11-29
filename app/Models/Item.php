<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Coderflex\Laravisit\Concerns\CanVisit;
use Coderflex\Laravisit\Concerns\HasVisits;

class Item extends Model implements TranslatableContract, CanVisit
{
    use HasFactory, Translatable;
    use HasVisits;

    public $translatedAttributes = ['name', 'description'];
    protected $fillable = ['price', 'is_available', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function itemImages()
    {
        return $this->hasMany(ItemImage::class);
    }
}
