<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePagoRequest extends FormRequest
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
        'solicitud_id'    => 'nullable|exists:solicituds,id',
        'tipo_documento'  => 'required|string',
        'num_documento'   => 'required|string',
        'nombre_completo' => 'required|string',
        'total'           => 'required|numeric',
        'user_id'         => 'nullable|exists:users,id',
        'tipo_copia'      => 'nullable|string',
        'tupas'           => 'required|array|min:1',
        'tupas.*.tupa_id' => 'required|exists:tupas,id',
        'tupas.*.cantidad'=> 'required|integer|min:1',
        'tupas.*.Subtotal'=> 'required|numeric|min:0',
    ];
    }
}
