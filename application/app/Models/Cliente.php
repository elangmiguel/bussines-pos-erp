<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'tipo',
        'persona_id',
        'empresa_id',
        'tipo_cliente',
        'credito_activo',
        'limite_credito',
        'plazo_dias',
        'observaciones',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'credito_activo' => 'boolean',
            'activo' => 'boolean',
            'limite_credito' => 'decimal:2',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    public function cuentasPorCobrar(): HasMany
    {
        return $this->hasMany(CuentaPorCobrar::class);
    }

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function getNombreAttribute(): string
    {
        return $this->tipo === 'natural'
            ? ($this->persona?->nombre_completo ?? '')
            : ($this->empresa?->razon_social ?? '');
    }

    public function getIdentificacionAttribute(): string
    {
        return $this->tipo === 'natural'
            ? ($this->persona?->numero_identificacion ?? '')
            : ($this->empresa?->nit ?? '');
    }
}
