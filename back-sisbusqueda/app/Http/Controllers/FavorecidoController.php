<?php

namespace App\Http\Controllers;

use App\Models\Favorecido;
use Illuminate\Http\Request;

class FavorecidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        return $this->generateViewSetList(
            $request,
            Favorecido::query(),
            [],
            ['nombre_completo'],
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
    public function store(Request $request)
    {
        return Favorecido::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Favorecido $favorecido)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Favorecido $favorecido)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Favorecido $favorecido)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Favorecido $favorecido)
    {
        //
    }
}
