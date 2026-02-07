<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = []; // អនុញ្ញាតអោយកែគ្រប់ field

    // ទំនាក់ទំនង៖ 1 Category មាន Products ច្រើន
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function destination()
    {
        return $this->belongsTo(KitchenDestination::class, 'kitchen_destination_id');
    }

    public function kitchenDestination()
    {
        // ភ្ជាប់ទៅ Table 'kitchen_destinations' តាមរយៈ field 'kitchen_destination_id'
        return $this->belongsTo(KitchenDestination::class, 'kitchen_destination_id');
    }
}
