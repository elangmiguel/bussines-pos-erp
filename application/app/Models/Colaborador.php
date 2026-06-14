<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Colaborador extends Model
{
    protected $table = 'colaboradores';

    protected $fillable = [
        'user_id',
        'salario',
        'turno',
        'fecha_contratacion',
        'fecha_retiro',
    ];

    protected function casts(): array
    {
        return [
            'salario' => 'decimal:2',
            'fecha_contratacion' => 'date',
            'fecha_retiro' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cajero(): HasOne
    {
        return $this->hasOne(Cajero::class);
    }
}
