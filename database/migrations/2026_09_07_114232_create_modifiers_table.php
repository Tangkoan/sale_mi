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
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modifier_group_id')->constrained('modifier_groups')->cascadeOnDelete(); // ភ្ជាប់ទៅក្រុម
            $table->string('name'); // ជម្រើស: ឧ. ០%, ៥០%, ១០០% 
            $table->decimal('price', 10, 2)->default(0); // តម្លៃដែលត្រូវបូកថែម (បើជាតិស្ករគឺ 0, បើទំហំកែវ L អាចបូក 2000៛)
            $table->boolean('is_active')->default(true); // បិទ/បើក
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifiers');
    }
};
