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



    public function index(Request $request)
    {
        // Cargar las relaciones necesarias incluyendo creador y actualizador
        $query = Pago::with(['tupas', 'user', 'solicitud', 'creador', 'actualizador']);

        // FILTRO POR USUARIO AUTENTICADO (para reportes)
        if ($request->has('solo_mios') && $request->boolean('solo_mios')) {
            $query->where('user_id', auth()->id());
        }

        // NUEVO FILTRO: Solo pagos actualizados por el usuario logueado (para reportes)
        if ($request->has('solo_actualizados_por_mi') && $request->boolean('solo_actualizados_por_mi')) {
            $user = auth()->user();

            // Si es administrador (user_id = 1), ver todos los pagos
            if ($user->id != 1) {
                // Para usuarios no administradores, solo ver los pagos que ellos actualizaron
                $query->where('updated_by', $user->id);
            }
            // Si es administrador, no aplica filtro - ve todos los pagos
        }

        // NUEVO FILTRO: Por usuario específico (para administrador en reportes)
        if ($request->filled('user_id')) {
            $query->where('updated_by', $request->user_id);
        }


        // Búsqueda por filtro general
        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            $query->where(function($q) use ($filter) {
                if (is_numeric($filter)) {
                    $q->orWhere('id', $filter)
                      ->orWhere('boleta_id', $filter);
                }
                $q->orWhere('nombre_completo', 'like', "%$filter%")
                  ->orWhere('solicitud_id', 'like', "%$filter%")
                  ->orWhere('num_documento', 'like', "%$filter%")
                    ->orWhereHas('solicitud', function ($sq) use ($filter) {
                        $sq->where('solicitud_code', 'like', "%$filter%");
                    });
            });
        }

        // Ordenamiento seguro
        $sortBy = $request->input('sortBy', 'id');
        if (!in_array($sortBy, ['id', 'boleta_id', 'nombre_completo', 'num_documento', 'total', 'estado', 'created_at', 'updated_at'])) {
            $sortBy = 'id';
        }
        $descending = $request->boolean('descending', true);
        $direction = $descending ? 'desc' : 'asc';
        $query->orderBy($sortBy, $direction);

        // Si rowsPerPage = -1, devuelve todo
        $perPage = $request->input('rowsPerPage', 7);
        if ($perPage == -1) {
            $data = $query->get();
            return response()->json([
                'data' => $data,
                'total' => $data->count(),
                'current_page' => 1,
                'per_page' => $data->count(),
            ]);
        }

        // Paginación
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



    public function store(StorePagoRequest $request)
    {
        // Solo obtenemos el user_id autenticado
        $userId = auth()->id();


        $data = [
            'solicitud_id'    => $request->solicitud_id,
            'tipo_documento'  => $request->tipo_documento,
            'num_documento'   => $request->num_documento,
            'nombre_completo' => $request->nombre_completo,
            'total'           => $request->total,
            'user_id'         => $userId,
            'created_by' => $userId,
            'updated_by' => $userId,
            'estado'          => $request->has('estado') ? $request->estado : 1,
            'monto_pagado'    => $request->monto_pagado,     // nuevo
            'vuelto'          => $request->vuelto,           // nuevo
            'forma_pago_id'   => $request->forma_pago_id,    // nuevo
            'condicion_pago_id' => $request->condicion_pago_id, // nuevo
            'created_at'      => now(),
            'updated_at'      => now(),
        ];


        // $pago = Pago::create($data);
        $desdeSolicitud = $request->has('desde_solicitud') && $request->desde_solicitud;
        $conPago = $request->has('con_pago') && $request->con_pago; // Puedes enviar este flag desde el frontend

        if ($desdeSolicitud && $conPago) {
            // Solicitud desde formulario y es CON pago: asignar boleta_id
            $ultimoBoletaId = Pago::whereNotNull('boleta_id')->max('boleta_id');
            $nuevoBoletaId = $ultimoBoletaId ? $ultimoBoletaId + 1 : 1;
            $data['boleta_id'] = $nuevoBoletaId;
        } elseif ($desdeSolicitud && !$conPago) {
            // Solicitud desde formulario y es SIN pago: NO asignar boleta_id
            $data['boleta_id'] = null;
        } else {
            // Pago directo desde PagosForm: asignar boleta_id
            // $ultimoBoletaId = Pago::whereNotNull('boleta_id')->max('boleta_id');
            // $nuevoBoletaId = $ultimoBoletaId ? $ultimoBoletaId + 1 : 1;
            // $data['boleta_id'] = $nuevoBoletaId;
                // Pago directo desde PagosForm.vue
            if (isset($data['estado']) && $data['estado'] == 0) {
                // Solo asigna boleta_id si el estado es 0 (pagado)
                $ultimoBoletaId = Pago::whereNotNull('boleta_id')->max('boleta_id');
                $nuevoBoletaId = $ultimoBoletaId ? $ultimoBoletaId + 1 : 1;
                $data['boleta_id'] = $nuevoBoletaId;
            } else {
                // Si el estado es 1 (pendiente), NO asignar boleta_id
                $data['boleta_id'] = null;
            }
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

        // Actualiza los campos principales incluyendo updated_by
        $pago->update(array_merge($request->only([
            'tipo_documento',
            'num_documento',
            'nombre_completo',
            'total',
            'user_id',
            'monto_pagado',      // nuevo
            'vuelto',           // nuevo
            'forma_pago_id',    // nuevo
            'condicion_pago_id' // nuevo
        ]), [
            'updated_by' => auth()->id() // Actualizar con el usuario logueado
        ]));


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




    public function toggleEstado(Request $request, Pago $pago)
    {
        try {
            \Log::info('ToggleEstado iniciado', [
                'pago_id' => $pago->id,
                'estado_actual' => $pago->estado,
                'request_data' => $request->all()
            ]);

            DB::beginTransaction();

            if ($pago->estado == 1) { // Cambiar de pendiente a pagado
                // Obtener el último boleta_id asignado
                $ultimoBoletaId = Pago::whereNotNull('boleta_id')->max('boleta_id');
                $nuevoBoletaId = $ultimoBoletaId ? $ultimoBoletaId + 1 : 1;

                $updateData = [
                    'estado' => 0,
                    'boleta_id' => $nuevoBoletaId,
                    'updated_by' => auth()->id()
                ];

                // CAPTURAR MONTO PAGADO Y VUELTO DEL REQUEST
                if ($request->has('monto_pagado')) {
                    $updateData['monto_pagado'] = $request->monto_pagado;
                }
                if ($request->has('vuelto')) {
                    $updateData['vuelto'] = $request->vuelto;
                }

                \Log::info('Actualizando pago', $updateData);

                $pago->update($updateData);

                // ACTUALIZAR LA SOLICITUD RELACIONADA
                if ($pago->solicitud_id) {
                    $solicitud = Solicitud::find($pago->solicitud_id);
                    if ($solicitud) {
                        $solicitud->estado = 'Buscando';
                        $solicitud->area_id = 2;
                        $solicitud->pago_busqueda = $pago->id;
                        $solicitud->save();
                        \Log::info('Solicitud actualizada', ['solicitud_id' => $solicitud->id]);
                    }
                }

            } else { // Cambiar de pagado a pendiente
                $pago->update([
                    'estado' => 1,
                    'boleta_id' => null,
                    'monto_pagado' => null,
                    'vuelto' => null,
                    'updated_by' => auth()->id()
                ]);
            }

            DB::commit();

            \Log::info('ToggleEstado completado exitosamente', ['pago_id' => $pago->id]);

            return response()->json([
                'message' => 'Estado de pago actualizado correctamente',
                'pago' => $pago,
                'boleta_id' => $pago->boleta_id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error en toggleEstado: ' . $e->getMessage(), [
                'pago_id' => $pago->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Error al actualizar el estado del pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function anular(Pago $pago)
    {
        try {
            if (!$pago) {
                return response()->json(['message' => 'Pago no encontrado'], 404);
            }
            $pago->update(['estado' => null, 'updated_by' => auth()->id() ]);
            return response()->json(['pago' => $pago], 200);
        } catch (\Exception $e) {
            \Log::error('Error al anular pago: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
