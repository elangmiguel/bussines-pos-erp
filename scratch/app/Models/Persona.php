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
 * Modelo que representa una persona registrada en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Persona",
 *     type         = "object",
 *     title        = "Persona",
 *     description  = "Entidad que almacena la información personal de un individuo, incluyendo nombre, documento de identidad, fecha de nacimiento y estado.",
 *     required     = {"nombre", "identificacion_tipo", "identificacion_numero"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único de la persona"
 * ),
 * @OA\Property(
 *     property     = "nombre",
 *     type         = "string",
 *     description  = "Nombre de la persona"
 * ),
 * @OA\Property(
 *     property     = "apellido",
 *     type         = "string",
 *     description  = "Apellido de la persona"
 * ),
 * @OA\Property(
 *     property     = "identificacion_tipo",
 *     type         = "string",
 *     description  = "Tipo de documento de identidad (Ej. DNI, pasaporte)"
 * ),
 * @OA\Property(
 *     property     = "identificacion_numero",
 *     type         = "string",
 *     description  = "Número o código único de identificación de la persona"
 * ),
 * @OA\Property(
 *     property     = "fecha_nacimiento",
 *     type         = "string",
 *     format       = "date",
 *     description  = "Fecha de nacimiento de la persona"
 * ),
 * @OA\Property(
 *     property     = "contacto_id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID del contacto asociado a la persona"
 * ),
 * @OA\Property(
 *     property     = "activo",
 *     type         = "boolean",
 *     default      = true,
 *     description  = "Estado de la persona (activo o inactivo)"
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
 *     description  = "Fecha de última actualización del registro"
 * )
 * )
 */

class Persona extends Model
{
    protected $table = 'persona';

    /**
     * Esquema utilizado para la creación o actualización de una persona en el sistema.
     *
     * @OA\Schema(
     *     schema       = "PersonaInput",
     *     type         = "object",
     *     required     = {
     *                      "nombre",
     *                      "apellido",
     *                      "identificacion_tipo",
     *                      "identificacion_numero",
     *                      "activo"
     *                     },
     *     description  = "Esquema de entrada para crear o actualizar una persona",
     *
     * @OA\Property(
     *     property     = "nombre",
     *     type         = "string",
     *     description  = "Nombre de la persona"
     * ),
     * @OA\Property(
     *     property     = "apellido",
     *     type         = "string",
     *     description  = "Apellido de la persona"
     * ),
     * @OA\Property(
     *     property     = "identificacion_tipo",
     *     type         = "string",
     *     description  = "Tipo de documento de identidad (Ej. DNI, pasaporte)"
     * ),
     * @OA\Property(
     *     property     = "identificacion_numero",
     *     type         = "string",
     *     description  = "Número o código único de identificación de la persona"
     * ),
     * @OA\Property(
     *     property     = "fecha_nacimiento",
     *     type         = "string",
     *     format       = "date",
     *     description  = "Fecha de nacimiento de la persona"
     * ),
     * @OA\Property(
     *     property     = "contacto_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID del contacto asociado a la persona"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     default      = true,
     *     description  = "Estado de la persona (activo o inactivo)"
     * )
     * )
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'identificacion_tipo',
        'identificacion_numero',
        'fecha_nacimiento',
        'contacto_id',
        'activo'
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Persona.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'nombre'               => 'required|string|max:255',
            'apellido'             => 'required|string|max:255',
            'identificacion_tipo'  => 'required|string|max:50',
            'identificacion_numero'=> 'required|string|max:100|unique:persona,identificacion_numero',
            'fecha_nacimiento'     => 'nullable|date',
            'contacto_id'          => 'nullable|integer|exists:contacto,id',
            'activo'               => 'required|boolean',
        ];
    }

    /**
     * Relación inversa: una persona tiene un contacto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contacto()
    {
        return $this->belongsTo(Contacto::class, 'contacto_id', 'id');
    }

    /**
     * Relación directa: una persona puede ser cliente.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cliente()
    {
        return $this->hasOne(Cliente::class, 'persona_id', 'id');
    }

    /**
     * Relación directa: una persona puede ser usuario.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'persona_id', 'id');
    }

    /**
     * Relación directa: una persona puede ser proveedor.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function proveedor()
    {
        return $this->hasOne(Proveedor::class, 'persona_id', 'id');
    }

}
