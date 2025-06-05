<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'role' => '1',
            ],
            [
                'name' => '西　伶奈',
                'email' => 'reina.n@coachtech.com',
                'email_verified_at' => '2025-05-01 09:00:00',
                'password' => Hash::make('password123'),
                'role' => '0',
            ],
            [
                'name' => '山田　太郎',
                'email' => 'taro.y@coachtech.com',
                'email_verified_at' => '2025-05-01 09:00:00',
                'password' => Hash::make('password123'),
                'role' => '0',
            ],
            [
                'name' => '増田　一世',
                'email' => 'issei.m@coachtech.com',
                'email_verified_at' => '2025-05-01 09:00:00',
                'password' => Hash::make('password123'),
                'role' => '0',
            ],
            [
                'name' => '山本　敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'email_verified_at' => '2025-05-01 09:00:00',
                'password' => Hash::make('password123'),
                'role' => '0',
            ],
            [
                'name' => '秋田　朋美',
                'email' => 'tomomi.a@coachtech.com',
                'email_verified_at' => '2025-05-01 09:00:00',
                'password' => Hash::make('password123'),
                'role' => '0',
            ],
            [
                'name' => '中西　教夫',
                'email' => 'norio.n@coachtech.com',
                'email_verified_at' => '2025-05-01 09:00:00',
                'password' => Hash::make('password123'),
                'role' => '0',
            ],
        ];
        DB::table('users')->insert($users);

    }
}
