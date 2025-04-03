<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use App\Models\Sis_AnteriorModels\Arbolito;
use Illuminate\Http\Request;

class ArbolitoController extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return  $this->generateViewSetList(
            $request,
            Arbolito::query(),
            ['otorgante','favorecido','protocolo'], //para el filtrado
            ['id','otorgante','favorecido','fecha'],  //para la busqueda
            ['id','otorgante','favorecido','fecha','protocolo','escritura','folio','bien','tmp'] //para el odenamiento
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Arbolito $arbolito)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Arbolito $arbolito)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Arbolito $arbolito)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Arbolito $arbolito)
    {
        //
    }
}
