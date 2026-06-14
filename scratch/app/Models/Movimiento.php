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
 * Modelo que representa un movimiento registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Movimiento",
 *     type         = "object",
 *     title        = "Movimiento de fondo",
 *     description  = "Entidad que representa una transacción de fondos dentro del sistema, que puede ser un ingreso o un egreso, asociada a un fondo determinado.",
 *     required     = {"fondo_id", "tipo", "monto"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del movimiento"
 * ),
 * @OA\Property(
 *     property     = "fondo_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del fondo asociado al movimiento"
 * ),
 * @OA\Property(
 *     property     = "tipo",
 *     type         = "string",
 *     enum         = {"ingreso", "egreso"},
 *     description  = "Tipo de movimiento (ingreso o egreso)"
 * ),
 * @OA\Property(
 *     property     = "monto",
 *     type         = "number",
 *     format       = "double",
 *     description  = "Monto del movimiento"
 * ),
 * @OA\Property(
 *     property     = "descripcion",
 *     type         = "string",
 *     description  = "Descripción del movimiento (opcional)"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del movimiento"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del movimiento"
 * ),
 * @OA\Property(
 *     property     = "fondo",
 *     ref          = "#/components/schemas/Fondo",
 *     description  = "Objeto relacionado con el fondo asociado al movimiento"
 * )
 * )
 */
class Movimiento extends Model
{
    protected $table = 'movimiento';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización de un movimiento en el sistema.
     *
     * @OA\Schema(
     *     schema       = "MovimientoInput",
     *     type         = "object",
     *     required     = {"fondo_id", "tipo", "monto"},
     *     description  = "Esquema de entrada para crear o actualizar un movimiento",
     *
     * @OA\Property(
     *     property     = "fondo_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del fondo asociado al movimiento"
     * ),
     * @OA\Property(
     *     property     = "tipo",
     *     type         = "string",
     *     enum         = {"ingreso", "egreso"},
     *     description  = "Tipo de movimiento (ingreso o egreso)"
     * ),
     * @OA\Property(
     *     property     = "monto",
     *     type         = "number",
     *     format       = "double",
     *     description  = "Monto del movimiento"
     * ),
     * @OA\Property(
     *     property     = "descripcion",
     *     type         = "string",
     *     description  = "Descripción del movimiento (opcional)"
     * )
     * )
     */
    protected $fillable = [
        'fondo_id',
        'tipo',
        'monto',
        'descripcion',
    ];

    /**
     * Retorna las reglas de validación para la entidad Movimiento.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'fondo_id'    => 'required|exists:fondo,id',
            'tipo'        => 'required|string|in:ingreso,egreso',
            'monto'       => 'required|numeric|min:0',
            'descripcion' => 'nullable|string',
        ];
    }

    /**
     * Relación inversa: un movimiento pertenece a un fondo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fondo()
    {
        return $this->belongsTo(Fondo::class, 'fondo_id', 'id');
    }
}
