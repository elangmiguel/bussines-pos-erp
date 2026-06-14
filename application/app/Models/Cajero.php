<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cajero extends Model
{
    protected $table = 'cajeros';

    protected $fillable = ['colaborador_id', 'codigo', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class);
    }

    public function turnos(): HasMany
    {
        return $this->hasMany(TurnoCaja::class);
    }

    public function turnoActivo(): HasMany
    {
        return $this->hasMany(TurnoCaja::class)->where('estado', 'abierto');
    }
}
