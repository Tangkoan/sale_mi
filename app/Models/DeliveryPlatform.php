<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DeliveryPlatform extends Model
{

    use HasFactory;

    protected $table = 'delivery_platforms';
    //
    protected $guarded = [];
}
