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
 * Modelo que representa una transaccion registrada en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Transaccion",
 *     type         = "object",
 *     title        = "Transacción",
 *     description  = "Representa una operación de venta o compra en el sistema.",
 *     required     = {"tipo"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único de la transacción"
 * ),
 * @OA\Property(
 *     property     = "tipo",
 *     type         = "string",
 *     enum         = {"venta", "compra"},
 *     description  = "Tipo de transacción realizada"
 * ),
 * @OA\Property(
 *     property     = "cajero_id",
 *     type         = "integer",
 *     format       = "int64",
 *     nullable     = true,
 *     description  = "ID del cajero que registró la transacción (opcional)"
 * ),
 * @OA\Property(
 *     property     = "cliente_id",
 *     type         = "integer",
 *     format       = "int64",
 *     nullable     = true,
 *     description  = "ID del cliente involucrado en la transacción (opcional)"
 * ),
 * @OA\Property(
 *     property     = "proveedor_id",
 *     type         = "integer",
 *     format       = "int64",
 *     nullable     = true,
 *     description  = "ID del proveedor asociado a la transacción (opcional)"
 * ),
 * @OA\Property(
 *     property     = "canal_id",
 *     type         = "integer",
 *     format       = "int64",
 *     nullable     = true,
 *     description  = "ID del canal a través del cual se realizó la transacción"
 * ),
 * @OA\Property(
 *     property     = "estado",
 *     type         = "string",
 *     description  = "Estado actual de la transacción (Ej. pendiente, completada)"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha y hora de creación de la transacción"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha y hora de la última actualización de la transacción"
 * ),
 * @OA\Property(
 *     property     = "cajero",
 *     ref          = "#/components/schemas/Cajero",
 *     description  = "Objeto relacionado con el cajero"
 * ),
 * @OA\Property(
 *     property     = "cliente",
 *     ref          = "#/components/schemas/Cliente",
 *     description  = "Objeto relacionado con el cliente"
 * ),
 * @OA\Property(
 *     property     = "proveedor",
 *     ref          = "#/components/schemas/Proveedor",
 *     description  = "Objeto relacionado con el proveedor"
 * )
 * )
 */
class Transaccion extends Model
{
    protected $table = 'transaccion';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización
     * de una transacción en el sistema.
     *
     * @OA\Schema(
     *     schema       = "TransaccionInput",
     *     type         = "object",
     *     required     = {"tipo", "estado"},
     *     description  = "Esquema de entrada para crear o actualizar una transacción",
     *
     * @OA\Property(
     *     property     = "tipo",
     *     type         = "string",
     *     enum         = {"venta", "compra"},
     *     description  = "Tipo de transacción realizada"
     * ),
     * @OA\Property(
     *     property     = "cajero_id",
     *     type         = "integer",
     *     format       = "int64",
     *     nullable     = true,
     *     description  = "ID del cajero que registró la transacción (opcional)"
     * ),
     * @OA\Property(
     *     property     = "cliente_id",
     *     type         = "integer",
     *     format       = "int64",
     *     nullable     = true,
     *     description  = "ID del cliente involucrado en la transacción (opcional)"
     * ),
     * @OA\Property(
     *     property     = "proveedor_id",
     *     type         = "integer",
     *     format       = "int64",
     *     nullable     = true,
     *     description  = "ID del proveedor asociado a la transacción (opcional)"
     * ),
     * @OA\Property(
     *     property     = "canal_id",
     *     type         = "integer",
     *     format       = "int64",
     *     nullable     = true,
     *     description  = "ID del canal a través del cual se realizó la transacción"
     * ),
     * @OA\Property(
     *     property     = "estado",
     *     type         = "string",
     *     maxLength    = 50,
     *     description  = "Estado actual de la transacción (Ej. pendiente)"
     *     )
     * )
     */
    protected $fillable = [
        'tipo',
        'cajero_id',
        'cliente_id',
        'proveedor_id',
        'canal_id',
        'estado',
    ];

    /**
     * Retorna las reglas de validación para la entidad Transaccion.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'tipo'          => 'required|string|in:venta,compra',
            'cajero_id'     => 'nullable|integer|exists:cajero,id',
            'cliente_id'    => 'nullable|integer|exists:cliente,id',
            'proveedor_id'  => 'nullable|integer|exists:proveedor,id',
            'canal_id'      => 'nullable|integer|exists:canal,id',
            'estado'        => 'required|string|max:50',
        ];
    }


    /**
     * Relación inversa: una transacción pertenece a un canal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canal()
    {
        return $this->belongsTo(Canal::class, 'canal_id', 'id');
    }

    /**
     * Relación inversa: una transacción pertenece a un cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id', 'id');
    }

    /**
     * Relación inversa: una transacción pertenece a un cajero.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cajero()
    {
        return $this->belongsTo(Cajero::class, 'cajero_id', 'id');
    }

    /**
     * Relación inversa: una transacción pertenece a un proveedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id', 'id');
    }

    /**
     * Relación directa: una transacción tiene muchos detalles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function detalles()
    {
        return $this->hasMany(Detalle::class, 'transaccion_id', 'id');
    }

}
