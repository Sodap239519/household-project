<?php

namespace App\Services;

class ScoreCalculator
{
    /**
     * คำนวณคะแนนและระบุ priority/passed
     * รับ input array (เช่น validated request->all())
     * คืนค่า array ที่มี keys: poverty_score,motivation_score,experience_score,group_score,potential_score,area_score,market_score,total,priority,passed
     */
    public static function calculate(array $data): array
    {
        // 1. poverty_score (0-100) - ถ้ารายได้ต่ำ => คะแนนสูง (ยากจนมาก)
        $povertyScore = 0;
        if (isset($data['income_month']) || isset($data['expense_month'])) {
            $income = floatval($data['income_month'] ?? 0);
            $expense = floatval($data['expense_month'] ?? 0);
            $net = max(0, $income - $expense);
            // ปรับเกณฑ์: net <=0 => 100, net >=25000 => 0, linear between
            $povertyScore = max(0, min(100, 100 - ($net / 25000) * 100));
        }

        // 2. motivation_score: interest_level (high=100, medium=60, low=20) และ available_hours_per_week เพิ่มน้ำหนัก
        $motivationScore = 0;
        if (!empty($data['interest_level'])) {
            $map = ['high'=>100,'medium'=>60,'low'=>20];
            $motivationScore = $map[$data['interest_level']] ?? 0;
            if (!empty($data['available_hours_per_week'])) {
                $hours = floatval($data['available_hours_per_week']);
                // ให้คะแนนชั่วโมง: 0-40 maps to 0-100
                $hoursScore = min(100, ($hours / 40) * 100);
                // รวม 70% interest + 30% hours
                $motivationScore = ($motivationScore * 0.7) + ($hoursScore * 0.3);
            }
        }

        // 3. experience_score: ให้คะแนนจากเคยทำเกษตร/เพาะเห็ด/ทักษะสมาร์ทโฟน
        $expPoints = 0;
        if (!empty($data['ever_farmed'])) $expPoints += 40;
        if (!empty($data['ever_mushroom'])) $expPoints += 30;
        if (!empty($data['smartphone_usage'])) {
            $smMap = ['use_well'=>30,'use_some'=>10,'not_use'=>0];
            $expPoints += $smMap[$data['smartphone_usage']] ?? 0;
        }
        $experienceScore = min(100, $expPoints);

        // 4. group_score: สมาชิกกลุ่ม + ความพร้อม
        $groupScore = 0;
        if (!empty($data['group_member'])) $groupScore += 60;
        if (!empty($data['group_readiness'])) {
            $grMap = ['ready'=>40,'consider'=>20,'not_interested'=>0];
            $groupScore += $grMap[$data['group_readiness']] ?? 0;
        }
        $groupScore = min(100, $groupScore);

        // 5. potential_score: initial_investment + health (good) + main occupation stability
        $potentialScore = 0;
        if (!empty($data['initial_investment'])) {
            $potentialScore += min(80, (floatval($data['initial_investment']) / 20000) * 100); // scale to 0-80
        }
        if (!empty($data['health']) && strtolower($data['health']) == 'good') $potentialScore += 20;
        $potentialScore = min(100, $potentialScore);

        // 6. area_score: พื้นที่เพาะเห็ด + ไฟฟ้า + น้ำ
        $areaScore = 0;
        if (!empty($data['has_mushroom_area'])) {
            $areaScore += 50;
            $areaSize = floatval($data['mushroom_area_size'] ?? 0);
            // เพิ่มตามขนาด: ทุก 10 ตร.ม. ให้ 5 คะแนน (จนถึง +40)
            $areaScore += min(40, ($areaSize/10)*5);
        }
        if (!empty($data['has_electricity'])) $areaScore += 20;
        if (!empty($data['water_source']) && in_array($data['water_source'], ['tap','well'])) $areaScore += 10;
        $areaScore = min(100, $areaScore);

        // 7. market_score: ระยะทางจากตลาด (ใกล้ -> คะแนนสูง)
        $marketScore = 0;
        if (isset($data['market_distance_km'])) {
            $d = floatval($data['market_distance_km']);
            // 0 km => 100, 50 km => 0, linear
            $marketScore = max(0, min(100, (1 - min($d,50)/50) * 100));
        }

        // รวมคะแนนตามสูตรที่ให้มา
        $total = (
            ($povertyScore * 0.25) +
            ($motivationScore * 0.20) +
            ($experienceScore * 0.15) +
            ($groupScore * 0.15) +
            ($potentialScore * 0.10) +
            ($areaScore * 0.10) +
            ($marketScore * 0.05)
        );

        // ลำดับ Priority (A,B,C,D)
        $priority = 'D';
        $passed = false;
        if ($total >= 80) { $priority = 'A'; $passed = true; }
        elseif ($total >= 70) { $priority = 'B'; $passed = true; }
        elseif ($total >= 60) { $priority = 'C'; $passed = true; }
        else { $priority = 'D'; $passed = false; }

        return [
            'poverty_score' => round($povertyScore,2),
            'motivation_score' => round($motivationScore,2),
            'experience_score' => round($experienceScore,2),
            'group_score' => round($groupScore,2),
            'potential_score' => round($potentialScore,2),
            'area_score' => round($areaScore,2),
            'market_score' => round($marketScore,2),
            'total_score' => round($total,2),
            'priority' => $priority,
            'passed' => $passed
        ];
    }
}