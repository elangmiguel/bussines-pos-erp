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
 * Modelo que representa un usuario registrado en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema      = "Usuario",
 *     type        = "object",
 *     title       = "Usuario",
 *     description = "Representa una cuenta de acceso de usuario vinculada a una persona.",
 *     required    = {"usuario", "contrasena"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único del usuario"
 * ),
 * @OA\Property(
 *     property     = "usuario",
 *     type         = "string",
 *     description  = "Nombre de usuario único utilizado para iniciar sesión"
 * ),
 * @OA\Property(
 *     property     = "contrasena",
 *     type         = "string",
 *     description  = "Contraseña cifrada del usuario"
 * ),
 * @OA\Property(
 *     property     = "rol",
 *     type         = "string",
 *     description  = "Rol asignado al usuario dentro del sistema (Ej. administrador, cajero)"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha y hora de creación del usuario"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha y hora de la última actualización del usuario"
 * ),
 * @OA\Property(
 *     property     = "persona",
 *     ref          = "#/components/schemas/Persona",
 *     description  = "Objeto relacionado con la persona asociada al usuario"
 * ),
 * )
 */
class Usuario extends Model
{
    protected $table = 'usuario';

    /**
     * Esquema utilizado para la creación o actualización de un usuario en el sistema.
     *
     * @OA\Schema(
     *     schema       = "UsuarioInput",
     *     type         = "object",
     *     required     = {"usuario", "contrasena", "rol"},
     *     description  = "Esquema de entrada para crear o actualizar un usuario",
     *
     * @OA\Property(
     *     property     = "persona_id",
     *     type         = "integer",
     *     format       = "int64",
     *     description  = "ID de la persona asociada al usuario"
     * ),
     * @OA\Property(
     *     property     = "usuario",
     *     type         = "string",
     *     maxLength    = 100,
     *     description  = "Nombre de usuario único utilizado para iniciar sesión"
     * ),
     * @OA\Property(
     *     property     = "contrasena",
     *     type         = "string",
     *     minLength    = 8,
     *     description  = "Contraseña del usuario antes del cifrado"
     * ),
     * @OA\Property(
     *     property     = "rol",
     *     type         = "string",
     *     maxLength    = 50,
     *     description  = "Rol asignado al usuario dentro del sistema (Ej. administrador, cajero)"
     * ),
     * @OA\Property(
     *     property     = "activo",
     *     type         = "boolean",
     *     description  = "Indica si el usuario está activo"
     * )
     * )
     */
    protected $fillable = [
        'persona_id',
        'usuario',
        'rol',
        'activo',
        'contrasena',
    ];

    protected $hidden = [
        'contrasena',
    ];

    /**
     * Retorna las reglas de validación para la entidad Usuario.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'persona_id' => 'required|integer|max:100|unique:usuario,usuario',
            'usuario'    => 'required|string|max:100|unique:usuario,usuario',
            'contrasena' => 'required|string|min:8', // validación antes del cifrado
            'activo'     => 'required|boolean',
            'rol'        => 'required|string|max:50',
        ];
    }

    protected $casts = [
        'contrasena' => 'hashed',
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';




    /**
     * Relación inversa: un usuario pertenece a una persona.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    /**
     * Relación directa: un usuario tiene un colaborador.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function colaborador()
    {
        return $this->hasOne(Colaborador::class, 'usuario_id', 'id');
    }
}
