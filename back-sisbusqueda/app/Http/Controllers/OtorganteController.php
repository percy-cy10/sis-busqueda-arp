<?php

namespace App\Http\Controllers;

use App\Models\Otorgante;
use Illuminate\Http\Request;

class OtorganteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        return $this->generateViewSetList(
            $request,
            Otorgante::query(),
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
        return Otorgante::create($request->all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Otorgante $otorgante)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Otorgante $otorgante)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Otorgante $otorgante)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Otorgante $otorgante)
    {
        //
    }
}
