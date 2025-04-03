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
            'solicitud_id'=>'required',
            'pago_busqueda'=>'required',
            // 'pago_verificacion'=>'required',
            'cantidad_folio'=>'required',
            'pago_folio'=>'required',
            'cantidad_fotocopia'=>'required',
            'pago_fotocopia'=>'required',
        ];
    }
}
