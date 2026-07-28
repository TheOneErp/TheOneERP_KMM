<?php

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Page;
use App\Models\Verify;
use App\Models\VerifyLevel;
use App\Models\VerifyCondition;
use App\Models\Parmeter;
use App\Models\Language;
use App\Models\NotificationUser;
use App\Models\Notification;
use App\Models\NotificationSetting;

use App\Utils\MigrationUtil;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::insert([
            ['username' => 'yun', 'password' => Hash::make("yun"), 'name' => "Yun", 'user_disabled' => false],
            ['username' => 'lupin', 'password' => Hash::make("lupin"), 'name' => "Lupin", 'user_disabled' => false],
            ['username' => 'zyyyyy', 'password' => Hash::make("zyyyyy"), 'name' => "Zyyyyy", 'user_disabled' => false],
        ]);

        /* Group::insert([
            ['group_name' => '測試群組1'],
            ['group_name' => '測試群組2'],
            ['group_name' => '測試群組3']
        ]); */

        /* GroupUser::insert([
            ['group_id' => 1, 'user_id' => 1],
            ['group_id' => 1, 'user_id' => 2],
            ['group_id' => 2, 'user_id' => 3],
            ['group_id' => 2, 'user_id' => 4]
        ]); */

        /* $TestVerify = Verify::create(['page_id' => 3]);
        $TestVerifyLevel1_A = $TestVerify->verifyLevel()->create([
            'verify_level' => 1,
            'verify_target_id' => 0,
            'verify_target_type' => 'user',
            'verify_population' => 1,
        ]);
        $TestVerifyLevel1_B = $TestVerify->verifyLevel()->create([
            'verify_level' => 1,
            'verify_target_id' => 0,
            'verify_target_type' => 'user',
            'verify_population' => 1,
        ]);
        $TestVerifyLevel2_A = $TestVerify->verifyLevel()->create([
            'verify_level' => 2,
            'verify_target_id' => 0,
            'verify_target_type' => 'user',
            'verify_population' => 1,
        ]);
        $TestVerifyLevel1_A->verifyCondition()->createMany([
            ['verify_condition_group' => 0,'verify_logical' => "AND",'field_code' => "username",'verify_comparison' => "=",'verify_value' => "test001"]
        ]);
        $TestVerifyLevel1_B->verifyCondition()->createMany([
            ['verify_condition_group' => 0,'verify_logical' => "AND",'field_code' => "username",'verify_comparison' => "=",'verify_value' => "test001"]
        ]);
        $TestVerifyLevel2_A->verifyCondition()->createMany([
            ['verify_condition_group' => 0,'verify_logical' => "AND",'field_code' => "username",'verify_comparison' => "=",'verify_value' => "test001"]
        ]); */
    }
}
