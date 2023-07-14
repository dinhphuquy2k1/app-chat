<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Enums\UserRole;
use App\Enums\UserActive;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'email' => 'phuquy@gmail.com',
                'user_type' => UserRole::CUSER,
                'avatar' => 'https://ui-avatars.com/api/?background=ff324d&color=fff&name=+p'
            ],
            [
                'email' => 'linh@gmail.com',
                'user_type' => UserRole::CUSER,
                'avatar' => 'https://ui-avatars.com/api/?background=ff324d&color=fff&name=+l'
            ],
            [
                'email' => 'quangtuyen101@gmail.com',
                'user_type' => UserRole::NUSER,
                'avatar' => 'https://ui-avatars.com/api/?background=f5bc42&color=fff&name=+t'
            ],
            [
                'email' => 'huytu101@gmail.com',
                'user_type' => UserRole::NUSER,
                'avatar' => 'https://ui-avatars.com/api/?background=4287f5&color=fff&name=+t'
            ],
            [
                'email' => 'vanthanh@gmail.com',
                'user_type' => UserRole::NUSER,
                'avatar' => 'https://ui-avatars.com/api/?background=f542d4&color=fff&name=+t'
            ]
        ];

        foreach($data as $item){
            User::create([
                'name' => substr($item['email'], 0, strpos($item['email'], '@')),
                'email' => $item['email'],
                'user_type' => $item['user_type'],
                'is_active' => UserActive::INACTIVE,
                'password' => \Hash::make('123456'),
                'avatar' => $item['avatar']
            ]);
        }
    }
}
