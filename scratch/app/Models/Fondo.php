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
 * Modelo que representa un fondo registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       ="Fondo",
 *     type         ="object",
 *     title        ="Fondo",
 *     description  ="Representa un fondo utilizado en el sistema, asociado a un canal de pago.",
 *     required     ={"nombre", "tipo", "canal_id"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del fondo"
 * ),
 * @OA\Property(
 *     property     = "nombre",
 *     type         = "string",
 *     description  = "Nombre del fondo"
 * ),
 * @OA\Property(
 *     property     = "tipo",
 *     type         = "string",
 *     enum         = {"caja","digital","banco","reserva","otros"},
 *     description  = "Tipo de fondo (caja, digital, banco, reserva, otros)"
 * ),
 * @OA\Property(
 *     property     = "canal_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del canal asociado al fondo"
 * ),
 * @OA\Property(
 *     property     = "descripcion",
 *     type         = "string",
 *     description  = "Descripción del fondo"
 * ),
 * @OA\Property(
 *     property     = "activo",
 *     type         = "boolean",
 *     description  = "Indica si el fondo está activo"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del fondo"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del fondo"
 * ),
 *
 * @OA\Property(
 *     property="canal",
 *     ref="#/components/schemas/Canal",
 *     description="Objeto relacionado con el canal asociado al fondo"
 * )
 * )
 */
class Fondo extends Model
{
    protected $table = 'fondo';

    public $timestamps = false;

    /**
     * Esquema utilizado para la creación o actualización de un fondo en el sistema.
     *
     * @OA\Schema(
     *     schema       = "FondoInput",
     *     type         = "object",
     *     required     = {"nombre", "tipo", "canal_id", "activo"},
     *     description  = "Esquema de entrada para crear o actualizar un fondo",
     *
     * @OA\Property(
     *     property     = "nombre",
     *     type         = "string",
     *     description  = "Nombre del fondo"
     * ),
     * @OA\Property(
     *     property     = "tipo",
     *     type         = "string",
     *     enum         = {"caja","digital","banco","reserva","otros"},
     *     description  = "Tipo de fondo (caja, digital, banco, reserva, otros)"
     * ),
     * @OA\Property(
     *     property     = "canal_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del canal asociado al fondo"
     * ),
     * @OA\Property(
     *     property     = "descripcion",
     *     type         = "string",
     *     description  = "Descripción del fondo"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     description  = "Indica si el fondo está activo"
     * )
     * )
     */
    protected $fillable = [
        'nombre',
        'tipo',
        'canal_id',
        'descripcion',
        'activo',
    ];

    /**
     * Retorna las reglas de validación para la entidad Fondo.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'nombre'      => 'required|string|max:255',
            'tipo'        => 'required|string|in:caja,digital,banco,reserva,otros',
            'canal_id'    => 'required|exists:canal,id',
            'descripcion' => 'nullable|string',
            'activo'      => 'required|boolean',
        ];
    }

    /**
     * Relación inversa: un fondo pertenece a un canal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function canal()
    {
        return $this->belongsTo(Canal::class, 'canal_id', 'id');
    }

    /**
     * Relación directa: un fondo tiene muchos movimientos.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'fondo_id', 'id');
    }
}
