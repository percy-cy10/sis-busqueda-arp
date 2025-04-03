<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $admin = Role::updateOrCreate(['name' => 'Administrador'], ['name' => 'Administrador', 'guard_name' => 'api']);
        $personal = Role::updateOrCreate(['name' => 'Operador'], ['name' => 'Operador', 'guard_name' => 'api']);
        Permission::updateOrCreate(['name' => 'admin-permisos'], [
            'name' => 'admin-permisos',
            'description' => 'Administrar Permisos'
        ])->assignRole([$admin, $personal]);
        Permission::updateOrCreate(['name' => 'admin-roles'], [
            'name' => 'admin-roles',
            'description' => 'Administrar Roles'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-usuarios'], [
            'name' => 'admin-usuarios',
            'description' => 'Administrar Usuarios'
        ])->assignRole([$admin]);

       
       
        Permission::updateOrCreate(['name' => 'admin-areas'], [
            'name' => 'admin-areas',
            'description' => 'Administrar Areas'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-busquedas'], [
            'name' => 'admin-busquedas',
            'description' => 'Administrar busquedas'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-verificaciones'], [
            'name' => 'admin-verificaciones',
            'description' => 'Administrar verificaciones'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-libro'], [
            'name' => 'admin-libro',
            'description' => 'Administrar libro'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-notario'], [
            'name' => 'admin-notario',
            'description' => 'Administrar notario'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-subseries'], [
            'name' => 'admin-subseries',
            'description' => 'Administrar subseries'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-subseries'], [
            'name' => 'admin-subseries',
            'description' => 'Administrar subseries'
        ])->assignRole([$admin]);

        ///anterior bd
        Permission::updateOrCreate(['name' => 'admin-anteriores'], [
            'name' => 'admin-anteriores',
            'description' => 'Administrar anteriores'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-anteriores2'], [
            'name' => 'admin-anteriores2',
            'description' => 'Administrar anteriores2'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-sis2018'], [
            'name' => 'admin-sis2018',
            'description' => 'Administrar sis2018'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-sis20182'], [
            'name' => 'admin-sis20182',
            'description' => 'Administrar sis20182'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-nuevo'], [
            'name' => 'admin-nuevo',
            'description' => 'Administrar nuevo'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-nuevo2'], [
            'name' => 'admin-nuevo2',
            'description' => 'Administrar nuevo2'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-sia'], [
            'name' => 'admin-sia',
            'description' => 'Administrar sia'
        ])->assignRole([$admin]);
        Permission::updateOrCreate(['name' => 'admin-arbolito'], [
            'name' => 'admin-arbolito',
            'description' => 'Administrar arbolito'
        ])->assignRole([$admin]);


        $user = User::create([
            'name' => 'password',
            'email' => 'password@gmail.com',
            'password' => bcrypt('password'),
            // 'area_id' => 5,
        ]);
        $operador = User::create([
            'name' => 'area',
            'email' => 'areacaja@gmail.com',
            'password' => bcrypt('areacaja'),
            'area_id'=> 1,
        ]);
        $operador = User::create([
            'name' => 'Juan Busqueda',
            'email' => 'busqueda@gmail.com',
            'password' => bcrypt('busqueda'),
            'area_id'=> 2,
        ]);
        $operador = User::create([
            'name' => 'Juan Verificacion',
            'email' => 'verificacion@gmail.com',
            'password' => bcrypt('verificacion'),
            'area_id'=> 3,
        ]);
        $operador = User::create([
            'name' => 'Juan Direccion',
            'email' => 'direccion@gmail.com',
            'password' => bcrypt('direccion'),
            'area_id'=> 4,
        ]);
        $user->assignRole('Administrador');
        $operador->assignRole('Operador');
    }
}
