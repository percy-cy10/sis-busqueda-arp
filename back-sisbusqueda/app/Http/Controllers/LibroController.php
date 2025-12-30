<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLibroRequest;
use App\Models\Libro;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibroController extends Controller
{
    /**
     * Mostrar una lista de los recursos.
     * GET /libros
     */
    // public function index(Request $request)
    // {
    //     // Consulta base para obtener los libros
    //     $query = Libro::query();

    //     // Filtrar por estado si se proporciona en la solicitud
    //     if ($request->filled('estado')) {
    //         $query->where('estado', $request->estado);
    //     }

    //     // Ordenar por estado (descendente) y fecha de actualización (descendente)
    //     $query->orderBy('estado', 'desc')->orderBy('updated_at', 'desc');

    //     // Paginación: se puede personalizar el número de resultados por página con 'per_page'
    //     $perPage = $request->get('per_page', 10); // Por defecto, 10 resultados por página
    //     $libros = $query->paginate($perPage);

    //     // Retornar los resultados en formato JSON
    //     return response()->json($libros, 200);
    // }
    public function index(Request $request)
    {
        $query = Libro::query();

        // Filtrar por estado si se proporciona
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // 🔍 Filtro de búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('protocolo', 'LIKE', "%{$search}%")
                ->orWhereHas('notario', function ($sub) use ($search) {
                    $sub->where('nombre_completo', 'LIKE', "%{$search}%");
                });
            });
        }

        // Ordenar (si viene en request)
        if ($request->filled('sortBy')) {
            $query->orderBy($request->sortBy, $request->boolean('descending') ? 'desc' : 'asc');
        } else {
            $query->orderBy('estado', 'desc')->orderBy('updated_at', 'desc');
        }

        // Paginación
        $perPage = $request->get('per_page', 10);
        $libros = $query->paginate($perPage);

        return response()->json($libros, 200);
    }


    /**
     * Almacenar un nuevo recurso en la base de datos.
     * POST /libros
     */
    // public function store(StoreLibroRequest $request)
    // {
    //     // Crear un nuevo libro con los datos validados
    //     $libro = Libro::create([
    //         'protocolo' => $request->protocolo,
    //         'estado' => $request->estado ?? 1, // Estado por defecto: 1 (activo)
    //         // 'estado' => filter_var($request->estado, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),

    //         'user_id' => auth()->id(), // ID del usuario autenticado
    //         'notario_id' => $request->notario_id,
    //         'created_at' => Carbon::now(),
    //         'updated_at' => Carbon::now(),
    //     ]);

    //     // Retornar una respuesta JSON con el libro creado
    //     return response()->json(['message' => 'Libro creado correctamente', 'libro' => $libro], 201);
    // }

    public function store(Request $request)
    {
        // Verificar los datos recibidos
        \Log::info('Datos recibidos:', $request->all());

        // Validar datos
        $request->validate([
            'protocolo' => 'required|string|max:50',
            'notario_id' => 'required|integer|exists:notarios,id',
            'estado' => 'required|boolean',
        ]);

        // Intentar guardar el libro
        try {
            // Crear un nuevo libro con usuario y fecha
            $libro = Libro::create([
                'protocolo' => $request->protocolo,
                'estado' => $request->estado,
                'notario_id' => $request->notario_id,
                'user_id' => auth()->id(), // ID del usuario autenticado
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

            return response()->json(['message' => 'Libro creado con éxito', 'libro' => $libro], 201);
        } catch (\Exception $e) {
            \Log::error('Error al insertar libro: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }



    /**
     * Mostrar un recurso específico.
     * GET /libros/{id}
     */
    public function show(Libro $libro)
    {
        // Retornar el libro solicitado en formato JSON
        return response()->json($libro, 200);
    }

    /**
     * Actualizar un recurso específico en la base de datos.
     * PUT o PATCH /libros/{id}
     */
    // public function update(StoreLibroRequest $request, Libro $libro)
    // {
    //     // Actualizar el libro con los datos validados
    //     $libro->update([
    //         'protocolo' => $request->protocolo,
    //         'estado' => $request->estado,
    //         // 'estado' => filter_var($request->estado, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),

    //         'notario_id' => $request->notario_id,
    //         'updated_at' => Carbon::now(),
    //     ]);

    //     // Retornar una respuesta JSON con el libro actualizado
    //     return response()->json(['message' => 'Libro actualizado correctamente', 'libro' => $libro], 200);
    // }

    public function update(StoreLibroRequest $request, Libro $libro)
    {
        // Actualizar el libro con los datos validados
        $libro->update([
            'protocolo' => $request->protocolo,
            'estado' => $request->estado,
            'notario_id' => $request->notario_id,
            'user_id' => auth()->id(), // Actualizar el usuario
            'updated_at' => Carbon::now(), // Actualizar la fecha
        ]);

        return response()->json(['message' => 'Libro actualizado correctamente', 'libro' => $libro], 200);
    }


    /**
     * Eliminar un recurso específico de la base de datos.
     * DELETE /libros/{id}
     */
    public function destroy(Libro $libro)
    {
        // Eliminar el libro de la base de datos
        $libro->delete();

        // Retornar una respuesta JSON indicando que se eliminó correctamente
        return response()->json(['message' => 'Libro eliminado correctamente'], 200);
    }

    /**
     * Alternar el estado de un recurso específico.
     * PATCH /libros/{id}/toggle-estado
     */
    public function toggleEstado(Libro $libro)
    {
        try {
            // Cambiar el estado del libro (activo/inactivo) sin disparar validaciones
            $libro->updateQuietly(['estado' => !$libro->estado]);

            // Retornar una respuesta JSON con el nuevo estado del libro
            return response()->json([
                'message' => 'Estado actualizado correctamente',
                'libro' => $libro,
            ], 200);
        } catch (\Exception $e) {
            // Manejar errores inesperados
            return response()->json([
                'message' => 'Error al actualizar el estado',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
