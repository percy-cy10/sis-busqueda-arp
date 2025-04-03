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
        return $this->generateViewSetList(
            $request,
            Solicitud::orderBy('updated_at', 'desc'),
            ['area_id','estado','user_id'],
            ['id'],
            ['id'],
            // $solicitudesConUbigeos,
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
    public function show(Solicitud $solicitude)
    {
        return response()->json($solicitude);
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
}
