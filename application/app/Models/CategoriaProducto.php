<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaProducto extends Model
{
    protected $table = 'categorias_producto';

    protected $fillable = ['parent_id', 'nombre', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CategoriaProducto::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(CategoriaProducto::class, 'parent_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'categoria_id');
    }
}
