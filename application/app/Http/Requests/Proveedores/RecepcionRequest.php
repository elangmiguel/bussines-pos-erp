<?php

namespace App\Http\Requests\Proveedores;

use Illuminate\Foundation\Http\FormRequest;

class RecepcionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_id'                       => ['required', 'exists:ordenes_compra,id'],
            'fecha'                          => ['required', 'date'],
            'observaciones'                  => ['nullable', 'string', 'max:2000'],
            'items'                          => ['required', 'array', 'min:1'],
            'items.*.detalle_id'             => ['required', 'exists:detalles_orden_compra,id'],
            'items.*.cantidad_recibida'      => ['required', 'numeric', 'min:0'],
            'items.*.novedad'                => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'orden_id.required'                    => 'La orden de compra es obligatoria.',
            'orden_id.exists'                      => 'La orden de compra seleccionada no existe.',
            'fecha.required'                       => 'La fecha de recepción es obligatoria.',
            'fecha.date'                           => 'La fecha de recepción no es válida.',
            'items.required'                       => 'Debe registrar al menos un ítem en la recepción.',
            'items.array'                          => 'Los ítems deben ser una lista válida.',
            'items.min'                            => 'Debe registrar al menos un ítem en la recepción.',
            'items.*.detalle_id.required'          => 'Cada ítem debe referenciar un detalle de orden.',
            'items.*.detalle_id.exists'            => 'Uno de los detalles referenciados no existe.',
            'items.*.cantidad_recibida.required'   => 'La cantidad recibida es obligatoria para cada ítem.',
            'items.*.cantidad_recibida.numeric'    => 'La cantidad recibida debe ser un número.',
            'items.*.cantidad_recibida.min'        => 'La cantidad recibida no puede ser negativa.',
            'items.*.novedad.max'                  => 'La novedad no puede superar 500 caracteres.',
        ];
    }
}
