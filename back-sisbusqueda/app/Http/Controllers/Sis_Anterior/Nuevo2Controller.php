<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use App\Models\Sis_AnteriorModels\Nuevo2;
use Illuminate\Http\Request;

class Nuevo2Controller extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return  $this->generateViewSetList(
            $request,
            Nuevo2::query(),
            ['notario','lugar','subserie'], //para el filtrado
            ['notario','lugar'],  //para la busqueda
            ['id','notario','lugar','subserie','fecha','bien','protocolo'] //para el odenamiento
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
    public function show(Nuevo2 $nuevo2)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nuevo2 $nuevo2)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nuevo2 $nuevo2)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nuevo2 $nuevo2)
    {
        //
    }
}
