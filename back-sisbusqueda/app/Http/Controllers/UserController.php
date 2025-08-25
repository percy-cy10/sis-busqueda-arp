<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            User::query(),
            [],
            ['id', 'name', 'email', 'dni', 'nivel', 'estado'],
            ['id', 'name', 'email', 'dni', 'nivel', 'estado']
        );
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'dni' => $request->dni,               // ✅ Nuevo
            'nivel_id' => $request->nivel,           // ✅ Nuevo
            'area_id' => $request->area_id,
            'password' => bcrypt($request->password),
            'estado' => $request->estado ?? 1,
        ]);

        $user->syncRoles($request->rolesSelected);

        return response()->json([$user->save()], 201);
    }

    public function show(User $usuario)
    {
        return response()->json([
            'user' => $usuario,
            'rolesSelected' => $usuario->roles->pluck('id'),
            'estado' => $usuario->estado,
        ]);
    }

    public function update(StoreUserRequest $request, User $usuario)
    {
        $usuario->syncRoles($request->rolesSelected);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'dni' => $request->dni,               // ✅ Nuevo
            'nivel_id' => $request->nivel,           // ✅ Nuevo
            'area_id' => $request->area_id,
            'password' => $request->password ? bcrypt($request->password) : $usuario->password,
            'estado' => $request->estado ?? $usuario->estado,
        ]);

        return response()->json($usuario);
    }

    public function destroy(User $usuario)
    {
        return response()->json($usuario->delete());
    }

    public function toggleEstado(User $usuario)
    {
        $usuario->updateQuietly(['estado' => !$usuario->estado]);
        return response()->json([
            'message' => 'Estado actualizado',
            'estado' => $usuario->estado
        ], 200);
    }
}

