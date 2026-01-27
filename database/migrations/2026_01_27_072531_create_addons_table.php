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
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ex: សាច់មាន់, ថែមស្ករ
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('type', ['food', 'drink'])->nullable(); // ដើម្បីងាយស្រួល Filter
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addons');
    }
};
