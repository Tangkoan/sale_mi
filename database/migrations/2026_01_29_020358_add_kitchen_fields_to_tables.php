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
        // 1. បន្ថែម Status និង is_printed ទៅក្នុង order_items
        Schema::table('order_items', function (Blueprint $table) {
            // status: pending (ចាំធ្វើ), cooking (កំពុងធ្វើ), ready (ធ្វើរួច), served (លើកជូនភ្ញៀវ)
            $table->string('status')->default('pending')->after('price'); 
            
            // is_printed: 0 (មិនទាន់ Print/Send), 1 (Send រួចហើយ)
            // យើងមាន field នេះស្រាប់ក្នុង controller មុន ប៉ុន្តែត្រូវប្រាកដថាវានៅក្នុង DB ដែរ
            if (!Schema::hasColumn('order_items', 'is_printed')) {
                $table->boolean('is_printed')->default(false)->after('note');
            }
        });

        // 2. បន្ថែម Destination ទៅក្នុង categories
        // ដើម្បីដឹងថា Category នេះជារបស់ ផ្ទះបាយ (kitchen) ឬ បារ (bar)
        Schema::table('categories', function (Blueprint $table) {
            $table->string('destination')->default('kitchen')->after('name'); 
        });
    }

    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['status']);
            // $table->dropColumn(['is_printed']); // ប្រយ័ត្នលុបខុស បើវាមានពីមុន
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['destination']);
        });
    }

};
