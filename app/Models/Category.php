<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Coderflex\Laravisit\Concerns\CanVisit;
use Coderflex\Laravisit\Concerns\HasVisits;

class Category extends Model implements TranslatableContract, CanVisit
{
    use HasFactory, Translatable;
    use HasVisits;

    public $translatedAttributes = ['name', 'slug'];
    protected $fillable = ['image_url', 'is_available', 'user_id'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function products()
    {
        return $this->hasManyThrough(Product::class, Item::class);
    }
}
