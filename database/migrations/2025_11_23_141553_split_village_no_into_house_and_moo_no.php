<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class SplitVillageNoIntoHouseAndMoo extends Migration
{
    public function up()
    {
        Schema::table('households', function (Blueprint $table) {
            // เพิ่มคอลัมน์ใหม่ (nullable ปลอดภัย)
            $table->string('house_no')->nullable()->after('village');
            $table->string('moo_no')->nullable()->after('house_no');
        });

        // ย้ายข้อมูลจาก village_no -> house_no / moo_no ด้วยการประมวลผลเป็นกลุ่ม (chunk)
        DB::table('households')->orderBy('id')->chunk(200, function ($rows) {
            foreach ($rows as $row) {
                $v = trim($row->village_no ?? '');
                $house_no = null;
                $moo_no = null;

                if ($v !== '') {
                    // ถ้ามี "/" เช่น "12/3" หรือ "12 / 3"
                    if (strpos($v, '/') !== false) {
                        [$a, $b] = array_map('trim', explode('/', $v, 2));
                        $house_no = $a ?: null;
                        $moo_no = $b ?: null;
                    }
                    // "หมู่ 3" หรือ "หมู่3"
                    elseif (preg_match('/หมู่[\s:.]*([0-9]+)/u', $v, $m)) {
                        $moo_no = $m[1];
                        if (preg_match('/^([0-9]+)/u', $v, $m2)) {
                            $house_no = $m2[1];
                        }
                    }
                    // "ม.3" หรือ "ม3"
                    elseif (preg_match('/\bม[.]{0,1}[:\s]*([0-9]+)/u', $v, $m)) {
                        $moo_no = $m[1];
                        if (preg_match('/^([0-9]+)/u', $v, $m2)) {
                            $house_no = $m2[1];
                        }
                    }
                    // มี "-" เช่น "12-3"
                    elseif (strpos($v, '-') !== false) {
                        [$a, $b] = array_map('trim', explode('-', $v, 2));
                        $house_no = $a ?: null;
                        $moo_no = $b ?: null;
                    }
                    // มีสองตัวเลขในสตริง -> แยกเป็นตัวแรก house ตัวที่สอง moo
                    elseif (preg_match_all('/([0-9]+)/', $v, $nums) && count($nums[0]) >= 2) {
                        $house_no = $nums[0][0];
                        $moo_no = $nums[0][1];
                    }
                    // มีตัวเลขหนึ่งตัว -> เก็บเป็น house_no
                    elseif (preg_match('/([0-9]+)/', $v, $num)) {
                        $house_no = $num[1];
                    }
                    // fallback เก็บทั้งสตริงไว้ใน house_no
                    else {
                        $house_no = $v;
                    }
                }

                DB::table('households')->where('id', $row->id)->update([
                    'house_no' => $house_no,
                    'moo_no' => $moo_no,
                ]);
            }
        });
    }

    public function down()
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn(['house_no', 'moo_no']);
        });
    }
}