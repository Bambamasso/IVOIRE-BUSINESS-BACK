<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $permissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',

            'view products',
            'create products',
            'edit products',
            'delete products',
             
            'view permissions',
            'create permissions',
            'edit permissions',
            'delete permissions'
        ];

        foreach($permissions as $permission){
         Permission::create([
          "name"=>$permission,
          "guard_name"=>"api"
         ]);
        }
    }
}
