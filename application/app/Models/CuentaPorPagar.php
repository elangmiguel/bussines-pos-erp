<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaPorPagar extends Model
{
    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'proveedor_id',
        'orden_id',
        'monto_total',
        'monto_pagado',
        'fecha_vencimiento',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    public function getSaldoAttribute(): float
    {
        return round($this->monto_total - $this->monto_pagado, 2);
    }

    public function scopeVencida(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '<', now()->toDateString())
            ->whereIn('estado', ['pendiente', 'parcial']);
    }
}
