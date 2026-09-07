<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    use HasFactory;

    protected $table = 'modifier_groups';

    protected $fillable = [
        'name',
        'type',
        'is_required',
        'is_active',
    ];

    // បំប្លែងទិន្នន័យ (Casts) ទៅជាទម្រង់ Boolean (true/false) ដើម្បីងាយស្រួលប្រើ
    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    /**
     * ទំនាក់ទំនង មួយ-ទៅ-ច្រើន (One-to-Many) ទៅកាន់ Modifiers
     * ឧទាហរណ៍៖ ក្រុម "កម្រិតជាតិស្ករ" មានជម្រើស "0%", "50%", "100%"
     */
    public function modifiers()
    {
        return $this->hasMany(Modifier::class, 'modifier_group_id');
    }

    /**
     * ទំនាក់ទំនង ច្រើន-ទៅ-ច្រើន (Many-to-Many) ទៅកាន់ Products
     * ឧទាហរណ៍៖ ក្រុម "កម្រិតជាតិស្ករ" ត្រូវបានកំណត់ឱ្យបង្ហាញលើ "កាហ្វេ" និង "តែបៃតង"
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'modifier_group_product', 'modifier_group_id', 'product_id');
    }
}