<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Pago;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index(Request $request)
    // {
    //     $query = Pago::with(['tupas', 'user', 'solicitud']);

    //     // Búsqueda por filtro general (id, boleta_id, nombre, etc)
    //     if ($request->filled('filter')) {
    //         $filter = $request->input('filter');
    //         $query->where(function($q) use ($filter) {
    //             $q->orWhere('id', 'like', "%$filter%")
    //             ->orWhere('boleta_id', 'like', "%$filter%")
    //             ->orWhere('nombre_completo', 'like', "%$filter%")
    //             ->orWhere('num_documento', 'like', "%$filter%");
    //         });
    //     }
    //     if (is_numeric($filter)) {
    //         $q->orWhere('id', $filter);
    //     }

    //     // Ordenamiento
    //     // $sortBy = $request->input('sortBy', 'id');
    //     // $descending = $request->boolean('descending', true); // Por defecto descendente
    //     // $direction = $descending ? 'desc' : 'asc';
    //     // $query->orderBy($sortBy, $direction);
    //     $sortBy = $request->input('sortBy', 'id');
    //     if (!in_array($sortBy, ['id', 'boleta_id', 'nombre_completo', 'num_documento', 'total', 'estado'])) {
    //         $sortBy = 'id';
    //     }
    //     $descending = $request->boolean('descending', true);
    //     $direction = $descending ? 'desc' : 'asc';
    //     $query->orderBy($sortBy, $direction);

    //     // Paginación
    //     $perPage = $request->input('rowsPerPage', 7);
    //     $paginated = $query->paginate($perPage);

    //     // return response()->json($paginated);
    //     return response()->json([
    //         'data' => $paginated->items(),
    //         'total' => $paginated->total(),
    //         'current_page' => $paginated->currentPage(),
    //         'per_page' => $paginated->perPage(),
    //     ]);
    // }

    public function index(Request $request)
    {
        $query = Pago::with(['tupas', 'user', 'solicitud']);
        

        // FILTRO POR USUARIO AUTENTICADO
        if ($request->has('solo_mios') && $request->boolean('solo_mios')) {
            $query->where('user_id', auth()->id());
        }

        // Búsqueda por filtro general (id, boleta_id, nombre, etc)
        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            $query->where(function($q) use ($filter) {
                if (is_numeric($filter)) {
                    $q->orWhere('id', $filter);
                }
                $q->orWhere('boleta_id', 'like', "%$filter%")
                ->orWhere('nombre_completo', 'like', "%$filter%")
                ->orWhere('solicitud_id', 'like', "%$filter%")
                ->orWhere('num_documento', 'like', "%$filter%");
            });
        }

        // Ordenamiento seguro
        $sortBy = $request->input('sortBy', 'id');
        if (!in_array($sortBy, ['id', 'boleta_id', 'nombre_completo', 'num_documento', 'total', 'estado'])) {
            $sortBy = 'id';
        }
        $descending = $request->boolean('descending', true);
        $direction = $descending ? 'asc' : 'desc';
        $query->orderBy($sortBy, $direction);

        // Paginación
        $perPage = $request->input('rowsPerPage', 7);
        $paginated = $query->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StorePagoRequest $request)
    // {
    //     // Actualiza la solicitud relacionada
    //     Solicitud::find($request->solicitud_id)->update([
    //         "estado" => "Finalizado",
    //         'updated_at'=> now(),
    //         'area_id' => 4,
    //     ]);

    //     // Si quieres forzar el user_id del usuario autenticado (opcional)
    //     $userId = auth()->id();


    //     // Crea el pago
    //     $pago = Pago::create([
    //         'solicitud_id'    => $request->solicitud_id,
    //         'tipo_documento'  => $request->tipo_documento,
    //         'num_documento'   => $request->num_documento,
    //         'nombre_completo' => $request->nombre_completo,
    //         'total'           => $request->total,
    //         'user_id'         => $userId,
    //         'estado'          => $request->has('estado') ? $request->estado : 1,
    //         'created_at'      => now(),
    //         'updated_at'      => now(),
    //     ]);

    //     // Inserta en la tabla pivote pagos_tupa
    //     if ($request->has('tupas') && is_array($request->tupas)) {
    //         foreach ($request->tupas as $tupa) {
    //             \DB::table('pagos_tupa')->insert([
    //                 'pagos_id' => $pago->id,
    //                 'tupa_id' => $tupa['tupa_id'],
    //                 'cantidad' => $tupa['cantidad'],
    //                 'Subtotal' => $tupa['Subtotal'],
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         }
    //     }

    //     return response($pago, 201);
    // }


    public function store(StorePagoRequest $request)
    {
        // Solo obtenemos el user_id autenticado
        $userId = auth()->id();

        // Crea el pago solo con el ID de la solicitud y los datos enviados
        // $pago = Pago::create([
        //     'solicitud_id'    => $request->solicitud_id,
        //     'tipo_documento'  => $request->tipo_documento,
        //     'num_documento'   => $request->num_documento,
        //     'nombre_completo' => $request->nombre_completo,
        //     'total'           => $request->total,
        //     'user_id'         => $userId,
        //     'estado'          => $request->has('estado') ? $request->estado : 1,
        //     'created_at'      => now(),
        //     'updated_at'      => now(),
        // ]);

        $data = [
            'solicitud_id'    => $request->solicitud_id,
            'tipo_documento'  => $request->tipo_documento,
            'num_documento'   => $request->num_documento,
            'nombre_completo' => $request->nombre_completo,
            'total'           => $request->total,
            'user_id'         => $userId,
            'estado'          => $request->has('estado') ? $request->estado : 1,
            'created_at'      => now(),
            'updated_at'      => now(),
        ];

        // Solo asigna boleta_id si viene desde SolicitudesForm
        if ($request->has('desde_solicitud') && $request->desde_solicitud) {
            $ultimoBoletaId = Pago::whereNotNull('boleta_id')->max('boleta_id');
            $nuevoBoletaId = $ultimoBoletaId ? $ultimoBoletaId + 1 : 1;
            $data['boleta_id'] = $nuevoBoletaId;
        }

        $pago = Pago::create($data);

        // Inserta en la tabla pivote pagos_tupa si corresponde
        if ($request->has('tupas') && is_array($request->tupas)) {
            foreach ($request->tupas as $tupa) {
                \DB::table('pagos_tupa')->insert([
                    'pagos_id' => $pago->id,
                    'tupa_id' => $tupa['tupa_id'],
                    'cantidad' => $tupa['cantidad'],
                    'Subtotal' => $tupa['Subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response($pago, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        $pago->load(['tupas', 'user', 'solicitud']);
        return response()->json($pago);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        // Actualiza los campos principales
        $pago->update($request->only([
            'tipo_documento',
            'num_documento',
            'nombre_completo',
            'total',
            'user_id'
        ]));

        // --- ACTUALIZAR LA TABLA PIVOTE pagos_tupa ---
        // 1. Eliminar las relaciones actuales
        \DB::table('pagos_tupa')->where('pagos_id', $pago->id)->delete();

        // 2. Insertar las nuevas relaciones
        if ($request->has('tupas') && is_array($request->tupas)) {
            foreach ($request->tupas as $tupa) {
                \DB::table('pagos_tupa')->insert([
                    'pagos_id' => $pago->id,
                    'tupa_id' => $tupa['tupa_id'],
                    'cantidad' => $tupa['cantidad'],
                    'Subtotal' => $tupa['Subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json($pago);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        $pago->delete();
        return response()->json(['message' => 'Pago eliminado']);
    }

    // public function toggleEstado(Pago $pago)
    // {
    //     try {
    //         // Alterna entre 1 (pendiente) y 0 (pagado)
    //         $nuevoEstado = $pago->estado == 1 ? 0 : 1;
    //         $pago->updateQuietly(['estado' => $nuevoEstado]);

    //         return response()->json([
    //             'message' => 'Estado de pago actualizado correctamente',
    //             'pago' => $pago,
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Error al actualizar el estado del pago',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function toggleEstado(Pago $pago)
    {
        try {
            DB::beginTransaction();

            if ($pago->estado == 1) { // Cambiar de pendiente a pagado
                // Obtener el último boleta_id asignado
                $ultimoBoletaId = Pago::whereNotNull('boleta_id')->max('boleta_id');
                $nuevoBoletaId = $ultimoBoletaId ? $ultimoBoletaId + 1 : 1;

                $pago->update([
                    'estado' => 0,
                    'boleta_id' => $nuevoBoletaId
                ]);
            } else { // Cambiar de pagado a pendiente
                $pago->update([
                    'estado' => 1,
                    'boleta_id' => null
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Estado de pago actualizado correctamente',
                'pago' => $pago,
                'boleta_id' => $pago->boleta_id // Devuelve el nuevo boleta_id
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al actualizar el estado del pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


}
