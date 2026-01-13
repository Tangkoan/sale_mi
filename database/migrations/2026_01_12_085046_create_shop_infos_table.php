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
        Schema::create('shop_infos', function (Blueprint $table) {
            $table->id();
            $table->string('shop_en')->nullable();
            $table->string('shop_kh')->nullable();
            $table->text('description_en')->nullable(); // កែពី describetion
            $table->text('description_kh')->nullable(); // កែពី describetion
            $table->string('phone_number')->nullable();
            $table->text('address_en')->nullable();
            $table->text('address_kh')->nullable();
            $table->string('logo')->nullable(); // Store image path
            $table->string('fav')->nullable();  // Store favicon path
            $table->text('note_kh')->nullable();
            $table->boolean('status')->default(1); // 1=Active, 0=Inactive
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_infos');
    }
};
