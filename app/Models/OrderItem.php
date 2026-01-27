<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 
        'product_id', 
        'quantity', 
        'price', 
        'note', 
        'is_printed', 
        'created_by'
    ];

    // ទំនាក់ទំនង៖ មុខម្ហូបនេះស្ថិតក្នុង Order ណា
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // ទំនាក់ទំនង៖ មុខម្ហូបនេះគឺ Product អី (ដើម្បីយកឈ្មោះ និងរូប)
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ទំនាក់ទំនង៖ តើមុខម្ហូបនេះមានថែម Addon អីខ្លះ? (Ex: ថែមសាច់)
    public function selectedAddons()
    {
        return $this->hasMany(OrderItemAddon::class);
    }

    // ទំនាក់ទំនង៖ អ្នកណាជាអ្នកចុចកុម្ម៉ង់ចាននេះ? (User A ឬ User B)
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}