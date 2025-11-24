<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHouseholdsTable extends Migration
{
    public function up()
    {
        Schema::create('households', function (Blueprint $table) {
            $table->id();
            // Section A: ข้อมูลพื้นฐาน
            $table->string('prefix')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('education')->nullable();
            $table->string('health')->nullable();
            $table->unsignedSmallInteger('household_members')->nullable();
            $table->string('main_occupation')->nullable();
            $table->string('extra_occupation')->nullable();
            $table->decimal('income_month', 12, 2)->nullable();
            $table->decimal('expense_month', 12, 2)->nullable();
            $table->text('debt')->nullable(); // เก็บแหล่งเงินกู้หรือรายละเอียดหนี้
            
            // Section B: ความพร้อมด้านกายภาพ
            $table->boolean('has_mushroom_area')->default(false);
            $table->float('mushroom_area_size')->nullable(); // ตร.ม.
            $table->string('water_source')->nullable(); // ประปา/บ่อน้ำ/อื่นๆ
            $table->boolean('has_electricity')->default(false);
            $table->float('market_distance_km')->nullable();
            
            // Section C: ประสบการณ์และทักษะ
            $table->boolean('ever_farmed')->default(false);
            $table->boolean('ever_mushroom')->default(false);
            $table->enum('smartphone_usage', ['use_well','use_some','not_use'])->default('not_use');
            $table->boolean('social_media')->default(false);
            
            // Section D: ความสนใจและแรงจูงใจ
            $table->enum('interest_level', ['high','medium','low'])->nullable();
            $table->text('interest_reason')->nullable();
            $table->float('available_hours_per_week')->nullable();
            $table->decimal('initial_investment', 12, 2)->nullable();
            
            // Section E: การรวมกลุ่ม
            $table->boolean('group_member')->default(false);
            $table->enum('group_readiness', ['ready','consider','not_interested'])->nullable();
            
            // คะแนนแยกด้านและผลลัพธ์
            $table->decimal('poverty_score', 5, 2)->nullable();
            $table->decimal('motivation_score', 5, 2)->nullable();
            $table->decimal('experience_score', 5, 2)->nullable();
            $table->decimal('group_score', 5, 2)->nullable();
            $table->decimal('potential_score', 5, 2)->nullable();
            $table->decimal('area_score', 5, 2)->nullable();
            $table->decimal('market_score', 5, 2)->nullable();
            $table->decimal('total_score', 6, 2)->nullable(); // 0 - 100
            $table->enum('priority', ['A','B','C','D'])->nullable(); // A,B,C หรือ D=ไม่ผ่าน
            $table->boolean('passed')->default(false); // ผ่านเกณฑ์หรือไม่
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('households');
    }
}