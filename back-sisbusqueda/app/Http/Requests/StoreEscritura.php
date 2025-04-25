<?php


namespace App\Http\Controllers;

use App\Models\Escritura;
use Illuminate\Http\Request;

class EscrituraController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación de los datos de entrada
        $validated = $request->validate([
            'bien' => 'required|string|max:255',
            'subserie_id' => 'required|exists:subseries,id', // Verifica que el subserie_id exista en la tabla subseries
            'libro_id' => 'required|exists:libros,id', // Verifica que el libro_id exista en la tabla libros
            'anio' => 'required|integer',
            'mes' => 'required|integer',
            'dia' => 'required|integer',
            'fecha' => 'required|date',
            'cod_escritura' => 'required|string|max:255',
            'cod_folioInicial' => 'required|string|max:255',
            'cod_folioFinal' => 'required|string|max:255',
            'observaciones' => 'nullable|string',
            
        ]);

        // Crear el objeto Escritura con los datos validados
        $escritura = Escritura::create($validated);

        // Retornar la escritura creada con una respuesta JSON
        return response()->json($escritura, 201);  // 201: Created
    }
}
