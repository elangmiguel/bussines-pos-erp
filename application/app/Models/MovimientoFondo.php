<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoFondo extends Model
{
    protected $table = 'movimientos_fondo';

    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'fondo_id',
        'tipo',
        'monto',
        'descripcion',
        'referencia_tipo',
        'referencia_id',
        'user_id',
    ];

    protected function casts(): array
    {
        return ['monto' => 'decimal:2'];
    }

    public function fondo(): BelongsTo
    {
        return $this->belongsTo(Fondo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
