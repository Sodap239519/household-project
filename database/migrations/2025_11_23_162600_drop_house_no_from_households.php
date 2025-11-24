<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropHouseNoFromHouseholds extends Migration
{
    public function up()
    {
        Schema::table('households', function (Blueprint $table) {
            if (Schema::hasColumn('households', 'house_no')) {
                $table->dropColumn('house_no');
            }
        });
    }

    public function down()
    {
        Schema::table('households', function (Blueprint $table) {
            if (!Schema::hasColumn('households', 'house_no')) {
                $table->string('house_no')->nullable()->after('village');
            }
        });
    }
}