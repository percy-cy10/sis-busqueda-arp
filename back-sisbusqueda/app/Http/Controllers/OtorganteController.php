<?php

namespace App\Http\Controllers;

use App\Models\Otorgante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class OtorganteController extends Controller
{
    /**
     * Mostrar una lista de los recursos.
     * GET /otorgantes
     */
    public function index(Request $request)
    {
        // Consulta base para obtener los otorgantes
        $query = Otorgante::query();

        // Ordenar por fecha de actualización (descendente)
        $query->orderBy('updated_at', 'desc');

        // Paginación con opción de resultados por página
        $perPage = $request->get('per_page', 10);
        $otorgantes = $query->paginate($perPage);

        return response()->json($otorgantes, 200);
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    /**
     * Almacenar un nuevo recurso en la base de datos.
     * POST /otorgantes
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['natural', 'juridica'])],
            'nombre' => ['required_if:tipo,natural', 'nullable', 'string'],
            'apellido_paterno' => ['required_if:tipo,natural', 'nullable', 'string'],
            'apellido_materno' => ['required_if:tipo,natural', 'nullable', 'string'],
            'razon_social' => ['required_if:tipo,juridica', 'nullable', 'string'],
            'nombre_completo' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $validated = $this->cleanData($validated);
        $validated['created_at'] = Carbon::now();
        $validated['updated_at'] = Carbon::now();

        try {
            $otorgante = Otorgante::create($validated);
            return response()->json([
                'message' => 'Otorgante creado con éxito',
                'otorgante' => $otorgante
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear otorgante: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Mostrar un recurso específico.
     * GET /otorgantes/{id}
     */
    public function show(Otorgante $otorgante)
    {
        return response()->json($otorgante, 200);
    }

    /**
     * Actualizar un recurso específico en la base de datos.
     * PUT/PATCH /otorgantes/{id}
     */
    public function update(Request $request, Otorgante $otorgante)
    {
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['natural', 'juridica'])],
            'nombre' => ['required_if:tipo,natural', 'nullable', 'string'],
            'apellido_paterno' => ['required_if:tipo,natural', 'nullable', 'string'],
            'apellido_materno' => ['required_if:tipo,natural', 'nullable', 'string'],
            'razon_social' => ['required_if:tipo,juridica', 'nullable', 'string'],
            'nombre_completo' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $validated = $this->cleanData($validated);
        $validated['updated_at'] = Carbon::now();

        try {
            $otorgante->update($validated);
            return response()->json([
                'message' => 'Otorgante actualizado correctamente',
                'otorgante' => $otorgante->fresh()
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar otorgante: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Eliminar un recurso específico de la base de datos.
     * DELETE /otorgantes/{id}
     */
    public function destroy(Otorgante $otorgante)
    {
        try {
            $otorgante->delete();
            return response()->json([
                'message' => 'Otorgante eliminado correctamente'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar otorgante: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Limpiar datos según el tipo de otorgante
     */
    private function cleanData(array $data): array
    {
        if ($data['tipo'] === 'juridica') {
            $data['nombre'] = null;
            $data['apellido_paterno'] = null;
            $data['apellido_materno'] = null;
        } else {
            $data['razon_social'] = null;
        }
        return $data;
    }
}
