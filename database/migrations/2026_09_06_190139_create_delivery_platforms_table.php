<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ឈ្មោះ App (Foodpanda, WOWNOW...)
            $table->string('logo')->nullable(); // ទុកសម្រាប់ដាក់ឈ្មោះរូបភាព Logo (អាចទទេបាន)
            $table->enum('status', ['active', 'inactive'])->default('active'); // កំណត់បើក/បិទការបង្ហាញ
            $table->timestamps(); // វានឹងបង្កើត field created_at និង updated_at ឱ្យដោយស្វ័យប្រវត្តិ
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_platforms');
    }
};