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
 * Modelo que representa un contacto registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Contacto",
 *     type         = "object",
 *     title        = "Contacto",
 *     description  = "Información de contacto de una persona o entidad.",
 *     required     =  {},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del contacto"
 * ),
 * @OA\Property(
 *     property     = "telefono",
 *     type         = "string",
 *     description  = "Número de teléfono del contacto"
 * ),
 * @OA\Property(
 *     property     = "direccion",
 *     type         = "string",
 *     description  = "Dirección física del contacto"
 * ),
 * @OA\Property(
 *     property     = "email",
 *     type         = "string",
 *     format       = "email",
 *     description  = "Correo electrónico del contacto"
 * ),
 * @OA\Property(
 *     property     = "activo",
 *     type         = "boolean",
 *     description  = "Indica si el contacto está activo"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación del contacto"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización del contacto"
 * )
 * )
 */
class Contacto extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacto';

    /**
     * Esquema utilizado para la creación o actualización de contactos en el sistema.
     *
     * @OA\Schema(
     *     schema       = "ContactoInput",
     *     type         = "object",
     *     required     = {},
     *     description  = "Esquema de entrada para crear o actualizar un contacto",
     *
     * @OA\Property(
     *     property     = "telefono",
     *     type         = "string",
     *     description  = "Número de teléfono del contacto"
     * ),
     * @OA\Property(
     *     property     = "direccion",
     *     type         = "string",
     *     description  = "Dirección física del contacto"
     * ),
     * @OA\Property(
     *     property     = "email",
     *     type         = "string",
     *     format       = "email",
     *     description  = "Correo electrónico del contacto"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     description  = "Indica si el contacto está activo"
     * )
     * )
     */
    protected $fillable = [
        'telefono',
        'email',
        'direccion',
        'activo'
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Contacto.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'telefono'  => 'nullable|string|max:20',
            'direccion' => 'nullable|string|max:255',
            'email'     => 'nullable|email|max:100',
            'activo'    => 'nullable|boolean',
        ];
    }

    /**
     * Relación inversa: un contacto tiene muchas personas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function personas()
    {
        return $this->hasMany(Persona::class, 'contacto_id', 'id');
    }

    /**
     * Relación inversa: un contacto tiene muchas empresas.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function empresas()
    {
        return $this->hasMany(Empresa::class, 'contacto_id', 'id');
    }
}
