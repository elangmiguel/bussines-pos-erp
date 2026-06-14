<?php

/**
 * Definición del controlador HTTP para la entidad Contacto.
 *
 * Este archivo contiene la implementación del controlador responsable de gestionar
 * las operaciones relacionadas con la tabla `contacto`, como listar, crear,
 * actualizar, consultar y eliminar registros.
 *
 * PHP version 8.1
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 */

namespace App\Http\Controllers;

use App\Models\Contacto;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Contacto.
 *
 * Gestiona la lógica de negocio para los contactos, permitiendo
 * listar, crear, actualizar, mostrar y eliminar registros.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Contacto",
 *     description = "Operaciones relacionadas con los contactos del sistema"
 * )
 */
class ContactoController extends Controller
{
    /**
     * Listar todos los contactos con paginación.
     *
     * @param Request $request Solicitud con el número de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/contactos",
     *     summary     = "Listar todos los contactos con paginación",
     *     tags        = {"Contacto"},
     *
     *   * @OA\Parameter(
     *         name        = "page",
     *         in          = "query",
     *         description = "Número de página a obtener",
     *         required    = false,
     *       * @OA\Schema(type="integer", example=1)
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Lista paginada de contactos",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Contacto")
     *             ),
     *           * @OA\Property(property="current_page", type="integer"),
     *           * @OA\Property(property="last_page", type="integer"),
     *           * @OA\Property(property="per_page", type="integer"),
     *           * @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *
     *   * @OA\Response(
     *         response    = 500,
     *         description = "Error interno del servidor"
     *     )
     * )
     */
    public function index(Request $request)
    {
        try {
            $page = $request->query('page', 1);
            $contactos = Contacto::with([])->paginate(10, ['*'], 'page', $page);
            return response()->json($contactos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo contacto.
     *
     * @param Request $request Solicitud con los datos a registrar.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/contactos",
     *     summary = "Crear un contacto",
     *     tags    = {"Contacto"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/ContactoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Contacto creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Contacto")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 400,
     *         description = "Datos inválidos"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate(Contacto::rules());
        $contacto = Contacto::create($validated);
        return response()->json($contacto, 201);
    }

    /**
     * Obtener un contacto por su ID.
     *
     * @param int $id ID del contacto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/contactos/{id}",
     *     summary = "Obtener un contacto por su ID",
     *     tags    = {"Contacto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del contacto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Contacto encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Contacto")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Contacto no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $contacto = Contacto::with([])->findOrFail($id);
        return response()->json($contacto);
    }

    /**
     * Actualizar un contacto existente.
     *
     * @param Request $request Datos validados para actualización.
     * @param int     $id      ID del contacto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/contactos/{id}",
     *     summary = "Actualizar un contacto",
     *     tags    = {"Contacto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del contacto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el contacto",
     *       * @OA\JsonContent(ref="#/components/schemas/ContactoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Contacto actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Contacto")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 400,
     *         description = "Datos inválidos"
     *     )
     * )
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate(Contacto::rules());
        $contacto = Contacto::findOrFail($id);
        $contacto->update($validated);
        return response()->json($contacto, 200);
    }

    /**
     * Eliminar un contacto.
     *
     * @param int $id ID del contacto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/contactos/{id}",
     *     summary = "Eliminar un contacto",
     *     tags    = {"Contacto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del contacto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Contacto eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Contacto no encontrado"
     *     )
     * )
     */
    public function destroy($id)
    {
        $contacto = Contacto::findOrFail($id);
        $contacto->delete();
        return response()->json(['message' => 'Contacto eliminado correctamente']);
    }
}
