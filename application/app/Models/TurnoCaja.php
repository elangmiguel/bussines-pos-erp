<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TurnoCaja extends Model
{
    protected $table = 'turnos_caja';

    protected $fillable = [
        'caja_id',
        'cajero_id',
        'saldo_inicial',
        'saldo_final',
        'apertura',
        'cierre',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'saldo_inicial' => 'decimal:2',
            'saldo_final' => 'decimal:2',
            'apertura' => 'datetime',
            'cierre' => 'datetime',
        ];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class);
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(Cajero::class);
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class);
    }

    public function scopeAbierto(Builder $query): Builder
    {
        return $query->where('estado', 'abierto');
    }
}
