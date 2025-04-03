<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotarioRequest;
use App\Models\Notario;
use Illuminate\Http\Request;

class NotarioController extends Controller
{
    
    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            Notario::query(),
            [],
            ['id', 'nombre_completo'],
            ['id', 'nombre_completo']
        );
    }

    
    public function store(StoreNotarioRequest $request)
    {
        return response(Notario::create($request->all()), 201);
    }

    
    public function show(Notario $notario)
    {
        return response()->json($notario);
    }

    
    public function update(StoreNotarioRequest $request, Notario $notario)
    {
        $notario->update($request->all());

        return response()->json([$request, $notario]);
    }

    
    public function destroy(Notario $notario)
    {
        return response()->json($notario->delete());
    }
}
