<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationAndGenderToHouseholdsTable extends Migration
{
    public function up()
    {
        Schema::table('households', function (Blueprint $table) {
            $table->string('province')->nullable()->after('id');
            $table->string('district')->nullable()->after('province');
            $table->string('subdistrict')->nullable()->after('district');
            $table->string('village')->nullable()->after('subdistrict');
            $table->string('village_no')->nullable()->after('village');
            $table->string('phone')->nullable()->after('last_name');
            $table->enum('gender', ['male','female','other'])->nullable()->after('age');
            $table->index(['province','district','subdistrict']);
        });
    }

    public function down()
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropIndex(['province','district','subdistrict']);
            $table->dropColumn(['province','district','subdistrict','village','village_no','phone','gender']);
        });
    }
}