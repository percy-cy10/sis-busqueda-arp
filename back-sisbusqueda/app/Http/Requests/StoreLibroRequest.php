<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLibroRequest extends FormRequest
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
            'protocolo' => 'required|unique:libros,protocolo,' . $this->route('libro')->id,
            'notario_id' => 'required|exists:notarios,id',
            'estado' => 'nullable|boolean',
            // 'estado' => 'required|in:0,1,true,false',

        ];
    }
}
