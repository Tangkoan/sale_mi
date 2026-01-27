<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Save តម្លៃលក់ជាក់ស្តែង
            $table->text('note')->nullable(); // Ex: មិនដាក់ប៊ីចេង
            
            // សំខាន់សម្រាប់ Printer & Multi-User
            $table->boolean('is_printed')->default(false); 
            $table->foreignId('created_by')->constrained('users'); // User ដែលចុចកុម្ម៉ង់មុខម្ហូបនេះ
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
