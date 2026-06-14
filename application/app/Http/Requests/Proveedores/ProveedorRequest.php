<?php

namespace App\Http\Requests\Proveedores;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'tipo'            => ['required', 'in:natural,juridico'],
            'condiciones_pago' => ['nullable', 'string', 'max:1000'],
            'plazo_dias'      => ['nullable', 'integer', 'min:0'],
        ];

        if ($this->input('tipo') === 'natural') {
            $rules['tipo_identificacion']  = ['required', 'string', 'max:20'];
            $rules['numero_identificacion'] = ['required', 'string', 'max:20'];
            $rules['nombres']              = ['required', 'string', 'max:100'];
            $rules['apellidos']            = ['required', 'string', 'max:100'];
            $rules['email']                = ['nullable', 'email', 'max:150'];
            $rules['telefono']             = ['nullable', 'string', 'max:20'];
            $rules['celular']              = ['nullable', 'string', 'max:20'];
            $rules['direccion']            = ['nullable', 'string', 'max:255'];
            $rules['ciudad']               = ['nullable', 'string', 'max:100'];
        } else {
            $rules['razon_social']         = ['required', 'string', 'max:200'];
            $rules['nit']                  = ['required', 'string', 'max:20'];
            $rules['digito_verificacion']  = ['required', 'string', 'max:2'];
            $rules['regimen_tributario']   = ['required', 'in:responsable_iva,no_responsable_iva'];
            $rules['email']                = ['nullable', 'email', 'max:150'];
            $rules['telefono']             = ['nullable', 'string', 'max:20'];
            $rules['direccion']            = ['nullable', 'string', 'max:255'];
            $rules['ciudad']               = ['nullable', 'string', 'max:100'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'tipo.required'                   => 'El tipo de proveedor es obligatorio.',
            'tipo.in'                          => 'El tipo debe ser natural o jurídico.',
            'condiciones_pago.max'             => 'Las condiciones de pago no pueden superar 1000 caracteres.',
            'plazo_dias.integer'               => 'El plazo de días debe ser un número entero.',
            'plazo_dias.min'                   => 'El plazo de días no puede ser negativo.',
            'tipo_identificacion.required'     => 'El tipo de identificación es obligatorio.',
            'numero_identificacion.required'   => 'El número de identificación es obligatorio.',
            'nombres.required'                 => 'El nombre es obligatorio.',
            'apellidos.required'               => 'Los apellidos son obligatorios.',
            'email.email'                      => 'El correo electrónico no tiene un formato válido.',
            'razon_social.required'            => 'La razón social es obligatoria.',
            'nit.required'                     => 'El NIT es obligatorio.',
            'digito_verificacion.required'     => 'El dígito de verificación es obligatorio.',
            'regimen_tributario.required'      => 'El régimen tributario es obligatorio.',
            'regimen_tributario.in'            => 'El régimen tributario debe ser responsable de IVA o no responsable de IVA.',
        ];
    }
}
