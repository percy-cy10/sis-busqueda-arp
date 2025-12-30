<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNivelRequest;
use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    public function index(Request $request)
    {
        $rowsPerPage = (int) $request->input('rowsPerPage', 15);

        if ($rowsPerPage === 0) {
            $data = Nivel::all(['id', 'nombre']);
            return response()->json([
                'data' => $data,
                'total' => $data->count(),
            ]);
        }

        $niveles = Nivel::select(['id', 'nombre'])->paginate($rowsPerPage);
        return response()->json([
            'data' => $niveles->items(),
            'total' => $niveles->total(),
        ]);
    }

    public function store(StoreNivelRequest $request)
    {
        return response(Nivel::create($request->all()), 201);
    }

    public function show(Nivel $nivel)
    {
        return response()->json($nivel);
    }


    public function update(StoreNivelRequest $request, Nivel $nivel)
    {
        $nivel->update($request->all());
        return response()->json($nivel);    
    }

    public function destroy(Nivel $nivel)
    {
        return response()->json($nivel->delete());
    }
}
