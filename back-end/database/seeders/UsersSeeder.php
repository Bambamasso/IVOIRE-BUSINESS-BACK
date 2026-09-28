<?php

namespace Database\Seeders;

use App\Models\User;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = [
            ['first_name' => 'Boyo André', 'last_name' => 'KOUEDAN', 'civility' => 'Monsieur', 'username' => 'boyo', 'email' => 'intellectstores@gmail.com', 'number' => '0747721510', 'password' => 'boyo@2026', 'role' => 'admin'],
            ['first_name' => 'Mansogbê', 'last_name' => 'BAMABA', 'civility' => 'Mademoiselle', 'username' => 'masso', 'email' => 'bambamasso51@gmail.com', 'number' => '0554219929', 'password' => 'masso@2026', 'role' => 'super-admin'],
        ];

        foreach ($users as $user) {
            $existe = DB::table('users')
                ->where('username', $user['username'])
                ->orWhere('email', $user['email'])
                ->exists();
            if (!$existe) {
                $userModel = new User();
                $userModel->fill([
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'civility' => $user['civility'],
                    'email' => $user['email'],
                    'number' => $user['number'],
                    'password' => $user['password'],
                    'username' => $user['username'],
                ]);
                $userModel->save();

                $userModel->assignRole($user['role']);
            }
        }
    }
}
