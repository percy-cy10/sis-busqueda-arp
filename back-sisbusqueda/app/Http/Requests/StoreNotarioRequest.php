<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotarioRequest extends FormRequest
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
            'nombres' => 'required',
            'apellido_paterno' => 'required',
            //'apellido_materno' => 'required',
            'apellido_materno' => 'nullable',
            'año_inicio' => 'required',
            'año_final' => 'required',
        ];
    }
}
