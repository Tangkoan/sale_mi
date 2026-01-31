<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('kitchen_destinations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ឈ្មោះផ្នែក (Wok, Soup, Bar)
            $table->string('printer_ip')->nullable(); // IP ម៉ាស៊ីនបោះពុម្ព (192.168.1.xxx)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_destinations');
    }
};
