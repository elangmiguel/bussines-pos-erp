<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productoId = $this->route('producto')?->id;

        return [
            'codigo' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('productos', 'codigo')->ignore($productoId)->whereNull('deleted_at'),
            ],
            'codigo_barras' => ['nullable', 'string', 'max:100'],
            'nombre'        => ['required', 'string', 'max:200'],
            'descripcion'   => ['nullable', 'string', 'max:1000'],
            'categoria_id'  => ['nullable', 'integer', 'exists:categorias_producto,id'],
            'unidad_medida_id' => ['required', 'integer', 'exists:unidades_medida,id'],
            'tarifa_iva_id'    => ['required', 'integer', 'exists:tarifas_iva,id'],
            'precio_compra'    => ['required', 'numeric', 'min:0'],
            'precio_venta'     => ['required', 'numeric', 'min:0'],
            'precio_mayorista' => ['nullable', 'numeric', 'min:0'],
            'stock_minimo'     => ['required', 'numeric', 'min:0'],
            'stock_maximo'     => ['nullable', 'numeric', 'min:0'],
            'activo'           => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.unique'              => 'Ya existe un producto con este código.',
            'codigo.max'                 => 'El código no puede superar los 50 caracteres.',
            'nombre.required'            => 'El nombre del producto es obligatorio.',
            'nombre.max'                 => 'El nombre no puede superar los 200 caracteres.',
            'categoria_id.exists'        => 'La categoría seleccionada no existe.',
            'unidad_medida_id.required'  => 'La unidad de medida es obligatoria.',
            'unidad_medida_id.exists'    => 'La unidad de medida seleccionada no existe.',
            'tarifa_iva_id.required'     => 'La tarifa de IVA es obligatoria.',
            'tarifa_iva_id.exists'       => 'La tarifa de IVA seleccionada no existe.',
            'precio_compra.required'     => 'El precio de compra es obligatorio.',
            'precio_compra.numeric'      => 'El precio de compra debe ser un número.',
            'precio_compra.min'          => 'El precio de compra no puede ser negativo.',
            'precio_venta.required'      => 'El precio de venta es obligatorio.',
            'precio_venta.numeric'       => 'El precio de venta debe ser un número.',
            'precio_venta.min'           => 'El precio de venta no puede ser negativo.',
            'precio_mayorista.numeric'   => 'El precio mayorista debe ser un número.',
            'precio_mayorista.min'       => 'El precio mayorista no puede ser negativo.',
            'stock_minimo.required'      => 'El stock mínimo es obligatorio.',
            'stock_minimo.numeric'       => 'El stock mínimo debe ser un número.',
            'stock_minimo.min'           => 'El stock mínimo no puede ser negativo.',
            'stock_maximo.numeric'       => 'El stock máximo debe ser un número.',
            'stock_maximo.min'           => 'El stock máximo no puede ser negativo.',
            'activo.boolean'             => 'El campo activo debe ser verdadero o falso.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->boolean('activo', true),
        ]);
    }
}
