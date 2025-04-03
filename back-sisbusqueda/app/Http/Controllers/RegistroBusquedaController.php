<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistroBusquedaRequest;
use App\Models\RegistroBusqueda;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class RegistroBusquedaController extends Controller
{

    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            RegistroBusqueda::query()
            ->leftJoin('users','users.id', '=','registro_busquedas.user_id')
            ->leftJoin('solicituds','solicituds.id', '=','registro_busquedas.solicitud_id')
            ->select('registro_busquedas.*','users.*','solicituds.*'),
            RegistroBusqueda::getModel()->getFillable(),
            ['id'],
            ['id'],
        );
    }

    public function create()
    {
        //
    }

    public function store(StoreRegistroBusquedaRequest $request)
    {
        Solicitud::find($request->solicitud_id)->update([
            "estado" => "Verificación",
            "area_id" => 3,
            'updated_at'=> now(),
        ]);
        return response(RegistroBusqueda::create([
            'solicitud_id' => $request->solicitud_id,
            'user_id' => auth()->user()->id,
            'estado' => 0,
            'cod_protocolo' =>  $request->cod_protocolo,
            'cod_escritura' => $request->cod_escritura,
            'cod_folioInicial' => $request->cod_folioInicial,
            'cod_folioFinal' => $request->cod_folioFinal,
            'observaciones' => $request->observaciones,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]), 201);
    }


    public function show(RegistroBusqueda $registroBusqueda)
    {
        // $solicitude = Solicitud::find($id);
        return response()->json($registroBusqueda);
    }

    // public function show($id)
    // {
    //     $solicitude = Solicitud::find($id);
    //     return response()->json($solicitude);
    // }


    public function edit(RegistroBusqueda $registroBusqueda)
    {
        //
    }


    public function update(Request $request, $id)
    {
        $solicitude = Solicitud::find($id);
        return response()->json([$solicitude]);
    }


    public function destroy(RegistroBusqueda $registroBusqueda)
    {
        //
    }
}
