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
 * Modelo que representa un cliente registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Cliente",
 *     type         = "object",
 *     title        = "Cliente",
 *     description  = "Entidad que representa a un cliente del sistema.",
 *     required     = {"persona_id"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "Identificador único del cliente"
 * ),
 * @OA\Property(
 *     property     = "persona_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID de la persona asociada"
 * ),
 * @OA\Property(
 *     property     = "tipo",
 *     type         = "string",
 *     maxLength    = 50,
 *     description  = "Tipo de cliente (ej: frecuente,nuevo,empresarial)"
 * ),
 * @OA\Property(
 *     property     = "activo",
 *     type         = "boolean",
 *     description  = "Indica si el cliente está activo"
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
 *    property     = "persona",
 *    ref          = "#/components/schemas/Persona",
 *    description  = "Objeto relacionado de la persona asociada"
 * )
 * )
 */
class Cliente extends Model
{
    protected $table = 'cliente';

    /**
     * Esquema utilizado para la creación o actualización de clientes en el sistema.
     *
     * @OA\Schema(
     *     schema       = "ClienteInput",
     *     type         = "object",
     *     required     = {"persona_id", "tipo"},
     *     description  = "Esquema de entrada para crear o actualizar un cliente",
     *
     * @OA\Property(
     *     property     = "persona_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID de la persona asociada"
     * ),
     * @OA\Property(
     *     property     = "tipo",
     *     type         = "string",
     *     maxLength    = 50,
     *     description  = "Tipo de cliente (ej: frecuente, nuevo, empresarial)"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     description  = "Indica si el cliente está activo"
     * )
     * )
     */
    protected $fillable = [
        'persona_id',
        'tipo',
        'activo'
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Cliente.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'persona_id' => 'required|exists:persona,id',
            'tipo'       => 'required|string|max:50',
            'activo'     => 'nullable|boolean',
        ];
    }


    /**
     * Relación inversa: un cliente pertenece a una persona.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    /**
     * Relación directa: un cliente tiene muchas transacciones.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transacciones()
    {
        return $this->hasMany(Transaccion::class, 'cliente_id', 'id');
    }

}
