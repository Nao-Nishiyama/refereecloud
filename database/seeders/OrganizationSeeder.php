<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrganizationSeeder extends Seeder
{
    public function run()
    {
        $organizations = [
            ['short_name' => 'クラブ', 'full_name' => '京都クラブバレーボール連盟'],
            ['short_name' => '実業団', 'full_name' => '京都府実業団バレーボール連盟'],
            ['short_name' => 'ママさん', 'full_name' => '京都府ママさんバレーボール連盟'],
            ['short_name' => '大学', 'full_name' => '京都府大学バレーボール連盟'],
            ['short_name' => '高校', 'full_name' => '京都府高等学校体育連盟バレーボール専門部'],
            ['short_name' => '中学', 'full_name' => '京都府中学校体育連盟バレーボール専門部'],
            ['short_name' => '小学生', 'full_name' => '京都府小学生バレーボール連盟'],
            ['short_name' => 'ヤング', 'full_name' => '京都府ヤングクラブバレーボール連盟'],
            ['short_name' => 'ソフト', 'full_name' => '京都府ソフトバレーボール連盟'],
            ['short_name' => 'ビーチ', 'full_name' => '京都府バレーボール連盟'],
            ['short_name' => '京都市', 'full_name' => '京都市バレーボール協会'],
            ['short_name' => '宇治市', 'full_name' => '宇治市バレーボール協会'],
            ['short_name' => '城陽市', 'full_name' => '城陽市バレーボール協会'],
            ['short_name' => '長岡京市', 'full_name' => '長岡京市バレーボール協会'],
            ['short_name' => '亀岡市', 'full_name' => '亀岡市バレーボール協会'],
            ['short_name' => '南丹市', 'full_name' => '南丹市バレーボール協会'],
            ['short_name' => '綾部市', 'full_name' => '綾部市バレーボール協会'],
            ['short_name' => '福知山', 'full_name' => '福知山バレーボール協会'],
            ['short_name' => '舞鶴', 'full_name' => '舞鶴バレーボール協会'],
            ['short_name' => '与謝地方', 'full_name' => '与謝地方バレーボール協会'],
            ['short_name' => '京丹後市', 'full_name' => '京丹後市バレーボール協会'],
            ['short_name' => '京都府', 'full_name' => '京都府バレーボール協会'],
            ['short_name' => 'その他', 'full_name' => 'その他'],
        ];

        foreach ($organizations as $org) {
            DB::table('organizations')->insert([
                'short_name' => $org['short_name'],
                'full_name' => $org['full_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
