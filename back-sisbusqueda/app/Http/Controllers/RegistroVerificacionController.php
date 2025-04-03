<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistroBusquedaRequest;
use App\Models\RegistroBusqueda;
use App\Models\RegistroVerificacion;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class RegistroVerificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            RegistroVerificacion::query(),
            RegistroVerificacion::getModel()->getFillable(),
            ['id', 'nombre'],
            ['id', 'nombre']
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
    public function store(StoreRegistroBusquedaRequest $request)
    {
        Solicitud::find($request->solicitud_id)->update([
            "estado" => "Verificado",
            "area_id" => 1,
            'updated_at'=> now(),
        ]);
        RegistroBusqueda::find($request->RB_id_derivado)->update([
            'cod_protocolo' =>  $request->cod_protocolo,
            'cod_escritura' => $request->cod_escritura,
            'cod_folioInicial' => $request->cod_folioInicial,
            'cod_folioFinal' => $request->cod_folioFinal,
            'updated_at'=> now(),
        ]);
        return response(RegistroVerificacion::create([
            'RB_id_derivado' => $request->RB_id_derivado,
            'user_id' => auth()->user()->id,
            'estado' => 0,
            'observaciones' => $request->observaciones,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(RegistroVerificacion $registro_verificacione)
    {
        return response()->json($registro_verificacione);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegistroVerificacion $registroVerificacion)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegistroVerificacion $registroVerificacion)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegistroVerificacion $registroVerificacion)
    {
        //
    }
}
