<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $guarded = []; // អនុញ្ញាតអោយកែគ្រប់ field

    // ទំនាក់ទំនង៖ Product ស្ថិតក្នុង Category មួយ
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ទំនាក់ទំនង៖ Product មួយអាចមាន Addons ច្រើន (Many-to-Many)
    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'product_addon');
    }

}