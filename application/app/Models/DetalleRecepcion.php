<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleRecepcion extends Model
{
    protected $table = 'detalles_recepcion';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'recepcion_id',
        'producto_id',
        'cantidad_esperada',
        'cantidad_recibida',
        'precio_unitario',
        'novedad',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_esperada' => 'decimal:3',
            'cantidad_recibida' => 'decimal:3',
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionMercancia::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
