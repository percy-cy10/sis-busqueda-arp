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
    // public function index(Request $request)
    // {
    //     $query = Otorgante::query();

    //     // 🔍 Filtro de búsqueda (opcional)
    //     if ($request->has('search') && $request->search !== '') {
    //         $search = $request->search;
    //         $query->where(function ($q) use ($search) {
    //             $q->where('nombre_completo', 'like', "%{$search}%")
    //                 ->orWhere('razon_social', 'like', "%{$search}%")
    //                 ->orWhere('nombre', 'like', "%{$search}%")
    //                 ->orWhere('apellido_paterno', 'like', "%{$search}%")
    //                 ->orWhere('apellido_materno', 'like', "%{$search}%");
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
        $query = Otorgante::query();

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
        $sortBy = $request->get('sort_by', 'updated_at');   // default updated_at
        $sortOrder = $request->get('sort_order', 'desc');   // default desc

        // Validar que solo se permita ordenar por campos seguros
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
