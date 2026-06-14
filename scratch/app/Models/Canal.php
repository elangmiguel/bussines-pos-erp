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
 * Modelo que representa un canal registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema="Canal",
 *     type="object",
 *     title="Canal",
 *     description="Método de pago utilizado en transacciones, cajas y fondos.",
 *     required={"nombre"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "Identificador único del canal"
 * ),
 * @OA\Property(
 *     property     = "nombre",
 *     type         = "string",
 *     maxLength    = 100,
 *     description  = "Nombre del canal (ej: Efectivo,Nequi,Banco)"
 * ),
 * @OA\Property(
 *     property     = "tipo",
 *     type         = "string",
 *     maxLength    = 50,
 *     description  = "Tipo de canal (ej: digital,físico)"
 * ),
 * @OA\Property(
 *     property     = "descripcion",
 *     type         = "string",
 *     description  = "Descripción del canal"
 * ),
 * @OA\Property(
 *     property     = "activo",
 *     type         = "boolean",
 *     description  = "Indica si el canal está activo"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del canal"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del canal"
 * )
 * )
 */
class Canal extends Model
{
    protected $table = 'canal';

    /**
     * Esquema utilizado para la creación o actualización de canales en el sistema.
     *
     * @OA\Schema(
     *     schema       = "CanalInput",
     *     type         = "object",
     *     required     = {"nombre"},
     *     description  = "Esquema de entrada para crear o actualizar un canal",
     *
     * @OA\Property(
     *     property     = "nombre",
     *     type         = "string",
     *     maxLength    = 100,
     *     description  = "Nombre del canal (ej: Efectivo, Nequi, Banco)"
     * ),
     * @OA\Property(
     *     property     = "tipo",
     *     type         = "string",
     *     maxLength    = 50,
     *     description  = "Tipo de canal (ej: digital, físico)"
     * ),
     * @OA\Property(
     *     property     = "descripcion",
     *     type         = "string",
     *     description  = "Descripción del canal"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     description  = "Indica si el canal está activo"
     * )
     * )
     */
    protected $fillable = [
        'tipo',
        'descripcion',
        'activo'
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Canal.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'nombre'      => 'required|string|max:100',
            'tipo'        => 'nullable|string|max:50',
            'descripcion' => 'nullable|string',
            'activo'      => 'nullable|boolean',
        ];
    }

    /**
     * Relación directa: un canal tiene muchas transacciones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'canal_id', 'id');
    }

    /**
     * Relación directa: un canal tiene muchas cajas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cajas()
    {
        return $this->hasMany(Caja::class, 'canal_id', 'id');
    }

    /**
     * Relación directa: un canal tiene muchos fondos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fondos()
    {
        return $this->hasMany(Fondo::class, 'canal_id', 'id');
    }
}
