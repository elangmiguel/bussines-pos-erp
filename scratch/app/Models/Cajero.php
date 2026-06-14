<?php
/**
 * Archivo que contiene el modelo Eloquent 'Cajero'
 * para el sistema de punto de venta.
 *
 * Define la estructura y relaciones de la entidad caja, usada para
 * operaciones asociadas a cajeros y canales.
 *
 * PHP version 8.1
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira    <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo           <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo que representa un cajero registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Cajero",
 *     type         = "object",
 *     title        = "Cajero",
 *     description  = "Entidad que representa a un operador responsable del manejo de una caja dentro del sistema de punto de venta",
 *     required     = {},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del cajero"
 * ),
 * @OA\Property(
 *     property     = "colaborador_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del colaborador asociado"
 * ),
 * @OA\Property(
 *     property     = "codigo",
 *     type         = "string",
 *     maxLength    = 50,
 *     description  = "Codigo del cajero"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del registro"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización"
 * ),
 *
 * @OA\Property(
 *     property     = "colaborador",
 *     ref          = "#/components/schemas/Colaborador",
 *     description  = "Objeto relacionado del colaborador"
 * )
 * )
 */
class Cajero extends Model
{
    protected $table = 'cajero';

    /**
     * Esquema utilizado para la creación o actualización de cajeros en el sistema.
     *
     * @OA\Schema(
     *     schema       = "CajeroInput",
     *     type         = "object",
     *     required     = {},
     *     description  = "Esquema de entrada para crear o actualizar un cajero",
     *
     * @OA\Property(
     *     property     = "colaborador_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del colaborador asociado"
     * ),
     * @OA\Property(
     *     property     = "codigo",
     *     type         = "string",
     *     maxLength    = 50,
     *     description  = "Código identificador del cajero"
     * )
     * )
     */
    protected $fillable = [
        'colaborador_id',
        'codigo',
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Cajero.
     *
     * @return array<string, string>
     * Arreglo asociativo con reglas de validación para cada atributo
     */
    public static function rules()
    {
        return [
            'colaborador_id' => 'required|exists:colaborador,id',
            'codigo'         => 'nullable|string|max:50',
        ];
    }

    /**
     * Relación inversa: un cajero pertenece a un colaborador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function colaborador()
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id', 'id');
    }

    /**
     * Relación directa: un cajero tiene muchas transacciones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'cajero_id', 'id');
    }

    /**
     * Relación directa: un cajero tiene muchas cajas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cajas()
    {
        return $this->hasMany(Caja::class, 'cajero_id', 'id');
    }
}
