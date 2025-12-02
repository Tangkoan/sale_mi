<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If table doesn't exist, create it
        if (!Schema::hasTable('themes')) {
            Schema::create('themes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Link to user
                $table->string('mode')->default('light');
                $table->string('sidebar_style')->default('style-1'); // New Column
                $table->timestamps();
            });
        } 
        // If table exists, just add the column
        else {
            Schema::table('themes', function (Blueprint $table) {
                if (!Schema::hasColumn('themes', 'sidebar_style')) {
                    $table->string('sidebar_style')->default('style-1')->after('mode');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('themes', function (Blueprint $table) {
            if (Schema::hasColumn('themes', 'sidebar_style')) {
                $table->dropColumn('sidebar_style');
            }
        });
    }
};