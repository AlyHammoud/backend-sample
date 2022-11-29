<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemImage extends Model
{
    use HasFactory;
    protected $fillable = ['image_url', 'item_id'];

    public function Item()
    {
        return $this->belongsTo(Item::class);
    }
}
