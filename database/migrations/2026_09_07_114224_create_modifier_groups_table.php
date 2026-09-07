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
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ឈ្មោះក្រុម: ឧ. កម្រិតជាតិស្ករ, ទំហំកែវ, កម្រិតទឹកកក
            $table->enum('type', ['single', 'multiple'])->default('single'); // single = រើសបានតែមួយ (Radio), multiple = រើសបានច្រើន (Checkbox)
            $table->boolean('is_required')->default(false); // តម្រូវឲ្យភ្ញៀវរើសដាច់ខាតឬអត់? (true/false)
            $table->boolean('is_active')->default(true); // បិទ/បើក
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_groups');
    }
};
