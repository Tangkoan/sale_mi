<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status']; // status: available, busy

    // ទំនាក់ទំនង៖ 1 Table អាចមាន Orders ច្រើន (តាមប្រវត្តិ)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}