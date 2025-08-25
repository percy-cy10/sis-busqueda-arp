<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotarioRequest;
use App\Models\Notario;
use Illuminate\Http\Request;

class NotarioController extends Controller
{


    public function index(Request $request)
    {
        $rowsPerPage = (int) $request->input('rowsPerPage', 15);

        if ($rowsPerPage === 0) {
            // Devuelve todos los notarios sin paginar
            $data = Notario::all(['id', 'nombre_completo', 'ubigeo_cod', 'año_inicio', 'año_final']);
            return response()->json([
                'data' => $data,
                'total' => $data->count(),
            ]);
        }

        // Si no es 0, usa la función original (paginada)
        return $this->generateViewSetList(
            $request,
            Notario::query(),
            [],
            ['id', 'nombre_completo', 'ubigeo_cod'],
            ['id', 'nombre_completo']
        );
    }


    public function store(StoreNotarioRequest $request)
    {
        return response(Notario::create($request->all()), 201);
    }


    public function show(Notario $notario)
    {
        return response()->json($notario);
    }


    public function update(StoreNotarioRequest $request, Notario $notario)
    {
        $notario->update($request->all());

        return response()->json([$request, $notario]);
    }


    public function destroy(Notario $notario)
    {
        return response()->json($notario->delete());
    }
}
