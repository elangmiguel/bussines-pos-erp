<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarifaIva extends Model
{
    protected $table = 'tarifas_iva';

    protected $fillable = ['nombre', 'tipo', 'porcentaje', 'activo'];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function detallesFactura(): HasMany
    {
        return $this->hasMany(DetalleFactura::class);
    }
}
