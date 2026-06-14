<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProveedorProducto extends Pivot
{
    protected $table = 'proveedores_productos';

    public $timestamps = true;

    protected $fillable = [
        'proveedor_id',
        'producto_id',
        'precio_compra',
        'tiempo_entrega',
        'es_principal',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'es_principal' => 'boolean',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
