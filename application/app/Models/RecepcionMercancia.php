<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecepcionMercancia extends Model
{
    protected $table = 'recepciones_mercancia';

    protected $fillable = [
        'orden_id',
        'user_id',
        'fecha',
        'observaciones',
        'estado',
    ];

    protected function casts(): array
    {
        return ['fecha' => 'datetime'];
    }

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenCompra::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRecepcion::class, 'recepcion_id');
    }
}
