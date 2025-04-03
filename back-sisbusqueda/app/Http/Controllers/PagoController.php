<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePagoRequest;
use App\Models\Pago;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pagos = Pago::query();
        $columnas = $pagos->getModel()->getFillable();
        return  $this->generateViewSetList(
            $request,
            $pagos,
            $columnas, //para el filtrado
            $columnas,  //para la busqueda
            $columnas //para el odenamiento
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePagoRequest $request)
    {
        Solicitud::find($request->solicitud_id)->update([
            "estado" => "Finalizado",
            "tipo_copia" => $request->tipo_copia,
            'updated_at'=> now(),
            'area_id' => 4,
        ]);
        return response(Pago::create([
            'solicitud_id'=> $request->solicitud_id,
            'pago_busqueda'=> $request->pago_busqueda,
            'pago_verificacion'=> $request->pago_verificacion,
            'cantidad_folio'=> $request->cantidad_folio,
            'pago_folio'=> $request->pago_folio,
            'cantidad_fotocopia'=> $request->cantidad_fotocopia,
            'pago_fotocopia'=> $request->pago_fotocopia,
            'total'=> $request->pago_busqueda + $request->pago_verificacion + $request->pago_folio + $request->pago_fotocopia,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Pago $pago)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pago $pago)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pago $pago)
    {
        //
    }
}
