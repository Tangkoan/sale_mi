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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code')->default('KHR'); // KHR
            $table->decimal('rate', 10, 2); // 4100.00
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert Default Data ភ្លាមៗ
        DB::table('exchange_rates')->insert([
            'currency_code' => 'KHR',
            'rate' => 4100,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
