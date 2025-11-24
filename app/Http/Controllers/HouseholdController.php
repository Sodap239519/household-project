<?php

namespace App\Http\Controllers;

use App\Models\Household;
use Illuminate\Http\Request;
use App\Services\ScoreCalculator;

class HouseholdController extends Controller
{
    public function create()
    {
        return view('households.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prefix'=>'nullable|string|max:10',
            'first_name'=>'required|string|max:100',
            'last_name'=>'required|string|max:100',
            'age'=>'nullable|integer|min:0|max:150',
            'gender'=>'nullable|in:male,female,other',
            'province'=>'nullable|string|max:100',
            'district'=>'nullable|string|max:100',
            'subdistrict'=>'nullable|string|max:100',
            'village'=>'nullable|string|max:100',
            'village_no'=>'nullable|string|max:50',
            'moo_no'=>'nullable|string|max:50',
            'phone'=>'nullable|string|max:30',
            'education'=>'nullable|string|max:100',
            'health'=>'nullable|string|max:255',
            'household_members'=>'nullable|integer|min:1',
            'main_occupation'=>'nullable|string|max:255',
            'extra_occupation'=>'nullable|string|max:255',
            'income_month'=>'nullable|numeric|min:0',
            'expense_month'=>'nullable|numeric|min:0',
            'debt'=>'nullable|string',
            'has_mushroom_area'=>'boolean',
            'mushroom_area_size'=>'nullable|numeric|min:0',
            'water_source'=>'nullable|string|max:50',
            'has_electricity'=>'boolean',
            'market_distance_km'=>'nullable|numeric|min:0',
            'ever_farmed'=>'boolean',
            'ever_mushroom'=>'boolean',
            'smartphone_usage'=>'nullable|in:use_well,use_some,not_use',
            'social_media'=>'boolean',
            'interest_level'=>'nullable|in:high,medium,low',
            'interest_reason'=>'nullable|string',
            'available_hours_per_week'=>'nullable|numeric|min:0',
            'initial_investment'=>'nullable|numeric|min:0',
            'group_member'=>'boolean',
            'group_readiness'=>'nullable|in:ready,consider,not_interested',
        ]);

        // คำนวณคะแนน
        $scores = ScoreCalculator::calculate($data);

        $saveData = array_merge($data, [
            'poverty_score' => $scores['poverty_score'],
            'motivation_score' => $scores['motivation_score'],
            'experience_score' => $scores['experience_score'],
            'group_score' => $scores['group_score'],
            'potential_score' => $scores['potential_score'],
            'area_score' => $scores['area_score'],
            'market_score' => $scores['market_score'],
            'total_score' => $scores['total_score'],
            'priority' => $scores['priority'],
            'passed' => $scores['passed'] ? 1 : 0,
        ]);

        $household = Household::create($saveData);

        return redirect()->route('admin.households.index')
            ->with('success','บันทึกข้อมูลเรียบร้อยแล้ว');
    }

    public function show(Household $household)
    {
        return view('households.show', compact('household'));
    }
}