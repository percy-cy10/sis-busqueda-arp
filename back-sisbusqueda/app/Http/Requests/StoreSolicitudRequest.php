<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSolicitudRequest extends FormRequest
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
            'notario_id'=>'required',
            'subserie_id'=>'',
            'solicitante_id'=>'',
            'otorgantes'=>'',
            'favorecidos'=>'',
            'anio'=>'required',
            'mes'=>'',
            'dia'=>'',
            'fecha'=>'',
            'ubigeo_cod'=>'',
            'bien'=>'',
            'mas_datos'=>'',
            'tipo_copia'=>'',
            'estado'=>'',
            'pago_busqueda'=>'',
            'area_id'=>'',
            'user_id'=>'',
        ];
    }
}
