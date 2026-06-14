<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'numero',
        'prefijo',
        'numero_completo',
        'resolucion_id',
        'turno_caja_id',
        'cliente_id',
        'user_id',
        'fecha',
        'fecha_vencimiento',
        'tipo_pago',
        'subtotal',
        'descuento_global',
        'base_iva_0',
        'base_iva_5',
        'base_iva_19',
        'iva_5',
        'iva_19',
        'inc',
        'total',
        'estado',
        'cufe',
        'qr_data',
        'xml_dian',
        'estado_dian',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'fecha_vencimiento' => 'date',
            'subtotal' => 'decimal:2',
            'descuento_global' => 'decimal:2',
            'base_iva_0' => 'decimal:2',
            'base_iva_5' => 'decimal:2',
            'base_iva_19' => 'decimal:2',
            'iva_5' => 'decimal:2',
            'iva_19' => 'decimal:2',
            'inc' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function resolucion(): BelongsTo
    {
        return $this->belongsTo(ResolucionDian::class, 'resolucion_id');
    }

    public function turnoCaja(): BelongsTo
    {
        return $this->belongsTo(TurnoCaja::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleFactura::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoFactura::class);
    }

    public function notasCredito(): HasMany
    {
        return $this->hasMany(NotaCredito::class);
    }

    public function notasDebito(): HasMany
    {
        return $this->hasMany(NotaDebito::class);
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CuentaPorCobrar::class);
    }

    public function scopeEmitida(Builder $query): Builder
    {
        return $query->where('estado', 'emitida');
    }

    public function scopeAnulada(Builder $query): Builder
    {
        return $query->where('estado', 'anulada');
    }

    public function scopePendienteDian(Builder $query): Builder
    {
        return $query->where('estado_dian', 'pendiente');
    }
}
