<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class AbonoRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cuenta_cobrar_id' => ['required', 'exists:cuentas_por_cobrar,id'],
            'medio_pago_id'    => ['required', 'exists:medios_pago,id'],
            'monto'            => ['required', 'numeric', 'min:1'],
            'observaciones'    => ['nullable', 'string'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cuenta_cobrar_id.required' => 'La cuenta por cobrar es obligatoria.',
            'cuenta_cobrar_id.exists'   => 'La cuenta por cobrar seleccionada no existe.',
            'medio_pago_id.required'    => 'El medio de pago es obligatorio.',
            'medio_pago_id.exists'      => 'El medio de pago seleccionado no existe.',
            'monto.required'            => 'El monto del abono es obligatorio.',
            'monto.numeric'             => 'El monto debe ser un valor numérico.',
            'monto.min'                 => 'El monto del abono debe ser mayor a cero.',
            'observaciones.string'      => 'Las observaciones deben ser texto.',
        ];
    }
}
