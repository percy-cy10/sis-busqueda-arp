<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistroVerificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'solicitud_id' => 'required|integer|exists:solicitudes,id',
            'RB_id_derivado' => 'required|integer|exists:registro_busquedas,id',
            'cod_protocolo' => 'required|string|max:255',
            'cod_escritura' => 'required|string|max:255',
            'cod_folioInicial' => 'required|string|max:255',
            'cod_folioFinal' => 'required|string|max:255',
            'observaciones' => 'nullable|string'
        ];
    }
}
