<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $roles=[ 'admin'];
        foreach($roles as $role){
      DB::table("roles")->insert([
        "id"=>Str::uuid(),
          "name"=>$role,
          "guard_name"=>"web",
        ]);
        }
    
    }
}
