<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAreaRequest;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Area::query(),
            [],
            ['id', 'nombre'],
            ['id', 'nombre']
        );

        // $dato = env('PAGO');

        // return $dato;
    }


    public function store(StoreAreaRequest $request)
    {
        return response(Area::create($request->all()), 201);
    }


    public function show(Area $area)
    {
        return response()->json($area);
    }


    public function update(StoreAreaRequest $request, Area $area)
    {
        $area->update($request->all());

        return response()->json([$request, $area]);
    }


    public function destroy(Area $area)
    {
        return response()->json($area->delete());
    }

    //
    public function getAreaByUserId(Request $request)
    {
        $user = $request->user();

        // Obtener las áreas relacionadas a través de la relación userHasAreas
        $areas = $user->userHasAreas()->with('area')->get(); // Asegúrate de que 'area' sea una relación en UserHasArea

        return response()->json($areas);
    }

    public function getUserAreas(Request $request)
    {
        // Obtiene el usuario autenticado
        $user = $request->user();

        // Obtiene las áreas asociadas al usuario
        $areas = $user->areas; // Asegúrate de que exista la relación 'areas' en el modelo User

        // Devuelve las áreas en formato JSON
        return response()->json($areas);
    }
}
