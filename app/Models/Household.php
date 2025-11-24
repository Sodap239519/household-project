<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Household extends Model
{
    protected $fillable = [
        'prefix','first_name','last_name','age','gender','province','district','subdistrict','village','village_no','moo_no','phone',
        'education','health','household_members',
        'main_occupation','extra_occupation','income_month','expense_month','debt',
        'has_mushroom_area','mushroom_area_size','water_source','has_electricity','market_distance_km',
        'ever_farmed','ever_mushroom','smartphone_usage','social_media',
        'interest_level','interest_reason','available_hours_per_week','initial_investment',
        'group_member','group_readiness',
        'poverty_score','motivation_score','experience_score','group_score','potential_score','area_score','market_score',
        'total_score','priority','passed'
    ];

    protected $casts = [
        'has_mushroom_area' => 'boolean',
        'has_electricity' => 'boolean',
        'ever_farmed' => 'boolean',
        'ever_mushroom' => 'boolean',
        'social_media' => 'boolean',
        'group_member' => 'boolean',
        'income_month' => 'decimal:2',
        'expense_month' => 'decimal:2',
        'initial_investment' => 'decimal:2',
        'age' => 'integer',
        'household_members' => 'integer',
        'mushroom_area_size' => 'float',
        'market_distance_km' => 'float',
        'available_hours_per_week' => 'float',
    ];

    public static function genderLabel($val)
    {
        if ($val === 'male') return 'ชาย';
        if ($val === 'female') return 'หญิง';
        if ($val === 'other') return 'อื่นๆ';
        return 'ไม่ระบุ';
    }

    public static function interestLabel($val)
    {
        if ($val === 'high') return 'มาก';
        if ($val === 'medium') return 'ปานกลาง';
        if ($val === 'low') return 'น้อย';
        return '';
    }

    public static function boolYesNo($val, $useThaiUse=false)
    {
        $yes = $useThaiUse ? 'ใช้' : 'ใช่';
        $no = $useThaiUse ? 'ไม่ใช้' : 'ไม่ใช่';
        return $val ? $yes : $no;
    }
}