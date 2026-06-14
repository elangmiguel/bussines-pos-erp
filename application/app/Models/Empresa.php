<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empresa extends Model
{
    use SoftDeletes;

    protected $table = 'empresas';

    protected $fillable = [
        'razon_social',
        'nit',
        'digito_verificacion',
        'regimen_tributario',
        'tipo_empresa',
        'representante_id',
        'email',
        'telefono',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function representante(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'representante_id');
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function configuracion(): HasMany
    {
        return $this->hasMany(Configuracion::class);
    }

    public function getNitCompletoAttribute(): string
    {
        return "{$this->nit}-{$this->digito_verificacion}";
    }
}
