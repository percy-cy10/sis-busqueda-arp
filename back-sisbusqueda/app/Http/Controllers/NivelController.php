<?php

namespace App\Http\Controllers;

use App\Models\Nivel;
use Illuminate\Http\Request;

class NivelController extends Controller
{
    /**
     * Display a listing of the niveles.
     */
    public function index()
    {
        $niveles = Nivel::with(['users', 'solicitudes'])->get();
        return response()->json($niveles);
    }

    /**
     * Store a newly created nivel in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $nivel = Nivel::create($request->only('nombre'));
        return response()->json($nivel, 201);
    }

    /**
     * Display the specified nivel.
     */
    public function show(Nivel $nivel)
    {
        return response()->json($nivel->load(['users', 'solicitudes']));
    }

    /**
     * Update the specified nivel in storage.
     */
    public function update(Request $request, Nivel $nivel)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        $nivel->update($request->only('nombre'));
        return response()->json($nivel);
    }

    /**
     * Remove the specified nivel from storage.
     */
    public function destroy(Nivel $nivel)
    {
        $nivel->delete();
        return response()->json(null, 204);
    }
}
