<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoFactura extends Model
{
    protected $table = 'pagos_factura';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'factura_id',
        'medio_pago_id',
        'monto',
        'referencia',
    ];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2'];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class);
    }

    public function medioPago(): BelongsTo
    {
        return $this->belongsTo(MedioPago::class);
    }
}
