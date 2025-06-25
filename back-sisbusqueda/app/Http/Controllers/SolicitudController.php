<?php

namespace App\Http\Controllers;

use App\Models\Precio;
use App\Models\RegistroBusqueda;
use App\Models\Solicitante;
use App\Models\Solicitud;
use App\Models\Tupa;
use Illuminate\Http\Request;

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
            'pago_busqueda' => $request->precio,
            'area_id' => 2,
            'estado'=> 'Buscando',
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
    // public function show(Solicitud $solicitude)
    // {
    //     return response()->json($solicitude);
    // }

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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Solicitud $solicitude)
    {
        //
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

    // public function generateViewSetList(Request $request, $query, $filterFields = [], $searchFields = [], $orderFields = [])
    // {
    //     // --- FILTROS ---
    //     foreach ($filterFields as $field) {
    //         if ($request->filled($field)) {
    //             $query->where($field, $request->input($field));
    //         }
    //     }

    //     // --- BÚSQUEDA GENERAL ---
    //     if ($request->filled('filter')) {
    //         $filter = $request->input('filter');
    //         $query->where(function($q) use ($searchFields, $filter) {
    //             foreach ($searchFields as $field) {
    //                 $q->orWhere($field, 'like', "%$filter%");
    //             }
    //         });
    //     }

    //     // --- ORDENAMIENTO ---
    //     $sortBy = $request->input('sortBy', $orderFields[0] ?? 'id');
    //     $direction = $request->boolean('descending', true) ? 'desc' : 'asc';
    //     if (in_array($sortBy, $orderFields)) {
    //         $query->orderBy($sortBy, $direction);
    //     }

    //     // --- DEVOLVER TODOS SI rowsPerPage = -1 ---
    //     $perPage = $request->input('rowsPerPage', 7);
    //     if ($perPage == -1) {
    //         $data = $query->get();
    //         return response()->json([
    //             'data' => $data,
    //             'total' => $data->count(),
    //             'current_page' => 1,
    //             'per_page' => $data->count(),
    //         ]);
    //     }

    //     // --- PAGINACIÓN NORMAL ---
    //     $paginated = $query->paginate($perPage);

    //     return response()->json([
    //         'data' => $paginated->items(),
    //         'total' => $paginated->total(),
    //         'current_page' => $paginated->currentPage(),
    //         'per_page' => $paginated->perPage(),
    //     ]);
    // }

    // ...existing code...
    public function generateViewSetList(
        \Illuminate\Http\Request $request,
        \Illuminate\Database\Eloquent\Builder $querySet,
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
}
