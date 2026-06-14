<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Configuracion extends Model
{
    protected $table = 'configuracion';

    protected $fillable = [
        'empresa_id',
        'logo',
        'moneda',
        'zona_horaria',
        'impresora_defecto',
        'dias_vencimiento_cred',
        'prefijo_nota_credito',
        'prefijo_nota_debito',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}
