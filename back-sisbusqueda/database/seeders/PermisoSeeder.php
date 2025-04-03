<?php

namespace Database\Seeders;

use App\Models\User;
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
        // Crear roles
        $admin = Role::updateOrCreate(['name' => 'Administrador'], ['guard_name' => 'api']);
        $operador = Role::updateOrCreate(['name' => 'Operador'], ['guard_name' => 'api']);

        // Permisos agrupados en un array para evitar repeticiones
        $permisos = [
            ['name' => 'admin-permisos', 'description' => 'Administrar Permisos', 'roles' => [$admin]],
            ['name' => 'admin-roles', 'description' => 'Administrar Roles', 'roles' => [$admin]],
            ['name' => 'admin-usuarios', 'description' => 'Administrar Usuarios', 'roles' => [$admin]],
            ['name' => 'admin-areas', 'description' => 'Administrar Áreas', 'roles' => [$admin]],
            ['name' => 'admin-busquedas', 'description' => 'Administrar Búsquedas', 'roles' => [$admin]],
            ['name' => 'admin-verificaciones', 'description' => 'Administrar Verificaciones', 'roles' => [$admin]],
            ['name' => 'admin-libro', 'description' => 'Administrar Libro', 'roles' => [$admin]],
            ['name' => 'admin-notario', 'description' => 'Administrar Notario', 'roles' => [$admin]],
            ['name' => 'admin-subseries', 'description' => 'Administrar Subseries', 'roles' => [$admin]],
            ['name' => 'admin-solicitudes', 'description' => 'Administrar Solicitudes', 'roles' => [$admin]],
            ['name' => 'admin-registro_busquedas', 'description' => 'Administrar Registro de Búsquedas', 'roles' => [$admin]],
            ['name' => 'admin-registro_verificaciones', 'description' => 'Administrar Registro de Verificaciones', 'roles' => [$admin]],
            // Base de datos anterior
            ['name' => 'admin-anteriores', 'description' => 'Administrar Anteriores', 'roles' => [$admin]],
            ['name' => 'admin-anteriores2', 'description' => 'Administrar Anteriores 2', 'roles' => [$admin]],
            ['name' => 'admin-sis2018', 'description' => 'Administrar SIS2018', 'roles' => [$admin]],
            ['name' => 'admin-sis20182', 'description' => 'Administrar SIS20182', 'roles' => [$admin]],
            ['name' => 'admin-nuevo', 'description' => 'Administrar Nuevo', 'roles' => [$admin]],
            ['name' => 'admin-nuevo2', 'description' => 'Administrar Nuevo2', 'roles' => [$admin]],
            ['name' => 'admin-sia', 'description' => 'Administrar SIA', 'roles' => [$admin]],
            ['name' => 'admin-arbolito', 'description' => 'Administrar Arbolito', 'roles' => [$admin]],
        ];

        // Crear permisos y asignarlos a roles
        foreach ($permisos as $permiso) {
            Permission::updateOrCreate(['name' => $permiso['name']], [
                'description' => $permiso['description']
            ])->assignRole($permiso['roles']);
        }

        // Crear usuarios
        $usuarios = [
            ['name' => 'Administrador', 'email' => 'admin@gmail.com', 'password' => 'password', 'role' => $admin],
            ['name' => 'Area Caja', 'email' => 'areacaja@gmail.com', 'password' => 'areacaja', 'area_id' => 1, 'role' => $operador],
            ['name' => 'Juan Búsqueda', 'email' => 'busqueda@gmail.com', 'password' => 'busqueda', 'area_id' => 2, 'role' => $operador],
            ['name' => 'Juan Verificación', 'email' => 'verificacion@gmail.com', 'password' => 'verificacion', 'area_id' => 3, 'role' => $operador],
            ['name' => 'Juan Dirección', 'email' => 'direccion@gmail.com', 'password' => 'direccion', 'area_id' => 4, 'role' => $operador],
        ];

        foreach ($usuarios as $data) {
            $user = User::updateOrCreate(['email' => $data['email']], [
                'name' => $data['name'],
                'password' => bcrypt($data['password']),
                'area_id' => $data['area_id'] ?? null,
            ]);
            $user->assignRole($data['role']);
        }
    }
}
// Compare this snippet from app/Models/User.php:
