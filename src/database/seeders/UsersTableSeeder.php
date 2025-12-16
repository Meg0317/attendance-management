<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            // ----- 一般ユーザー（社員）6名 -----
            // ※ 西 伶奈のみ、ログイン検証用としてメール認証済み
            [
                'name' => '西 伶奈',
                'email' => 'reina.n@coachtech.com',
                'role' => 2,
                'email_verified_at' => now(),
            ],
            [
                'name' => '山田 太郎',
                'email' => 'taro.y@coachtech.com',
                'role' => 2,
                'email_verified_at' => null,
            ],
            [
                'name' => '増田 一世',
                'email' => 'issei.m@coachtech.com',
                'role' => 2,
                'email_verified_at' => null,
            ],
            [
                'name' => '秋田 朋美',
                'email' => 'tomomi.a@coachtech.com',
                'role' => 2,
                'email_verified_at' => null,
            ],
            [
                'name' => '中西 教夫',
                'email' => 'norio.n@coachtech.com',
                'role' => 2,
                'email_verified_at' => null,
            ],
            [
                'name' => '山本 敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'role' => 2,
                'email_verified_at' => null,
            ],

            // ----- 管理者（認証済み） -----
            [
                'name' => '管理者',
                'email' => 'admin@example.com',
                'role' => 1,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'role' => $user['role'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => $user['email_verified_at'],
                ]
            );
        }
    }
}

