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
 * Modelo que representa un detalle registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Detalle",
 *     type         = "object",
 *     title        = "Detalle de transacción",
 *     description  = "Información detallada de los productos en una transacción.",
 *     required={"transaccion_id", "producto_id", "cantidad", "precio_unitario"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del detalle de la transacción"
 * ),
 * @OA\Property(
 *     property     = "transaccion_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID de la transacción a la que pertenece el detalle"
 * ),
 * @OA\Property(
 *     property     = "producto_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del producto involucrado en la transacción"
 * ),
 * @OA\Property(
 *     property     = "cantidad",
 *     type         = "integer",
 *     description  = "Cantidad del producto en la transacción"
 * ),
 * @OA\Property(
 *     property     = "precio_unitario",
 *     type         =  "number",
 *     format       =  "double",
 *     description  =  "Precio unitario del producto"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del detalle de transacción"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del detalle de transacción"
 * ),
 *
 * @OA\Property(
 *     property="transaccion",
 *     ref="#/components/schemas/Transaccion",
 *     description="Objeto relacionado con la transacción"
 * ),
 * @OA\Property(
 *     property="producto",
 *     ref="#/components/schemas/Producto",
 *     description="Objeto relacionado con el producto"
 * )
 * )
 */
class Detalle extends Model
{
    protected $table = 'detalle';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización de detalles de transacción en el sistema.
     *
     * @OA\Schema(
     *     schema       = "DetalleInput",
     *     type         = "object",
     *     required     = {"transaccion_id", "producto_id", "cantidad", "precio_unitario"},
     *     description  = "Esquema de entrada para crear o actualizar un detalle de transacción",
     *
     * @OA\Property(
     *     property     = "transaccion_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID de la transacción a la que pertenece el detalle"
     * ),
     * @OA\Property(
     *     property     = "producto_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del producto involucrado en la transacción"
     * ),
     * @OA\Property(
     *     property     = "cantidad",
     *     type         = "integer",
     *     description  = "Cantidad del producto en la transacción"
     * ),
     * @OA\Property(
     *     property     = "precio_unitario",
     *     type         = "number",
     *     format       = "double",
     *     description  = "Precio unitario del producto"
     * )
     * )
     */
    protected $fillable = [
        'transaccion_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
    ];

    /**
     * Retorna las reglas de validación para la entidad Detalle.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'transaccion_id'  => 'required|exists:transaccion,id',
            'producto_id'     => 'required|exists:producto,id',
            'cantidad'        => 'required|integer|min:1',
            'precio_unitario' => 'required|numeric|min:0',
        ];
    }

    /**
     * Relación inversa: un detalle pertenece a una transacción.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaccion()
    {
        return $this->belongsTo(Transaccion::class, 'transaccion_id', 'id');
    }

    /**
     * Relación inversa: un detalle pertenece a un producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }
}
