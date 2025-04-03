<?php

namespace App\Http\Controllers;

use App\Models\Tupa;
use Illuminate\Http\Request;

class TupaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->filled('precio_sub_code')) {
            $derecho_pago  = Tupa::select('derecho_pago')->where('sub_code',$request->precio_sub_code)->first()['derecho_pago'];
            $UIT = Tupa::select('costo')->where('code','00')->first()['costo'];
            return $derecho_pago * $UIT;
        }else{
            $precios = Tupa::query();
            $columnas = $precios->getModel()->getFillable();
            return  $this->generateViewSetList(
                $request,
                $precios,
                $columnas, //para el filtrado
                $columnas,  //para la busqueda
                $columnas //para el odenamiento
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tupa $tupa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tupa $tupa)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tupa $tupa)
    {
        //
    }
}
