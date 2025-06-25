<?php

namespace App\Http\Controllers;

use App\Models\Escritura;
use App\Models\SubSerie;
use App\Models\Libro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EscrituraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     // Consulta base para obtener las escrituras con relaciones
    //     $escrituras = Escritura::with(['subSerie', 'libro', 'favorecidos', 'otorgantes']) // Eager load
    //         ->when($request->subserie_id, function ($query) use ($request) {
    //             return $query->where('subserie_id', $request->subserie_id);
    //         })
    //         ->when($request->libro_id, function ($query) use ($request) {
    //             return $query->where('libro_id', $request->libro_id);
    //         })
    //         ->paginate(10); // Paginación

    //     return response()->json($escrituras);
    // }

    public function index(Request $request)
    {
        $query = Escritura::with(['subSerie', 'libro', 'favorecidos', 'otorgantes']);

        $busqueda = [
            'otorgante' => $request->otorgante,
            'favorecido' => $request->favorecido,
            'notario' => $request->notario,
            'bien' => $request->bien,
        ];

        // Contar cuántos filtros de texto hay activos
        $activos = array_filter($busqueda, fn($v) => $v && strlen($v) >= 3);

        // Si solo hay un filtro activo, buscar solo en ese campo
        if (count($activos) > 1) {
            $query->where(function($q) use ($request) {
                if ($request->otorgante && strlen($request->otorgante) >= 3) {
                    $valor = strtolower($request->otorgante);
                    $q->orWhereHas('otorgantes', fn($q2) => $q2->whereRaw('LOWER(nombre_completo) LIKE ?', ["%$valor%"]));
                }
                if ($request->favorecido && strlen($request->favorecido) >= 3) {
                    $valor = strtolower($request->favorecido);
                    $q->orWhereHas('favorecidos', fn($q2) => $q2->whereRaw('LOWER(nombre_completo) LIKE ?', ["%$valor%"]));
                }
                if ($request->notario && strlen($request->notario) >= 3) {
                    $valor = strtolower($request->notario);
                    $q->orWhereHas('libro', fn($q2) => $q2->whereRaw('LOWER(notario) LIKE ?', ["%$valor%"]));
                }
                if ($request->bien && strlen($request->bien) >= 3) {
                    $valor = strtolower($request->bien);
                    $q->orWhereRaw('LOWER(bien) LIKE ?', ["%$valor%"]);
                }
            });
        } else {
            // Si hay más de uno, aplicar todos los filtros activos (como ya tienes)
            if ($request->otorgante && strlen($request->otorgante) >= 3) {
                $valor = strtolower($request->otorgante);
                $query->whereHas('otorgantes', fn($q) => $q->whereRaw('LOWER(nombre_completo) LIKE ?', ["%$valor%"]));
            }
            if ($request->favorecido && strlen($request->favorecido) >= 3) {
                $valor = strtolower($request->favorecido);
                $query->whereHas('favorecidos', fn($q) => $q->whereRaw('LOWER(nombre_completo) LIKE ?', ["%$valor%"]));
            }
            if ($request->notario && strlen($request->notario) >= 3) {
                $valor = strtolower($request->notario);
                $query->whereHas('libro', fn($q) => $q->whereRaw('LOWER(notario) LIKE ?', ["%$valor%"]));
            }
            if ($request->bien && strlen($request->bien) >= 3) {
                $valor = strtolower($request->bien);
                $query->whereRaw('LOWER(bien) LIKE ?', ["%$valor%"]);
            }
        }

        $escrituras = $query->paginate($request->get('per_page', 10));

        // Marcar en qué columna hubo coincidencia
        $campoCoincidencia = count($activos) === 1 ? array_key_first($activos) : null;

        $escrituras->getCollection()->transform(function($item) use ($campoCoincidencia, $busqueda) {
            $item->coincidencia = null;
            if ($campoCoincidencia) {
                if ($campoCoincidencia === 'otorgante') $item->coincidencia = 'Otorgante';
                if ($campoCoincidencia === 'favorecido') $item->coincidencia = 'Favorecido';
                if ($campoCoincidencia === 'notario') $item->coincidencia = 'Notario';
                if ($campoCoincidencia === 'bien') $item->coincidencia = 'Bien';
            }
            return $item;
        });

        return response()->json($escrituras);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Cargar datos para el formulario de creación
        $subseries = SubSerie::all();
        $libros = Libro::all();

        return response()->json(compact('subseries', 'libros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'bien' => 'required|string|max:255',
            'subserie_id' => 'nullable|exists:sub_series,id',
            'anio' => 'nullable|digits:4',
            'mes' => 'nullable|integer|between:1,12',
            'dia' => 'nullable|integer|between:1,31',
            'fecha' => 'nullable|date',
            'cod_escritura' => 'required|string|max:255',
            'cod_folioInicial' => 'required|string|max:255',
            'cod_folioFinal' => 'required|string|max:255',
            'libro_id' => 'nullable|exists:libros,id',
            'observaciones' => 'nullable|string',
            'favorecidos' => 'required|array',
            'favorecidos.*' => 'exists:favorecidos,id',
            'otorgantes' => 'required|array',
            'otorgantes.*' => 'exists:otorgantes,id',
            'file' => 'nullable|file|mimes:pdf|max:10240', // Validar el archivo PDF
            'file_name' => 'nullable|string|max:255', // Validar el nombre del archivo
        ]);

        // Crear la escritura
        $escritura = Escritura::create($validated);

        // Sincronizar los IDs en las tablas pivote
        $escritura->favorecidos()->sync($validated['favorecidos']);
        $escritura->otorgantes()->sync($validated['otorgantes']);

        // Manejar el archivo PDF
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $validated['file_name'] ?? $file->getClientOriginalName(); // Usar el nombre proporcionado o el original
            $filePath = $file->storeAs('public/Escrituras', $fileName); // Guardar el archivo en storage/app/public/Escrituras

            // Guardar la ruta completa del archivo en la base de datos
            // $escritura->update(['file_name' => $filePath]);
            $escritura->update(['file_name' => $fileName]); // <-- Usar Storage::url()
        } else {
            // Si no se envía archivo, establecer file_name como null
            $escritura->update(['file_name' => null]);
        }

        return response()->json($escritura->load(['favorecidos', 'otorgantes']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Escritura $escritura)
    {
        // Mostrar la escritura específica, cargando la subserie y el libro
        return response()->json($escritura->load(['subSerie', 'libro', 'favorecidos', 'otorgantes']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Escritura $escritura)
    {
        // Devolver los datos de la escritura para edición
        $subseries = SubSerie::all();
        $libros = Libro::all();

        return response()->json(compact('escritura', 'subseries', 'libros'));
    }

    /**
     * Update the specified resource in storage.
      */
    // public function update(Request $request, Escritura $escritura)
    // {
    //     // Validar los datos recibidos
    //     $validated = $request->validate([
    //         'bien' => 'required|string|max:255',
    //         'subserie_id' => 'nullable|exists:sub_series,id',
    //         'anio' => 'nullable|digits:4',
    //         'mes' => 'nullable|integer|between:1,12',
    //         'dia' => 'nullable|integer|between:1,31',
    //         'fecha' => 'nullable|date',
    //         'cod_escritura' => 'required|string|max:255',
    //         'cod_folioInicial' => 'required|string|max:255',
    //         'cod_folioFinal' => 'required|string|max:255',
    //         'libro_id' => 'nullable|exists:libros,id',
    //         'observaciones' => 'nullable|string',
    //         'favorecidos' => 'required|array',
    //         'favorecidos.*' => 'exists:favorecidos,id',
    //         'otorgantes' => 'required|array',
    //         'otorgantes.*' => 'exists:otorgantes,id',
    //         'file' => 'nullable|sometimes|file|mimes:pdf|max:10240', // Archivo opcional y debe ser PDF
    //         'file_name' => 'nullable|string|max:255', // Nombre del archivo opcional
    //     ]);

    //     // Actualizar campos principales
    //     $escritura->update($validated);

    //     // Sincronizar relaciones
    //     $escritura->favorecidos()->sync($validated['favorecidos']);
    //     $escritura->otorgantes()->sync($validated['otorgantes']);

    //     // Manejar archivo nuevo si se sube
    //     // Manejo de archivo
    //     // if ($request->hasFile('file')) {
    //     //     // Eliminar archivo antiguo si existe
    //     //     if ($escritura->file_name && Storage::exists($escritura->file_name)) {
    //     //         Storage::delete($escritura->file_name);
    //     //     }
    //     //     // Guardar nuevo archivo
    //     //     $path = $request->file('file')->storeAs(
    //     //         'escrituras',
    //     //         $request->file_name // Usar el nombre generado en frontend
    //     //     );
    //     //     $escritura->file_name = $path;
    //     // } elseif ($request->file_name === null) {
    //     //     // Eliminar archivo si se envió null
    //     //     if ($escritura->file_name) {
    //     //         Storage::delete($escritura->file_name);
    //     //         $escritura->file_name = null;
    //     //     }
    //     // } elseif ($request->file_name !== $escritura->file_name) {
    //     //     // Renombrar archivo si cambió el nombre
    //     //     if ($escritura->file_name) {
    //     //         $newPath = 'escrituras/' . $request->file_name;
    //     //         Storage::move($escritura->file_name, $newPath);
    //     //         $escritura->file_name = $newPath;
    //     //     }
    //     // }

    //     if ($request->hasFile('file')) {
    //         // Eliminar archivo antiguo si existe
    //         if ($escritura->file_name && Storage::exists($escritura->file_name)) {
    //             Storage::delete($escritura->file_name);
    //         }
    //         // Guardar nuevo archivo
    //         $path = $request->file('file')->storeAs(
    //             'escrituras',
    //             $request->file_name // Usar el nombre generado en frontend
    //         );
    //         $escritura->file_name = $path;
    //     } elseif ($request->file_name === null) {
    //         // Eliminar archivo si se envió null
    //         if ($escritura->file_name) {
    //             Storage::delete($escritura->file_name);
    //             $escritura->file_name = null;
    //         }
    //     } elseif ($request->file_name !== $escritura->file_name) {
    //         // Renombrar archivo si cambió el nombre
    //         if ($escritura->file_name) {
    //             $newPath = 'escrituras/' . $request->file_name;
    //             Storage::move($escritura->file_name, $newPath);
    //             $escritura->file_name = $newPath;
    //         }
    //     }




    //     // Actualizar otros campos
    //     $escritura->update($request->except('file'));

    //     return response()->json($escritura->load(['favorecidos', 'otorgantes']));
    // }

    public function update(Request $request, Escritura $escritura)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'bien' => 'required|string|max:255',
            'subserie_id' => 'nullable|exists:sub_series,id',
            'anio' => 'nullable|digits:4',
            'mes' => 'nullable|integer|between:1,12',
            'dia' => 'nullable|integer|between:1,31',
            'fecha' => 'nullable|date',
            'cod_escritura' => 'required|string|max:255',
            'cod_folioInicial' => 'required|string|max:255',
            'cod_folioFinal' => 'required|string|max:255',
            'libro_id' => 'nullable|exists:libros,id',
            'observaciones' => 'nullable|string',
            'favorecidos' => 'required|array',
            'favorecidos.*' => 'exists:favorecidos,id',
            'otorgantes' => 'required|array',
            'otorgantes.*' => 'exists:otorgantes,id',
            'file' => 'nullable|sometimes|file|mimes:pdf|max:10240',
            'file_name' => 'nullable|string|max:255',
        ]);

        // Actualizar campos principales (excluyendo 'file' y 'file_name')
        $escritura->update($request->except(['file', 'file_name']));

        // Sincronizar relaciones
        $escritura->favorecidos()->sync($validated['favorecidos']);
        $escritura->otorgantes()->sync($validated['otorgantes']);

        // Manejar archivo
        if ($request->hasFile('file')) {
            // Ruta completa del archivo antiguo
            $oldFilePath = 'public/Escrituras/' . $escritura->file_name;

            // Eliminar archivo antiguo si existe
            if ($escritura->file_name && Storage::exists($oldFilePath)) {
                Storage::delete($oldFilePath);
            }

            // Guardar nuevo archivo en la carpeta correcta
            $newFileName = $request->file_name;
            $path = $request->file('file')->storeAs(
                'public/Escrituras', // Directorio consistente
                $newFileName
            );

            // Actualizar solo el nombre del archivo (sin ruta)
            $escritura->file_name = $newFileName;
            $escritura->save();

        } elseif ($request->file_name === null) {
            // Eliminar archivo si existe
            $filePath = 'public/Escrituras/' . $escritura->file_name;
            if ($escritura->file_name && Storage::exists($filePath)) {
                Storage::delete($filePath);
                $escritura->file_name = null;
                $escritura->save();
            }
        } else {
            // Verificar si el nombre ha cambiado
            if ($request->file_name !== $escritura->file_name) {
                $oldPath = 'public/Escrituras/' . $escritura->file_name;
                $newPath = 'public/Escrituras/' . $request->file_name;

                // Renombrar archivo físico si existe
                if (Storage::exists($oldPath)) {
                    Storage::move($oldPath, $newPath);
                }

                // Actualizar nombre en BD
                $escritura->file_name = $request->file_name;
                $escritura->save();
            }
        }

        return response()->json($escritura->load(['favorecidos', 'otorgantes']));
    }



    protected function shouldRenameFile(Escritura $escritura, array $validated): bool
    {
        return $escritura->cod_escritura !== $validated['cod_escritura'] ||
            $escritura->libro_id !== $validated['libro_id'];
    }

    protected function generateFileName(array $validated): string
    {
        $libro = \App\Models\Libro::find($validated['libro_id']);
        $libroCodigo = $libro ? $libro->codigo : 'sin_libro';

        return "{$libroCodigo}_{$validated['cod_escritura']}.pdf";
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Escritura $escritura)
    {
        // Eliminar el archivo si existe

        // if ($escritura->file_name && Storage::exists($escritura->file_name)) {
        //     Storage::delete($escritura->file_name);
        // }


        $filePath = 'public/Escrituras/' . $escritura->file_name;

        if ($escritura->file_name && Storage::exists($filePath)) {
            Storage::delete($filePath);
        }




        // Eliminar la escritura
        $escritura->delete();

        return response()->json(null, 204); // Código de éxito 204 para "sin contenido"
    }
}
