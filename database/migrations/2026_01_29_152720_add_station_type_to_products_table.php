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
    Schema::table('products', function (Blueprint $table) {
        // យើងបង្កើត column ថ្មីដាក់ឈ្មោះ station_type
        // អាចដាក់ values: 'wok', 'soup', 'drink'
        $table->string('station_type')->default('wok')->after('price'); 
    });
}

public function down()
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn('station_type');
    });
}
};
