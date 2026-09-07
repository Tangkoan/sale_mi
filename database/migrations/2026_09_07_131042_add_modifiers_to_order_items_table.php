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
        Schema::table('order_items', function (Blueprint $table) {
            // បន្ថែម Field modifiers ជាប្រភេទ JSON ដើម្បីងាយស្រួលផ្ទុកទិន្នន័យ Array
            $table->json('modifiers')->nullable()->after('note'); 
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('modifiers');
        });
    }
};
