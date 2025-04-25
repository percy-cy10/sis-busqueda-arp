<?php

namespace App\Http\Controllers;

use App\Models\Favorecido;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class FavorecidoController extends Controller
{
    /**
     * Mostrar una lista de los recursos.
     * GET /favorecidos
     */
    public function index(Request $request)
    {
        // Consulta base para obtener los favorecidos
        $query = Favorecido::query();

        

        // Ordenar por fecha de actualización (descendente)
        $query->orderBy('updated_at', 'desc');

        // Paginación: se puede personalizar el número de resultados por página con 'per_page'
        $perPage = $request->get('per_page', 10); // Por defecto, 10 resultados por página
        $favorecidos = $query->paginate($perPage);

        // Retornar los resultados en formato JSON
        return response()->json($favorecidos, 200);
    }

    /**
     * Almacenar un nuevo recurso en la base de datos.
     * POST /favorecidos
     */
    public function store(Request $request)
    {
        // Validar datos
        $validated = $request->validate([
            'tipo' => ['required', Rule::in(['natural', 'juridica'])],
            'nombre' => ['required_if:tipo,natural', 'nullable', 'string'],
            'apellido_paterno' => ['required_if:tipo,natural', 'nullable', 'string'],
            'apellido_materno' => ['required_if:tipo,natural', 'nullable', 'string'],
            'razon_social' => ['required_if:tipo,juridica', 'nullable', 'string'],
            'nombre_completo' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        // Limpiar datos según el tipo
        $validated = $this->cleanData($validated);
        $validated['created_at'] = Carbon::now();
        $validated['updated_at'] = Carbon::now();

        try {
            $favorecido = Favorecido::create($validated);
            return response()->json(['message' => 'Favorecido creado con éxito', 'favorecido' => $favorecido], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear favorecido: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Mostrar un recurso específico.
     * GET /favorecidos/{id}
     */
    public function show(Favorecido $favorecido)
    {
        return response()->json($favorecido, 200);
    }

    /**
     * Actualizar un recurso específico en la base de datos.
     * PUT o PATCH /favorecidos/{id}
     */
    public function update(Request $request, Favorecido $favorecido)
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

        // Limpiar datos según el tipo
        $validated = $this->cleanData($validated);
        $validated['updated_at'] = Carbon::now();

        try {
            $favorecido->update($validated);
            $favorecido->refresh(); // Recargar el modelo actualizado
            return response()->json(['message' => 'Favorecido actualizado correctamente', 'favorecido' => $favorecido], 200);
        } catch (\Exception $e) {
            \Log::error('Error al actualizar favorecido: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Eliminar un recurso específico de la base de datos.
     * DELETE /favorecidos/{id}
     */
    public function destroy(Favorecido $favorecido)
    {
        try {
            $favorecido->delete();
            return response()->json(['message' => 'Favorecido eliminado correctamente'], 200);
        } catch (\Exception $e) {
            \Log::error('Error al eliminar favorecido: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Método privado para limpiar datos según el tipo.
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
