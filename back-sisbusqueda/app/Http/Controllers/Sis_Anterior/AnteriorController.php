<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use App\Models\Sis_AnteriorModels\Anterior;
use Illuminate\Http\Request;

class AnteriorController extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // $tempTable = Anterior::query();

        $tempTable = Anterior::query();

        return  $this->generateViewSetList(
            $request,
            $tempTable,
            $tempTable->getModel()->getFillable(), //para el filtrado
            ['notario','subserie','otorgantes','favorecidos'],  //para la busqueda
            ['id','notario','lugar','subserie','fecha','bien','protocolo','nescritura','folio','cfolio','trabajador'.'otorgantes'] //para el odenamiento
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
    public function show(Anterior $anterior)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anterior $anterior)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anterior $anterior)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anterior $anterior)
    {
        //
    }
}
