<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaPorCobrar extends Model
{
    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'cliente_id',
        'factura_id',
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

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function abonos(): HasMany
    {
        return $this->hasMany(AbonoCartera::class, 'cuenta_cobrar_id');
    }

    public function getSaldoAttribute(): float
    {
        return round($this->monto_total - $this->monto_pagado, 2);
    }

    public function scopePendiente(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'parcial']);
    }

    public function scopeVencida(Builder $query): Builder
    {
        return $query->where('fecha_vencimiento', '<', now()->toDateString())
            ->whereIn('estado', ['pendiente', 'parcial']);
    }
}
