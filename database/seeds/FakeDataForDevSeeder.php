<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/04/2019
 * Time: 13:43
 */

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class FakeDataForDevSeeder extends Seeder
{
    public function run()
    {
        $this->insertUser();
        $this->insertAdmin();
    }
    
    protected function insertUser()
    {
        $users = [
            [
                'phone'    => '13700000001',
                'nickname' => '测试1',
                'motto'    => '我是测试账号1',
                'birthday' => '1990-01-01',
            ],
            [
                'phone'    => '13700000002',
                'nickname' => '测试2',
                'motto'    => '我是测试账号2',
                'birthday' => '1990-01-01',
            ],
            [
                'phone'    => '13700000003',
                'nickname' => '测试3',
                'motto'    => '我是测试账号3',
                'birthday' => '1990-01-01',
            ],
            [
                'phone'    => '13700000004',
                'nickname' => '测试4',
                'motto'    => '我是测试账号4',
                'birthday' => '1990-01-01',
            ],
        ];
        
        User::query()->insert($users);
    }
    
    
    protected function insertAdmin()
    {
        Admin::query()->create([
            'phone' => '13812345678',
            'name'  => 'admin',
        ]);
    }
}
