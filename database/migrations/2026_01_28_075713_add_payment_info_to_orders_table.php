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
       Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->nullable(); // cash, qr, card
            $table->decimal('received_amount', 10, 2)->default(0); // លុយទទួលពីភ្ញៀវ
            $table->decimal('change_amount', 10, 2)->default(0);   // លុយអាប់
            $table->timestamp('paid_at')->nullable();     // ម៉ោងបង់លុយ
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
