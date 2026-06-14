<?php

namespace App\Http\Requests\Proveedores;

use Illuminate\Foundation\Http\FormRequest;

class OrdenCompraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'proveedor_id'           => ['required', 'exists:proveedores,id'],
            'fecha'                  => ['required', 'date'],
            'fecha_esperada'         => ['nullable', 'date', 'after_or_equal:fecha'],
            'observaciones'          => ['nullable', 'string', 'max:2000'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.producto_id'    => ['required', 'exists:productos,id'],
            'items.*.cantidad'       => ['required', 'numeric', 'min:0.001'],
            'items.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'proveedor_id.required'            => 'Debe seleccionar un proveedor.',
            'proveedor_id.exists'              => 'El proveedor seleccionado no existe.',
            'fecha.required'                   => 'La fecha de la orden es obligatoria.',
            'fecha.date'                       => 'La fecha de la orden no es válida.',
            'fecha_esperada.date'              => 'La fecha esperada no es válida.',
            'fecha_esperada.after_or_equal'    => 'La fecha esperada debe ser igual o posterior a la fecha de la orden.',
            'items.required'                   => 'La orden debe tener al menos un producto.',
            'items.array'                      => 'Los ítems deben ser una lista válida.',
            'items.min'                        => 'La orden debe tener al menos un producto.',
            'items.*.producto_id.required'     => 'Cada ítem debe tener un producto seleccionado.',
            'items.*.producto_id.exists'       => 'Uno de los productos seleccionados no existe.',
            'items.*.cantidad.required'        => 'La cantidad de cada ítem es obligatoria.',
            'items.*.cantidad.numeric'         => 'La cantidad debe ser un número.',
            'items.*.cantidad.min'             => 'La cantidad debe ser mayor a cero.',
            'items.*.precio_unitario.required' => 'El precio unitario de cada ítem es obligatorio.',
            'items.*.precio_unitario.numeric'  => 'El precio unitario debe ser un número.',
            'items.*.precio_unitario.min'      => 'El precio unitario no puede ser negativo.',
        ];
    }
}
