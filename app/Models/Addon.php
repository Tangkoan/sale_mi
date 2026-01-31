<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

     protected $guarded = []; // អនុញ្ញាតអោយកែគ្រប់ field

    // ទំនាក់ទំនង៖ Addon អាចប្រើជាមួយ Products ច្រើន
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_addon');
    }


    public function destination()
    {
        return $this->belongsTo(KitchenDestination::class, 'kitchen_destination_id');
    }
}