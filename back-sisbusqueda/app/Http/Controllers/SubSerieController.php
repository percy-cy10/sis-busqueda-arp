<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubSerieRequest;
use App\Models\SubSerie;
use Illuminate\Http\Request;

class SubSerieController extends Controller
{

    public function index(Request $request)
    {
        return $this->generateViewSetList(
            $request,
            SubSerie::query(),
            [],
            ['id', 'nombre'],
            ['id', 'nombre']
        );
    }


    public function store(StoreSubSerieRequest $request)
    {
        return response(SubSerie::create($request->all()), 201);
    }


    public function show(SubSerie $subseries)
    {
        return response()->json($subseries);
    }


    public function update(StoreSubSerieRequest $request, SubSerie $subseries)
    {
        $subseries->update($request->all());

        return response()->json([$request, $subseries]);
    }


    public function destroy(SubSerie $subseries)
    {
        return response()->json($subseries->delete());
    }
}
