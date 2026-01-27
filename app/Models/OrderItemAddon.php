<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id', 
        'addon_id', 
        'price', 
        'quantity'
    ];

    // ទំនាក់ទំនង៖ ដឹងថាវាជា Addon ឈ្មោះអី
    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}