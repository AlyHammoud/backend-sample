<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Coderflex\Laravisit\Concerns\CanVisit;
use Coderflex\Laravisit\Concerns\HasVisits;

class Product extends Model implements TranslatableContract, CanVisit
{
    use HasFactory, Translatable;
    use HasVisits;

    public $translatedAttributes = ['name', 'description'];
    protected $fillable = ['price', 'is_available', 'item_id'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }
}
