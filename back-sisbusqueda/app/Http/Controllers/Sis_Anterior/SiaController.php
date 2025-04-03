<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use App\Models\Sis_AnteriorModels\Sia;
use Illuminate\Http\Request;

class SiaController extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return  $this->generateViewSetList(
            $request,
            Sia::query(),
            ['notario','otorgante', 'serie'], //para el filtrado
            ['id','notario','otorgante','favorecido','fecha'],  //para la busqueda
            ['id','notario','otorgante','favorecido','fecha', 'serie','folio','escritura','bien',] //para el odenamiento
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
    public function show(Sia $sia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sia $sia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sia $sia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sia $sia)
    {
        //
    }
}
