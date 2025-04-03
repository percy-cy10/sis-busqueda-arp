<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLibroRequest;
use App\Models\Libro;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LibroController extends Controller2
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $libros = Libro::orderBy('estado', 'desc')->orderBy('updated_at', 'desc');
        return  $this->generateViewSetList(
            $request,
            $libros,
            $libros->getModel()->getFillable(), //para el filtrado
            $libros->getModel()->getFillable(),  //para la busqueda
            $libros->getModel()->getFillable() //para el odenamiento
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
    public function store(StoreLibroRequest $request)
    {
        return response(Libro::create([
            'protocolo' => $request->protocolo,//'P-'.str_pad(intval($request->protocolo), 6, '0', STR_PAD_LEFT),
            'estado' => 1,
            'user_id' => auth()->user()->id,
            'notario_id' => $request->notario_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]),201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Libro $libro)
    {
        //
        return response()->json($libro);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Libro $libro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreLibroRequest $request, Libro $libro)
    {
        return response($libro->update([
            'protocolo' => $request->protocolo,
            'estado' => $request->estado,
            'notario_id' => $request->notario_id,
            'updated_at' => Carbon::now()
        ]),200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Libro $libro)
    {
        //
    }
}
