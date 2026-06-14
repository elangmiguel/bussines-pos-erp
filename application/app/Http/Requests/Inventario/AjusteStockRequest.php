<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class AjusteStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'producto_id'    => ['required', 'integer', 'exists:productos,id'],
            'nueva_cantidad' => ['required', 'numeric', 'min:0'],
            'motivo'         => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'producto_id.required'    => 'El producto es obligatorio.',
            'producto_id.exists'      => 'El producto seleccionado no existe.',
            'nueva_cantidad.required' => 'La nueva cantidad es obligatoria.',
            'nueva_cantidad.numeric'  => 'La nueva cantidad debe ser un número.',
            'nueva_cantidad.min'      => 'La nueva cantidad no puede ser negativa.',
            'motivo.required'         => 'El motivo del ajuste es obligatorio.',
            'motivo.min'              => 'El motivo debe tener al menos 5 caracteres.',
            'motivo.max'              => 'El motivo no puede superar los 500 caracteres.',
        ];
    }
}
