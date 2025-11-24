<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Household;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HouseholdAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Household::query();
        if ($request->filled('province')) $query->where('province', $request->province);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        $households = $query->orderBy('created_at','desc')->paginate(25);
        return view('admin.households.index', compact('households'));
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'households_export_' . date('Ymd_His') . '.csv';
        $headers = [
            'รหัส','จังหวัด','อำเภอ','ตำบล','หมู่บ้าน','เลขที่/บ้านเลขที่','หมู่ที่',
            'คำนำหน้า','ชื่อ','นามสกุล','เพศ','อายุ','เบอร์โทร',
            'จำนวนสมาชิก','อาชีพหลัก','อาชีพเสริม',
            'รายได้/เดือน','รายจ่าย/เดือน','หนี้สิน',
            'มีพื้นที่เพาะเห็ด','ขนาด(ตร.ม.)','แหล่งน้ำ','ไฟฟ้า','ระยะทางจากตลาด(กม.)',
            'เคยทำเกษตร','เคยเพาะเห็ด','การใช้สมาร์ทโฟน','การใช้ Social Media',
            'ความสนใจ','เวลาที่ให้(ชม./สัปดาห์)','ทุนเริ่มต้น(บาท)',
            'สมาชิกกลุ่ม','ความพร้อมเข้าร่วมกลุ่ม',
            'poverty_score','motivation_score','experience_score','group_score','potential_score','area_score','market_score','total_score','priority','ผ่าน','วันที่บันทึก'
        ];

        $response = new StreamedResponse(function() use ($headers, $request) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            $query = Household::query();
            if ($request->filled('province')) $query->where('province', $request->province);
            if ($request->filled('priority')) $query->where('priority', $request->priority);
            $query->orderBy('id');

            $query->chunk(500, function($rows) use ($handle) {
                foreach ($rows as $row) {
                    $line = [];
                    $line[] = $row->id;
                    $line[] = $row->province;
                    $line[] = $row->district;
                    $line[] = $row->subdistrict;
                    $line[] = $row->village;
                    $line[] = $row->village_no; // ใช้ village_no
                    $line[] = $row->moo_no;
                    $line[] = $row->prefix;
                    $line[] = $row->first_name;
                    $line[] = $row->last_name;
                    $line[] = \App\Models\Household::genderLabel($row->gender);
                    $line[] = $row->age;
                    $phone = $row->phone ? trim($row->phone) : '';
                    if ($phone !== '' && preg_match('/^\d+$/', $phone)) {
                        $line[] = '="' . $phone . '"';
                    } else {
                        $line[] = $phone ? '="' . str_replace('"','""',$phone) . '"' : '';
                    }
                    $line[] = $row->household_members;
                    $line[] = $row->main_occupation;
                    $line[] = $row->extra_occupation;
                    $line[] = $row->income_month;
                    $line[] = $row->expense_month;
                    $line[] = $row->debt;
                    $line[] = \App\Models\Household::boolYesNo($row->has_mushroom_area);
                    $line[] = $row->mushroom_area_size;
                    $line[] = $row->water_source;
                    $line[] = \App\Models\Household::boolYesNo($row->has_electricity);
                    $line[] = $row->market_distance_km;
                    $line[] = \App\Models\Household::boolYesNo($row->ever_farmed);
                    $line[] = \App\Models\Household::boolYesNo($row->ever_mushroom);
                    $su = $row->smartphone_usage;
                    $suLabel = $su === 'use_well' ? 'ใช้เป็น' : ($su === 'use_some' ? 'ใช้ได้บ้าง' : 'ไม่ได้');
                    $line[] = $suLabel;
                    $line[] = $row->social_media ? 'ใช้' : 'ไม่ใช้';
                    $line[] = \App\Models\Household::interestLabel($row->interest_level);
                    $line[] = $row->available_hours_per_week;
                    $line[] = $row->initial_investment;
                    $line[] = $row->group_member ? 'ใช่' : 'ไม่ใช่';
                    $gr = $row->group_readiness;
                    $grLabel = $gr === 'ready' ? 'พร้อม' : ($gr === 'consider' ? 'พิจารณา' : ($gr === 'not_interested' ? 'ไม่สนใจ' : ''));
                    $line[] = $grLabel;
                    $line[] = $row->poverty_score;
                    $line[] = $row->motivation_score;
                    $line[] = $row->experience_score;
                    $line[] = $row->group_score;
                    $line[] = $row->potential_score;
                    $line[] = $row->area_score;
                    $line[] = $row->market_score;
                    $line[] = $row->total_score;
                    $line[] = $row->priority;
                    $line[] = $row->passed ? 'ใช่' : 'ไม่ใช่';
                    $line[] = $row->created_at ? $row->created_at->toDateTimeString() : '';

                    fputcsv($handle, $line);
                }
            });

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}