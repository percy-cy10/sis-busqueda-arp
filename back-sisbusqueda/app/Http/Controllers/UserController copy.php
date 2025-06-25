<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserRequest;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        return $this->generateViewSetList(
            $request,
            User::query(),
            [],
            ['id', 'name', 'estado'],
            ['id', 'name', 'estado']
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        //
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'area_id'=>$request->area_id,
            'password' => bcrypt($request->password),

            'estado' => $request->estado ?? 1, // Valor por defecto 1 si no se envía
        ]);
        $user->syncRoles($request->rolesSelected);
        return response()->json([$user->save()], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $usuario)
    {
        //
        // return $usuario->id;

        return response()->json([
            'user' => $usuario,
            'rolesSelected' => $usuario->roles->pluck('id'),
            'estado' => $usuario->estado, // Opcional, si es necesario
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  User  $usuario
     * @return \Illuminate\Http\Response
     */
    public function update(StoreUserRequest $request, User  $usuario)
    {

        $usuario->syncRoles($request->rolesSelected);
        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'area_id'=>$request->area_id,
            'password' => $request->password ? bcrypt($request->password) : $usuario->password,
            'estado' => $request->estado ?? $usuario->estado, // Mantener el valor actual si no se envía
        ]);
        return response()->json($usuario);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $usuario)
    {
        //
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
