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
 * Modelo que representa una relacion proveedor producto registrada en el
 * sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "ProveedorProducto",
 *     type         = "object",
 *     title        = "ProveedorProducto",
 *     description  = "Relación muchos a muchos entre proveedores y productos ofrecidos. Esta tabla actúa como pivote.",
 *     required     = {"proveedor_id", "producto_id"},
 *
 * @OA\Property(
 *     property     = "proveedor_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del proveedor asociado"
 * ),
 * @OA\Property(
 *     property     = "producto_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del producto ofrecido por el proveedor"
 * ),
 * @OA\Property(
 *     property     = "proveedor",
 *     ref          = "#/components/schemas/Proveedor",
 *     description  = "Objeto relacionado del proveedor"
 * ),
 * @OA\Property(
 *     property     = "producto",
 *     ref          = "#/components/schemas/Producto",
 *     description  = "Objeto relacionado del producto"
 * )
 * )
 */


class ProveedorProducto extends Model
{
    protected $table = 'proveedor_producto';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización de una relación entre proveedor y producto en el sistema.
     *
     * @OA\Schema(
     *     schema       = "ProveedorProductoInput",
     *     type         = "object",
     *     required     = {"proveedor_id", "producto_id"},
     *     description  = "Esquema de entrada para crear o actualizar la relación proveedor-producto",
     *
     * @OA\Property(
     *     property     = "proveedor_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del proveedor asociado"
     * ),
     * @OA\Property(
     *     property     = "producto_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del producto ofrecido por el proveedor"
     * )
     * )
     */
    protected $fillable = [
        'proveedor_id',
        'producto_id',
    ];

    /**
     * Retorna las reglas de validación para la entidad ProveedorProducto.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'proveedor_id' => 'required|integer|exists:proveedor,id',
            'producto_id'  => 'required|integer|exists:producto,id',
        ];
    }


    /**
     * Relación inversa: pertenece a un proveedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'id');
    }

    /**
     * Relación inversa: pertenece a un producto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }
}
