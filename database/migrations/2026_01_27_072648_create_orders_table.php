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
        if (!Schema::hasTable('products')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('invoice_number')->unique(); // Ex: INV-2024001
                $table->foreignId('table_id')->constrained('tables');
                $table->foreignId('user_id')->constrained('users'); // User ដែលបើកតុដំបូង
                $table->enum('status', ['pending', 'completed', 'canceled'])->default('pending');
                $table->decimal('total_amount', 10, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
