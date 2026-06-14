<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use SoftDeletes;

    protected $table = 'personas';

    protected $fillable = [
        'tipo_identificacion',
        'numero_identificacion',
        'digito_verificacion',
        'nombres',
        'apellidos',
        'email',
        'telefono',
        'celular',
        'direccion',
        'ciudad',
        'departamento',
        'pais',
    ];

    public function usuario(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function empresasRepresentadas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'representante_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
