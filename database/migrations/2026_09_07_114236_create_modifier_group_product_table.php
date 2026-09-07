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
        Schema::create('modifier_group_product', function (Blueprint $table) {
            // ភ្ជាប់ Product ទៅកាន់ Modifier Group (ទំនាក់ទំនង Many-to-Many)
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('modifier_group_id')->constrained('modifier_groups')->cascadeOnDelete();
            
            // ការពារកុំឲ្យមានទិន្នន័យស្ទួន (Product មួយ មាន Modifier Group ដូចគ្នាច្រើនដង)
            $table->unique(['product_id', 'modifier_group_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_group_product');
    }
};
