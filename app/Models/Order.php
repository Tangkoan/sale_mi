<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 
        'table_id', 
        'user_id', 
        'status', 
        'total_amount', 
        'payment_method'
    ];

    // ទំនាក់ទំនង៖ Order មានមុខម្ហូបច្រើន (Items)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // ទំនាក់ទំនង៖ Order ជារបស់តុមួយ
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    // ទំនាក់ទំនង៖ អ្នកដែលបើកតុដំបូង (User A)
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}