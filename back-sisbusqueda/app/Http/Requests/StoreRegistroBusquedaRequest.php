<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistroBusquedaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'solicitud_id' => 'required',
            'cod_protocolo'=> 'required',
            'cod_escritura'=> 'required',
            'cod_folioInicial'=> 'required',
            'cod_folioFinal'=> 'required',
        ];
    }
}
