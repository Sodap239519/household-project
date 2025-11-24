<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * หน้า dashboard (view)
     */
    public function index()
    {
        return view('dashboard.index');
    }

    /**
     * JSON: จำนวนตามพื้นที่ (level = province|district|subdistrict|village)
     * คืนค่า: [{ area: "นครราชสีมา", total: 10, passed:5, failed:5 }, ...]
     */
    public function byArea(Request $request)
    {
        $level = $request->get('level','district'); // default district
        $allowed = ['province','district','subdistrict','village'];
        if (!in_array($level, $allowed)) $level = 'district';

        $rows = Household::select(
                DB::raw("$level as area"),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed'),
                DB::raw('SUM(CASE WHEN passed = 0 THEN 1 ELSE 0 END) as failed')
            )
            ->groupBy('area')
            ->orderBy('total','desc')
            ->limit(100)
            ->get();

        $mapped = $rows->map(function($r){
            $area = $r->area ?: 'ไม่ระบุ';
            return [
                'area' => $area,
                'total' => (int)$r->total,
                'passed' => (int)$r->passed,
                'failed' => (int)$r->failed,
            ];
        });

        return response()->json($mapped);
    }

    /**
     * JSON: เพศ (male/female/other/null => แปลงเป็น label)
     * คืนค่า: [{ gender: "ชาย", total: 3 }, ...]
     */
    public function byGender()
    {
        $rows = Household::select(
                DB::raw("COALESCE(NULLIF(gender,''),'ไม่ระบุ') as gender_label"),
                DB::raw('count(*) as total')
            )
            ->groupBy('gender_label')
            ->orderBy('total','desc')
            ->get();

        $mapped = $rows->map(function($r){
            $label = $r->gender_label;
            if ($label === 'male') $label = 'ชาย';
            elseif ($label === 'female') $label = 'หญิง';
            elseif ($label === 'other') $label = 'อื่นๆ';
            // keep 'ไม่ระบุ' as is
            return ['gender' => $label, 'total' => (int)$r->total];
        });

        return response()->json($mapped);
    }

    /**
     * JSON: ช่วงอายุ (0-17,18-34,35-49,50-64,65+,ไม่ระบุ)
     * คืนค่า: [{ range: "18-34", total: 5 }, ...] — คืนทุกช่วงแม้เป็น 0
     */
    public function byAgeRange()
    {
        // Query กลุ่มตามช่วงอายุ (ใช้ alias age_range เพื่อความปลอดภัย)
        $rows = DB::table('households')
            ->selectRaw("
                CASE
                    WHEN age IS NULL THEN 'ไม่ระบุ'
                    WHEN age < 18 THEN '0-17'
                    WHEN age BETWEEN 18 AND 34 THEN '18-34'
                    WHEN age BETWEEN 35 AND 49 THEN '35-49'
                    WHEN age BETWEEN 50 AND 64 THEN '50-64'
                    ELSE '65+'
                END as age_range,
                count(*) as total
            ")
            ->groupBy('age_range')
            ->get()
            ->keyBy('age_range'); // ให้ index ด้วย age_range สำหรับการแมป

        // ช่วงอายุที่ต้องการให้แสดงเสมอ
        $allRanges = ['0-17','18-34','35-49','50-64','65+','ไม่ระบุ'];

        // สร้างผลลัพธ์เต็มชุด (เติม 0 ถ้าไม่มีข้อมูล)
        $result = [];
        foreach ($allRanges as $r) {
            if (isset($rows[$r])) {
                $result[] = ['range' => $r, 'total' => (int)$rows[$r]->total];
            } else {
                $result[] = ['range' => $r, 'total' => 0];
            }
        }

        return response()->json($result);
    }

    /**
     * JSON: รายได้/รายจ่าย/หนี้เฉลี่ยต่อจังหวัด + poverty_line (จาก config)
     * คืนค่า: [{ province: 'นครราชสีมา', avg_income: 5000, avg_expense: 6000, avg_debt: 2000, poverty_line: 7500 }, ...]
     */
    public function financesByProvince()
    {
        $rows = Household::select('province',
            DB::raw('AVG(income_month) as avg_income'),
            DB::raw('AVG(expense_month) as avg_expense'),
            // try to cast debt numeric; if not numeric use 0
            DB::raw('AVG(CASE WHEN CAST(debt AS SIGNED) IS NULL THEN 0 ELSE CAST(debt AS SIGNED) END) as avg_debt')
        )->groupBy('province')->orderBy('avg_income','desc')->limit(100)->get();

        $poverty = config('poverty.lines', []);

        $mapped = $rows->map(function($r) use ($poverty) {
            $prov = $r->province ?: 'ไม่ระบุ';
            $line = isset($poverty[$r->province]) ? floatval($poverty[$r->province]) : null;
            return [
                'province' => $prov,
                'avg_income' => round(floatval($r->avg_income),2),
                'avg_expense' => round(floatval($r->avg_expense),2),
                'avg_debt' => round(floatval($r->avg_debt),2),
                'poverty_line' => $line
            ];
        });

        return response()->json($mapped);
    }

    /**
     * JSON: สรุปสถานะระบบ (total, passed, failed)
     */
    public function statusSummary()
    {
        $total = Household::count();
        $passed = Household::where('passed',1)->count();
        $failed = $total - $passed;
        return response()->json([
            'total' => $total,
            'passed' => $passed,
            'failed' => $failed
        ]);
    }
}