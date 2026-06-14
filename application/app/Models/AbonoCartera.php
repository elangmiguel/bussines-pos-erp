<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbonoCartera extends Model
{
    protected $table = 'abonos_cartera';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'cuenta_cobrar_id',
        'medio_pago_id',
        'monto',
        'fecha',
        'user_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha' => 'datetime',
        ];
    }

    public function cuentaPorCobrar(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_cobrar_id');
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
