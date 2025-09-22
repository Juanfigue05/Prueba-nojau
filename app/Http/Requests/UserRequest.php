<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'name' => 'required|string|max:40',
            'email' => 'required|string|max:255|email',
            'phone' => 'required|string|max:16',
            'dni' => 'required|string|max:16',
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'email.required' => 'El correo es obligatorio',
            'email.email' => 'En este campo debe ir un correo válido',
            'phone.required' => 'El teléfono es obligatorio',
            'phone.max'=> 'El teléfono no debe ser mayor a 16 caracteres incluyendo símbolos(+ - .)',
            'dni.required' => 'El DNI es obligatorio',
            'dni.max'=> 'El DNI no debe ser mayor a 16 caracteres',
        ];
    }
}
