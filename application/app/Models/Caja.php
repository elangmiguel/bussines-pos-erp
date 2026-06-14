<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = ['nombre', 'ubicacion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(TurnoCaja::class);
    }

    public function turnoActivo()
    {
        return $this->hasOne(TurnoCaja::class)->where('estado', 'abierto');
    }
}
