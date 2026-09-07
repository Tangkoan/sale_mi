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
    

    // នៅក្នុង Model Product.php
    public function orderItems() {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    /**
     * ទំនាក់ទំនង Many-to-Many ពី Product ទៅកាន់ Modifier Groups
     * សម្រាប់ឲ្យដឹងថា Product នេះមានភ្ជាប់ក្រុមជម្រើសអ្វីខ្លះ (ឧទាហរណ៍៖ មានជាតិស្ករ និង ទឹកកក)
     */
    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'modifier_group_product', 'product_id', 'modifier_group_id');
    }

}