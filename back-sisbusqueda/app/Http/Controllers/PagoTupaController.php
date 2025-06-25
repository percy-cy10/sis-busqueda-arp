<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagoTupaController extends Controller
{
    // Listar todos los registros de la tabla pivote
    public function index()
    {
        $pagosTupa = DB::table('pagos_tupa')->get();
        return response()->json($pagosTupa);
    }

    // Asociar un tupa a un pago
    public function store(Request $request)
    {
        $data = $request->validate([
            'pagos_id' => 'required|exists:pagos,id',
            'tupa_id' => 'required|exists:tupas,id',
            'cantidad' => 'required|integer',
            'Subtotal' => 'required|numeric',
        ]);

        DB::table('pagos_tupa')->insert([
            'pagos_id' => $data['pagos_id'],
            'tupa_id' => $data['tupa_id'],
            'cantidad' => $data['cantidad'],
            'Subtotal' => $data['Subtotal'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Registro creado correctamente'], 201);
    }

    // Eliminar una relación
    public function destroy($id)
    {
        DB::table('pagos_tupa')->where('id', $id)->delete();
        return response()->json(['message' => 'Registro eliminado']);
    }
}
