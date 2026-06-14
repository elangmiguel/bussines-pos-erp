<?php
/**
 * Archivo que contiene el modelo Eloquent 'Caja' para el sistema de punto de venta.
 *
 * Define la estructura y relaciones de la entidad caja, usada para
 * gestionar los saldos y operaciones asociadas a cajeros y canales.
 *
 * PHP version 8.1
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa un proveedor registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Proveedor",
 *     type         = "object",
 *     title        = "Proveedor",
 *     description  = "Entidad que representa a un proveedor en el sistema. Está vinculado obligatoriamente a una persona y opcionalmente a una empresa.",
 *     required     = {"persona_id"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del proveedor"
 * ),
 * @OA\Property(
 *     property     = "persona_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID de la persona asociada al proveedor"
 * ),
 * @OA\Property(
 *     property     = "empresa_id",
 *     type         = "integer",
 *     format       = "int64",
 *     nullable     = true,
 *     description  = "ID de la empresa asociada al proveedor, opcional"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del proveedor"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del proveedor"
 * ),
 *
 * @OA\Property(
 *     property     = "persona",
 *     ref          = "#/components/schemas/Persona",
 *     description  = "Relación con la persona asociada"
 * ),
 * @OA\Property(
 *     property     = "empresa",
 *     ref          = "#/components/schemas/Empresa",
 *     description  = "Relación con la empresa asociada, opcional"
 * )
 * )
 */



class Proveedor extends Model
{
    protected $table = 'proveedor';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización de un proveedor en el sistema.
     *
     * @OA\Schema(
     *     schema       = "ProveedorInput",
     *     type         = "object",
     *     required     = {"persona_id"},
     *     description  = "Esquema de entrada para crear o actualizar un proveedor",
     *
     * @OA\Property(
     *     property     = "persona_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID de la persona asociada al proveedor"
     * ),
     * @OA\Property(
     *     property     = "empresa_id",
     *     type         = "integer",
     *     format       = "int64",
     *     nullable     = true,
     *     description  = "ID de la empresa asociada al proveedor, opcional"
     * )
     * )
     */
    protected $fillable = [
        'persona_id',
        'empresa_id',
    ];

    /**
     * Retorna las reglas de validación para la entidad Proveedor.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'persona_id' => 'required|integer|exists:persona,id',
            'empresa_id' => 'nullable|integer|exists:empresa,id',
        ];
    }

    /**
     * Relación inversa: un proveedor pertenece a una persona.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    /**
     * Relación inversa: un proveedor pertenece a una empresa.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id', 'id');
    }

    /**
     * Relación directa: un proveedor tiene muchos productos
     * (relación muchos a muchos).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'proveedor_producto', 'proveedor_id', 'producto_id');
    }

    /**
     * Relación directa: un proveedor tiene muchas transacciones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'proveedor_id', 'id');
    }
}
