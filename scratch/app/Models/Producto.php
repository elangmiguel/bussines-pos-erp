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
 * Modelo que representa un producto registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Producto",
 *     type         = "object",
 *     title        = "Producto",
 *     description  = "Entidad que representa un producto disponible en el inventario del sistema, incluyendo su nombre, precio, categoría, código de barras y stock disponible.",
 *     required     = {"nombre", "precio", "stock", "categoria", "codigo_barras"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del producto"
 * ),
 * @OA\Property(
 *     property     = "nombre",
 *     type         = "string",
 *     maxLength    = 255,
 *     description  = "Nombre del producto"
 * ),
 * @OA\Property(
 *     property     = "precio",
 *     type         = "number",
 *     format       = "double",
 *     description  = "Precio unitario del producto"
 * ),
 * @OA\Property(
 *     property     = "stock",
 *     type         = "integer",
 *     description  = "Cantidad disponible del producto en inventario"
 * ),
 * @OA\Property(
 *     property     = "categoria",
 *     type         = "string",
 *     description  = "Categoría del producto (Ej. Electrónica, Ropa, Alimentos)"
 * ),
 * @OA\Property(
 *     property     = "codigo_barras",
 *     type         = "string",
 *     description  = "Código de barras único del producto"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del producto"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del producto"
 * )
 * )
 */
class Producto extends Model
{
    protected $table = 'producto';

    /**
     * Esquema utilizado para la creación o actualización de un producto en el sistema.
     *
     * @OA\Schema(
     *     schema       = "ProductoInput",
     *     type         = "object",
     *     required     = {"nombre", "precio", "stock", "codigo_barras"},
     *     description  = "Esquema de entrada para crear o actualizar un producto",
     *
     * @OA\Property(
     *     property     = "nombre",
     *     type         = "string",
     *     maxLength    = 255,
     *     description  = "Nombre del producto"
     * ),
     * @OA\Property(
     *     property     = "precio",
     *     type         = "number",
     *     format       = "double",
     *     description  = "Precio unitario del producto"
     * ),
     * @OA\Property(
     *     property     = "stock",
     *     type         = "integer",
     *     description  = "Cantidad disponible del producto en inventario"
     * ),
     * @OA\Property(
     *     property     = "categoria",
     *     type         = "string",
     *     description  = "Categoría del producto (Ej. Electrónica, Ropa, Alimentos)"
     * ),
     * @OA\Property(
     *     property     = "codigo_barras",
     *     type         = "string",
     *     description  = "Código de barras único del producto"
     * )
     * )
     */
    protected $fillable = [
        'nombre',
        'precio',
        'stock',
        'categoria',
        'codigo_barras',
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Producto.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'nombre'        => 'required|string|max:255',
            'precio'        => 'required|numeric|min:0',
            'stock'         => 'required|integer|min:0',
            'categoria'     => 'nullable|string|max:100',
            'codigo_barras' => 'required|string|max:100|unique:producto,codigo_barras',
        ];
    }


    /**
     * Relación directa: un producto tiene muchos detalles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'producto_id', 'id');
    }

    /**
     * Relación inversa: un producto pertenece a muchos proveedores
     * (relación muchos a muchos).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function proveedores()
    {
        return $this->belongsToMany(Proveedor::class, 'proveedor_producto', 'producto_id', 'proveedor_id');
    }
}
