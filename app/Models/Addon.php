<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'price', 'type'];

    // ទំនាក់ទំនង៖ Addon អាចប្រើជាមួយ Products ច្រើន
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_addon');
    }
}