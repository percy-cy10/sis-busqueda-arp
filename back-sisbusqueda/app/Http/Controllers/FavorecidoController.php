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
    // public function index(Request $request)
    // {
    //     $query = Favorecido::query();

    //     // 🔍 Filtro de búsqueda (opcional)
    //     if ($request->has('search') && $request->search !== '') {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('nombre_completo', 'like', "%{$search}%")
    //             ->orWhere('razon_social', 'like', "%{$search}%")
    //             ->orWhere('nombre', 'like', "%{$search}%")
    //             ->orWhere('apellido_paterno', 'like', "%{$search}%")
    //             ->orWhere('apellido_materno', 'like', "%{$search}%");
    //         });
    //     }

    //     // Ordenar por fecha de actualización (descendente)
    //     $query->orderBy('updated_at', 'desc');

    //     // 📌 Si per_page = 0 → traer todos los registros
    //     $perPage = (int) $request->get('per_page', 10);
    //     if ($perPage === 0) {
    //         return response()->json([
    //             'data' => $query->get(),
    //             'total' => $query->count()
    //         ], 200);
    //     }

    //     // 📌 Caso normal: devolver con paginación
    //     return response()->json($query->paginate($perPage), 200);
    // }


    public function index(Request $request)
    {
        $query = Favorecido::query();

        // 🔍 Filtro de búsqueda (opcional)
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                   ->orWhere('razon_social', 'like', "%{$search}%")
                ->orWhere('nombre', 'like', "%{$search}%")
                ->orWhere('apellido_paterno', 'like', "%{$search}%")
                ->orWhere('apellido_materno', 'like', "%{$search}%");
            });
        }

        // 📌 Orden dinámico (id o nombre_completo)
        $sortBy = $request->get('sort_by', 'updated_at'); // por defecto updated_at
        $sortOrder = $request->get('sort_order', 'desc'); // por defecto descendente

        // Validamos que solo permita ordenar por columnas seguras
        if (in_array($sortBy, ['id', 'nombre_completo'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // 📌 Si per_page = 0 → traer todos los registros
        $perPage = (int) $request->get('per_page', 10);
        if ($perPage === 0) {
            return response()->json([
                'data' => $query->get(),
                'total' => $query->count()
            ], 200);
        }

        // 📌 Caso normal: devolver con paginación
        return response()->json($query->paginate($perPage), 200);
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
