<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    use HasFactory;

    protected $guarded = [];

    // ✅ ដក vc_ ចេញ សរសេរត្រឹម 'product_addon' បានហើយ ព្រោះ Laravel នឹងថែម Prefix អោយខ្លួនឯង
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_addon');
    }

    public function destination()
    {
        return $this->belongsTo(KitchenDestination::class, 'kitchen_destination_id');
    }
}