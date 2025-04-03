<?php

namespace App\Http\Controllers;

use App\Models\Precio;
use Illuminate\Http\Request;

class PrecioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->filled('vigencia')) {
            return Precio::where('vigente',$request->vigencia)->get();
        }else{
            $precios = Precio::query();
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Precio $precio)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Precio $precio)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Precio $precio)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Precio $precio)
    {
        //
    }
}
