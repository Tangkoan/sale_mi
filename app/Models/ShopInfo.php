<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopInfo extends Model
{

    protected $table = 'shop_infos'; // ឈ្មោះ Table របស់អ្នក
    protected $guarded = []; // អនុញ្ញាតអោយកែគ្រប់ field
    // protected $fillable = [
    //     'shop_en',
    //     'shop_kh',
    //     'description_en',
    //     'description_kh',
    //     'phone_number',
    //     'address_en',
    //     'address_kh',
    //     'logo',
    //     'fav',
    //     'note_kh',
    //     'status',
    // ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
