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
    $roles = [
      ['name' => 'admin', 'title' => 'Administrateur', 'guard_name' => 'api'],
      ['name' => 'user', 'title' => 'Utilisateur', 'guard_name' => 'api'],
      ['name' => 'manager', 'title' => 'Superviseur', 'guard_name' => 'api']
    ];

    foreach ($roles as $role) {
      $existe = DB::table("roles")
        ->where("name", $role['name'])
        ->where("title", $role['title'])
        ->where("guard_name", $role['guard_name'])
        ->exists();
      if (!$existe) {
        DB::table("roles")->insert([
          "id" => Str::uuid(),
          "name" => $role['name'],
          "title" => $role['title'],
          "guard_name" => $role['guard_name'],
        ]);
      }
    }


  }
}
