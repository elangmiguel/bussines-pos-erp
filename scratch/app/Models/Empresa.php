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
 * Modelo que representa una empresa registrada en el sistema de punto de venta.
 *
 * @category Models
 * @package  App\Models
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Schema(
 *     schema       = "Empresa",
 *     type         = "object",
 *     title        = "Empresa",
 *     description  = "Representa una empresa en el sistema.",
 *     required     = {"razon_social", "nit"},
 *
 * @OA\Property(
 *     property     = "id",
 *     type         = "integer",
 *     format       = "int64",
 *     description  = "ID único de la empresa"
 * ),
 * @OA\Property(
 *     property     = "razon_social",
 *     type         = "string",
 *     description  = "Razón social de la empresa"
 * ),
 * @OA\Property(
 *     property     = "nit",
 *     type         = "string",
 *     description  = "NIT (Número de Identificación Tributaria) de la empresa"
 * ),
 * @OA\Property(
 *     property     = "contacto_id",
 *     type         = "integer",
 *     format       = "int64",
 *     nullable     = true,
 *     description  = "ID del contacto principal de la empresa"
 * ),
 * @OA\Property(
 *     property     = "creado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de creación de la empresa"
 * ),
 * @OA\Property(
 *     property     = "actualizado_en",
 *     type         = "string",
 *     format       = "date-time",
 *     description  = "Fecha de última actualización de la empresa"
 * ),
 * @OA\Property(
 *     property="contacto",
 *     ref="#/components/schemas/Contacto",
 *     description="Objeto relacionado con el contacto principal de la empresa"
 * )
 * )
 */
class Empresa extends Model
{
    protected $table = 'empresa';

    /**
     * Esquema utilizado para la creación o actualización de una empresa en el sistema.
     *
     * @OA\Schema(
     *     schema       = "EmpresaInput",
     *     type         = "object",
     *     required     = {"razon_social", "nit"},
     *     description  = "Esquema de entrada para crear o actualizar una empresa",
     *
     * @OA\Property(
     *     property     = "razon_social",
     *     type         = "string",
     *     description  = "Razón social de la empresa"
     * ),
     * @OA\Property(
     *     property     = "nit",
     *     type         = "string",
     *     description  = "NIT (Número de Identificación Tributaria) de la empresa"
     * ),
     * @OA\Property(
     *     property     = "contacto_id",
     *     type         = "integer",
     *     format       = "int64",
     *     nullable     = true,
     *     description  = "ID del contacto principal de la empresa"
     * )
     * )
     */
    protected $fillable = [
        'razon_social',
        'nit',
        'contacto_id',
    ];

    public $timestamps = false;

    const CREATED_AT = 'creado_en';
    const UPDATED_AT = 'actualizado_en';

    /**
     * Retorna las reglas de validación para la entidad Empresa.
     *
     * @return array<string, string>
     * Arreglo con reglas de validación para cada atributo
     */
    public static function rules(): array
    {
        return [
            'razon_social' => 'required|string|max:255',
            'nit'          => 'required|string|max:50|unique:empresa,nit',
            'contacto_id'  => 'nullable|exists:contacto,id',
        ];
    }

    /**
     * Relación inversa: una empresa tiene un contacto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contacto()
    {
        return $this->belongsTo(Contacto::class, 'contacto_id', 'id');
    }

    /**
     * Relación directa: una empresa tiene muchos proveedores.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function proveedores()
    {
        return $this->hasMany(Proveedor::class, 'empresa_id', 'id');
    }

}
