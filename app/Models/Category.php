<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type']; // type: food, drink

    // ទំនាក់ទំនង៖ 1 Category មាន Products ច្រើន
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
