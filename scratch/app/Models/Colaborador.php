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
 * Modelo que representa un colaborador registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Colaborador",
 *     type         = "object",
 *     title        = "Colaborador",
 *     description  = "Persona que realiza funciones dentro de la empresa.",
 *     required     = {"usuario_id"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del colaborador"
 * ),
 * @OA\Property(
 *     property     = "usuario_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del usuario vinculado"
 * ),
 * @OA\Property(
 *     property     = "salario",
 *     type         = "number",
 *     format       = "double",
 *     description  = "Salario asignado al colaborador"
 * ),
 * @OA\Property(
 *     property     = "turno",
 *     type         = "string",
 *     maxLength    = 50,
 *     description  = "Turno laboral del colaborador"
 * ),
 * @OA\Property(
 *     property     = "activo",
 *     type         = "boolean",
 *
 *     description  = "Indica si el colaborador está activo"
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
 *     property     = "usuario",
 *     ref          = "#/components/schemas/Usuario",
 *     description  = "Objeto relacionado con el usuario asociado"
 * )
 * )
 */
class Colaborador extends Model
{
    protected $table = 'colaborador';

    /**
     * Esquema utilizado para la creación o actualización de colaboradores en el sistema.
     *
     * @OA\Schema(
     *     schema       = "ColaboradorInput",
     *     type         = "object",
     *     required     = {"usuario_id", "salario", "turno"},
     *     description  = "Esquema de entrada para crear o actualizar un colaborador",
     *
     * @OA\Property(
     *     property     = "usuario_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del usuario vinculado"
     * ),
     * @OA\Property(
     *     property     = "salario",
     *     type         = "number",
     *     format       = "double",
     *     description  = "Salario asignado al colaborador"
     * ),
     * @OA\Property(
     *     property     = "turno",
     *     type         = "string",
     *     maxLength    = 50,
     *     description  = "Turno laboral del colaborador"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     description  = "Indica si el colaborador está activo"
     * )
     * )
     */
    protected $fillable = [
        'usuario_id',
        'salario',
        'turno',
        'fecha_contratacion',
        'activo'
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Colaborador.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'usuario_id' => 'required|exists:usuario,id',
            'salario'    => 'required|numeric|min:0',
            'turno'      => 'required|string|max:50',
            'activo'     => 'nullable|boolean',
        ];
    }

    /**
     * Relación inversa: un colaborador pertenece a un usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'usuario_id', 'id');
    }

    /**
     * Relación directa: un colaborador tiene un cajero.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cajero()
    {
        return $this->hasOne(Cajero::class, 'colaborador_id', 'id');
    }
}
