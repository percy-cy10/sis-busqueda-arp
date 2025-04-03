<?php

namespace App\Http\Controllers\Sis_Anterior;

use App\Http\Controllers\Controller2;
use App\Models\Sis_AnteriorModels\Sis2018;
use Illuminate\Http\Request;

class Sis2018Controller extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return  $this->generateViewSetList(
            $request,
            Sis2018::query(),
            ['subserie','otorgantes', 'favorecidos'], //para el filtrado
            ['id','notario','otorgantes','favorecidos',],  //para la busqueda
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
    public function show(Sis2018 $sis2018)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sis2018 $sis2018)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sis2018 $sis2018)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sis2018 $sis2018)
    {
        //
    }
}
