<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedioPago extends Model
{
    protected $table = 'medios_pago';

    protected $fillable = ['nombre', 'tipo', 'instrucciones', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function fondos(): HasMany
    {
        return $this->hasMany(Fondo::class);
    }

    public function pagosFactura(): HasMany
    {
        return $this->hasMany(PagoFactura::class);
    }
}
