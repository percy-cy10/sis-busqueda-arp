<?php

namespace App\Http\Controllers;

use App\Models\Precio;
use App\Models\RegistroBusqueda;
use App\Models\Solicitante;
use App\Models\Solicitud;
use App\Models\Tupa;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;


class SolicitudController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // return $this->generateViewSetList(
        //     $request,
        //     Solicitud::orderBy('updated_at', 'desc'),
        //     ['area_id','estado','user_id'],
        //     ['id'],
        //     ['id'],
        //     // $solicitudesConUbigeos,
        // );
        return $this->generateViewSetList(
            $request,
            Solicitud::orderBy('updated_at', 'desc'),
            ['area_id','estado','user_id'],
            ['id'],
            ['id']
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $existeSolicitante = Solicitante::where('num_documento',$request->num_documento)->first();
        $id_solicitabte = null;
        if($existeSolicitante){
            $id_solicitabte = $existeSolicitante->id;
            Solicitante::find($id_solicitabte)->update([
                'nombres' => $request->nombres===''?null:$request->nombres,
                'apellido_paterno' => $request->apellido_paterno===''?null:$request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno===''?null:$request->apellido_materno,
                'nombre_completo' => $request->nombre_completo===''?null:$request->nombre_completo,
                'asunto' => $request->asunto===''?null:$request->asunto,
                'tipo_documento' => $request->tipo_documento,
                'num_documento' => $request->num_documento,
                'direccion' => $request->direccion,
                'correo' => $request->correo,
                'celular' => $request->celular,
                'ubigeo_cod' => $request->ubigeo_cod,
            ]);
        }else{
            $solicitante = Solicitante::create([
                'nombres' => $request->nombres===''?null:$request->nombres,
                'apellido_paterno' => $request->apellido_paterno===''?null:$request->apellido_paterno,
                'apellido_materno' => $request->apellido_materno===''?null:$request->apellido_materno,
                'nombre_completo' => $request->nombre_completo===''?null:$request->nombre_completo,
                'asunto' => $request->asunto===''?null:$request->asunto,
                'tipo_documento' => $request->tipo_documento,
                'num_documento' => $request->num_documento,
                'direccion' => $request->direccion,
                'correo' => $request->correo,
                'celular' => $request->celular,
                'ubigeo_cod' => $request->ubigeo_cod,
            ]);
            $id_solicitabte = $solicitante->id;
        }
        $uit = \env('PAGO');

        Solicitud::create([
            'notario_id' => $request->notario_id,
            'subserie_id'=> $request->subserie_id,
            'solicitante_id'=> $id_solicitabte,
            'otorgantes'=> $request->otorgantes,
            'favorecidos'=> $request->favorecidos,
            'anio'=> $request->anio,
            'mes'=> $request->mes,
            'dia'=> $request->dia,
            'fecha'=> $request->anio?$request->anio.'/'.($request->mes?str_pad($request->mes, 2, '0', STR_PAD_LEFT):'01').'/'.($request->dia?str_pad($request->dia, 2, '0', STR_PAD_LEFT):'01'):null,
            'ubigeo_cod'=> $request->ubigeo_cod_soli,
            'bien'=> $request->bien,
            'mas_datos'=> $request->mas_datos,
            //'pago_busqueda' => $request->precio,
            'pago_busqueda' => null, // No guardar monto ni precio aquí
            // 'pago_busqueda' => null,
            'segundo_pago' => null,
            'orden_pago' => null,
            'area_id' => $request->has('con_pago') && $request->con_pago ? 2 : 1,
            'estado'  => $request->has('con_pago') && $request->con_pago ? 'Buscando' : 'Pago pendiente',
            // 'area_id' => 2,
            // 'estado'=> 'Pago pendiente',// Estado inicial correcto
            'user_id' =>auth()->user()->id,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        // return response($this->RegistrarABusqueda($solicitud),201);

        return response([$request],201);
    }

    /**
     * Display the specified resource.
     */


    public function show(Solicitud $solicitude)
    {
        return response()->json(
            $solicitude->load(['ubigeo', 'solicitante.ubigeo', 'notario', 'sub_serie'])
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Solicitud $solicitude)
    {
        //

    }


    // public function update(Request $request, Solicitud $solicitude)
    // {


    //     if ($request->has('con_pago') && $request->con_pago) {
    //         $solicitude->estado = 'Buscando';
    //         $solicitude->area_id = 2;
    //     } else {
    //         // Si no es con pago, área 1 y estado 'Pago pendiente'
    //         $solicitude->estado = 'Pago pendiente';
    //         $solicitude->area_id = 1;
    //     }
    //     $solicitude->save();
    //     return response()->json($solicitude);
    // }

    public function update(Request $request, Solicitud $solicitude)
    {
        // Actualiza los campos enviados en el request
        $solicitude->fill($request->only([
            'pago_busqueda',
            'estado',
            'area_id',
            // otros campos si es necesario
        ]));

        // Mantén la lógica de con_pago si quieres
        if ($request->has('con_pago') && $request->con_pago) {
            $solicitude->estado = 'Buscando';
            $solicitude->area_id = 2;
        } else if ($request->has('con_pago')) {
            $solicitude->estado = 'Pago pendiente';
            $solicitude->area_id = 1;
        }

        $solicitude->save();
        return response()->json($solicitude);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Solicitud $solicitude)
    {
        return response()->json($solicitude->delete());
    }

    private function RegistrarABusqueda(Solicitud $solicitude){
        return RegistroBusqueda::create([
            'solicitud_id' =>$solicitude->id,
            'estado' => 0,
        ]);
    }


    // ...existing code...
    public function generateViewSetList(Request $request, Builder $querySet,
        array $filterBy,
        array $searchBy,
        array $orderBy,
        array $relationFields = []
    ) {
        // --- FILTROS ---
        foreach ($filterBy as $field) {
            if ($request->filled($field)) {
                $querySet->where($field, $request->input($field));
            }
        }

        // --- BÚSQUEDA GENERAL ---
        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            $querySet->where(function($q) use ($searchBy, $filter) {
                foreach ($searchBy as $field) {
                    $q->orWhere($field, 'like', "%$filter%");
                }
                // Buscar en la relación solicitante: num_documento, nombre_completo, asunto
                $q->orWhereHas('solicitante', function($q2) use ($filter) {
                    $q2->where('num_documento', 'like', "%$filter%")
                    ->orWhere('nombre_completo', 'like', "%$filter%")
                    ->orWhere('asunto', 'like', "%$filter%");
                });
            });
        }


        // --- ORDENAMIENTO ---
        $sortBy = $request->input('sortBy', $orderBy[0] ?? 'id');
        $direction = $request->boolean('descending', true) ? 'desc' : 'asc';
        if (in_array($sortBy, $orderBy)) {
            $querySet->orderBy($sortBy, $direction);
        }

        // --- DEVOLVER TODOS SI rowsPerPage = -1 ---
        $perPage = $request->input('rowsPerPage', 7);
        if ($perPage == -1) {
            $data = $querySet->get();
            return response()->json([
                'data' => $data,
                'total' => $data->count(),
                'current_page' => 1,
                'per_page' => $data->count(),
            ]);
        }

        // --- PAGINACIÓN NORMAL ---
        $paginated = $querySet->paginate($perPage);

        return response()->json([
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'per_page' => $paginated->perPage(),
        ]);
    }

    //PAGOS

    public function registrarPagoBusqueda($solicitudId, $pagoId)
    {
        $solicitud = Solicitud::findOrFail($solicitudId);
        $pago = Pago::findOrFail($pagoId);

        // Validar que el pago corresponde a búsqueda de documentos
        $tupaBusqueda = $pago->tupas()->where('code', '01')->where('sub_code', '0101')->first();
        if ($tupaBusqueda) {
            $solicitud->pago_busqueda = $pago->id;
            $solicitud->estado = 'Buscando';
            $solicitud->area_id = 2;
        } else {
            $solicitud->estado = 'Pago pendiente';
        }
        $solicitud->save();
    }

    public function registrarSegundoPago($solicitudId, $pagoId)
    {
        $solicitud = Solicitud::findOrFail($solicitudId);
        $pago = Pago::findOrFail($pagoId);

        $solicitud->segundo_pago = $pago->id;
        $solicitud->estado = 'Finalizado';
        $solicitud->save();
    }

    public function asignarOrdenPago($solicitudId, $pagoId)
    {
        $solicitud = Solicitud::findOrFail($solicitudId);
        $pago = Pago::findOrFail($pagoId);

        $solicitud->orden_pago = $pago->id;
        $solicitud->estado = 'Por pagar';
        $solicitud->save();
    }
}
