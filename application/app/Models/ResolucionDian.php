<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResolucionDian extends Model
{
    protected $table = 'resoluciones_dian';

    protected $fillable = [
        'numero_resolucion',
        'fecha_resolucion',
        'fecha_inicio',
        'fecha_fin',
        'prefijo',
        'rango_desde',
        'rango_hasta',
        'numero_actual',
        'clave_tecnica',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_resolucion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function facturas(): HasMany
    {
        return $this->hasMany(Factura::class, 'resolucion_id');
    }

    public function estaVigente(): bool
    {
        $hoy = now()->toDateString();
        return $this->activo
            && $hoy >= $this->fecha_inicio->toDateString()
            && $hoy <= $this->fecha_fin->toDateString()
            && $this->numero_actual < $this->rango_hasta;
    }

    public function siguienteNumero(): int
    {
        return $this->numero_actual + 1;
    }
}
