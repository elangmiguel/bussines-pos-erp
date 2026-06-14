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
 * Modelo que representa una caja registrada en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Caja",
 *     type         = "object",
 *     title        = "Caja",
 *     description  = "Entidad que representa una caja física o virtual donde se registran movimientos financieros de apertura, cierre y operaciones de venta, asociada a un cajero y un canal específico.",
 *     required     = {"cajero_id", "canal_id", "saldo_inicial"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único de la caja"
 * ),
 * @OA\Property(
 *     property     = "cajero_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del cajero responsable"
 * ),
 * @OA\Property(
 *     property     = "canal_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del canal asociado"
 * ),
 * @OA\Property(
 *     property     = "saldo_inicial",
 *     type         = "number",
 *     format       = "double",
 *     description  = "Saldo al momento de apertura"
 * ),
 * @OA\Property(
 *     property     = "saldo_final",
 *     type         = "number",
 *     format       = "double",
 *     description  = "Saldo al momento de cierre"
 * ),
 * @OA\Property(
 *     property     = "apertura",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha y hora de apertura"
 * ),
 * @OA\Property(
 *     property     = "cierre",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha y hora de cierre"
 * ),
 *
 * @OA\Property(
 *     property     = "cajero",
 *     ref          = "#/components/schemas/Cajero",
 *     description  = "Objeto relacionado: cajero"
 * ),
 * @OA\Property(
 *     property     = "canal",
 *     ref          = "#/components/schemas/Canal",
 *     description  = "Objeto relacionado: canal"
 * )
 * )
 */
class Caja extends Model
{
    protected $table = 'caja';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización de cajas en el sistema.
     *
     * @OA\Schema(
     *     schema       = "CajaInput",
     *     type         = "object",
     *     required     = {},
     *     description  = "Esquema de entrada para crear o actualizar una caja",
     *
     * @OA\Property(
     *     property     = "cajero_id",
     *     type         = "integer",
     *     description  = "ID del cajero asociado a la caja"
     * ),
     * @OA\Property(
     *     property     = "canal_id",
     *     type         = "integer",
     *     description  = "ID del canal asociado a la caja"
     * ),
     * @OA\Property(
     *     property     = "saldo_inicial",
     *     type         = "number",
     *     format       = "double",
     *     description  = "Saldo inicial disponible al momento de abrir la caja"
     * ),
     * @OA\Property(
     *     property     = "saldo_final",
     *     type         = "number",
     *     format       = "double",
     *     description  = "Saldo final con el que se cierra la caja"
     * ),
     * @OA\Property(
     *     property     = "apertura",
     *     type         = "string",
     *     format       = "date-time",
     *     description  = "Fecha y hora de apertura de la caja (formato ISO 8601)"
     * ),
     * @OA\Property(
     *     property     = "cierre",
     *     type         = "string",
     *     format       = "date-time",
     *     description  = "Fecha y hora de cierre de la caja (debe ser posterior o igual a apertura)"
     * )
     * )
     */
    protected $fillable = [
        'cajero_id',
        'canal_id',
        'saldo_inicial',
        'saldo_final',
        'apertura',
        'cierre',
    ];

    /**
     * Retorna las reglas de validación para la entidad Caja.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'cajero_id'     => 'nullable|exists:cajero,id',
            'canal_id'      => 'nullable|exists:canal,id',
            'saldo_inicial' => 'nullable|numeric|min:0',
            'saldo_final'   => 'nullable|numeric|min:0',
            'apertura'      => 'nullable|date',
            'cierre'        => 'nullable|date|after_or_equal:apertura',
        ];
    }


    /**
     * Relación inversa: una caja pertenece a un canal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canal()
    {
        return $this->belongsTo(Canal::class, 'canal_id', 'id');
    }

    /**
     * Relación inversa: una caja pertenece a un cajero.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cajero()
    {
        return $this->belongsTo(Cajero::class, 'cajero_id', 'id');
    }
}
