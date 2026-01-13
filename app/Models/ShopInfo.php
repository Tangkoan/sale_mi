<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopInfo extends Model
{
    protected $fillable = [
        'shop_en',
        'shop_kh',
        'description_en',
        'description_kh',
        'phone_number',
        'address_en',
        'address_kh',
        'logo',
        'fav',
        'note_kh',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
