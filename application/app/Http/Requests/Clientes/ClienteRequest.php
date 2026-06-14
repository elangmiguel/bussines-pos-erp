<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class ClienteRequest extends FormRequest
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
        $rules = [
            'tipo'          => ['required', 'in:natural,juridico'],
            'tipo_cliente'  => ['required', 'in:regular,frecuente,corporativo'],
            'credito_activo' => ['boolean'],
            'limite_credito' => ['required_if:credito_activo,true', 'nullable', 'numeric', 'min:0'],
            'plazo_dias'    => ['required_if:credito_activo,true', 'nullable', 'integer', 'min:0'],
            'observaciones' => ['nullable', 'string'],
        ];

        if ($this->input('tipo') === 'natural') {
            $rules = array_merge($rules, [
                'tipo_identificacion'  => ['required', 'in:CC,CE,TI,PAS,RC'],
                'numero_identificacion' => ['required', 'string', 'max:20'],
                'nombres'              => ['required', 'string', 'max:100'],
                'apellidos'            => ['nullable', 'string', 'max:100'],
                'email'                => ['nullable', 'email'],
                'telefono'             => ['nullable', 'string'],
                'celular'              => ['nullable', 'string'],
                'direccion'            => ['nullable', 'string'],
                'ciudad'               => ['nullable', 'string'],
            ]);
        }

        if ($this->input('tipo') === 'juridico') {
            $rules = array_merge($rules, [
                'razon_social'        => ['required', 'string', 'max:200'],
                'nit'                 => ['required', 'string', 'max:15'],
                'digito_verificacion' => ['required', 'string', 'size:1'],
                'regimen_tributario'  => ['required', 'in:responsable_iva,no_responsable_iva'],
                'email'               => ['nullable', 'email'],
                'telefono'            => ['nullable', 'string'],
                'direccion'           => ['nullable', 'string'],
                'ciudad'              => ['nullable', 'string'],
            ]);
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tipo.required'                  => 'El tipo de cliente es obligatorio.',
            'tipo.in'                        => 'El tipo de cliente debe ser natural o jurídico.',
            'tipo_cliente.required'          => 'La categoría del cliente es obligatoria.',
            'tipo_cliente.in'                => 'La categoría debe ser regular, frecuente o corporativo.',
            'credito_activo.boolean'         => 'El campo crédito activo debe ser verdadero o falso.',
            'limite_credito.required_if'     => 'El límite de crédito es obligatorio cuando el crédito está activo.',
            'limite_credito.numeric'         => 'El límite de crédito debe ser un valor numérico.',
            'limite_credito.min'             => 'El límite de crédito no puede ser negativo.',
            'plazo_dias.required_if'         => 'El plazo en días es obligatorio cuando el crédito está activo.',
            'plazo_dias.integer'             => 'El plazo en días debe ser un número entero.',
            'plazo_dias.min'                 => 'El plazo en días no puede ser negativo.',
            'observaciones.string'           => 'Las observaciones deben ser texto.',
            // Natural
            'tipo_identificacion.required'   => 'El tipo de identificación es obligatorio.',
            'tipo_identificacion.in'         => 'El tipo de identificación no es válido.',
            'numero_identificacion.required' => 'El número de identificación es obligatorio.',
            'numero_identificacion.max'      => 'El número de identificación no puede superar los 20 caracteres.',
            'nombres.required'               => 'El nombre es obligatorio.',
            'nombres.max'                    => 'El nombre no puede superar los 100 caracteres.',
            'apellidos.max'                  => 'Los apellidos no pueden superar los 100 caracteres.',
            'email.email'                    => 'El correo electrónico no tiene un formato válido.',
            'celular.string'                 => 'El celular debe ser texto.',
            // Jurídico
            'razon_social.required'          => 'La razón social es obligatoria.',
            'razon_social.max'               => 'La razón social no puede superar los 200 caracteres.',
            'nit.required'                   => 'El NIT es obligatorio.',
            'nit.max'                        => 'El NIT no puede superar los 15 caracteres.',
            'digito_verificacion.required'   => 'El dígito de verificación es obligatorio.',
            'digito_verificacion.size'       => 'El dígito de verificación debe ser un solo carácter.',
            'regimen_tributario.required'    => 'El régimen tributario es obligatorio.',
            'regimen_tributario.in'          => 'El régimen tributario seleccionado no es válido.',
        ];
    }
}
