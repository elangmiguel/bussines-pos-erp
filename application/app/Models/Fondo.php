<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fondo extends Model
{
    protected $table = 'fondos';

    protected $fillable = ['nombre', 'tipo', 'medio_pago_id', 'saldo_actual', 'activo'];

    protected function casts(): array
    {
        return [
            'saldo_actual' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoFondo::class);
    }
}
