<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use App\Models\Sis_AnteriorModels\Nuevo;
use Illuminate\Http\Request;

class NuevoController extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return  $this->generateViewSetList(
            $request,
            Nuevo::query(),
            ['notario','lugar'], //para el filtrado
            ['id','notario','favorecidos'],  //para la busqueda
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
    public function show(Nuevo $nuevo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nuevo $nuevo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nuevo $nuevo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nuevo $nuevo)
    {
        //
    }
}
