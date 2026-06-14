<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    protected $table = 'gastos';

    protected $fillable = [
        'categoria_id',
        'descripcion',
        'monto',
        'iva',
        'fecha',
        'proveedor_id',
        'medio_pago_id',
        'user_id',
        'numero_documento',
        'comprobante',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'iva' => 'decimal:2',
            'fecha' => 'date',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaGasto::class, 'categoria_id');
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalAttribute(): float
    {
        return round($this->monto + $this->iva, 2);
    }
}
