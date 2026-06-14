<?php

/**
 * Definición del controlador HTTP para la entidad Canal.
 *
 * Este archivo contiene la implementación del controlador responsable de gestionar
 * las operaciones relacionadas con la tabla `canal`, como listar, crear,
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

use App\Models\Canal;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Canal.
 *
 * Gestiona la lógica de negocio para los canales, permitiendo
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
 *     name        = "Canal",
 *     description = "Operaciones relacionadas con los canales del sistema"
 * )
 */
class CanalController extends Controller
{
    /**
     * Listar todos los canales con paginación.
     *
     * @param Request $request Solicitud con el número de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/canales",
     *     summary     = "Listar todos los canales con paginación",
     *     tags        = {"Canal"},
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
     *         description = "Lista paginada de canales",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Canal")
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
            $canales = Canal::with([])->paginate(10, ['*'], 'page', $page);
            return response()->json($canales);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo canal.
     *
     * @param Request $request Solicitud con los datos a registrar.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/canales",
     *     summary = "Crear un canal",
     *     tags    = {"Canal"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/CanalInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Canal creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Canal")
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
        $validated = $request->validate(Canal::rules());
        $canal = Canal::create($validated);
        return response()->json($canal, 201);
    }

    /**
     * Obtener un canal por su ID.
     *
     * @param int $id ID del canal.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/canales/{id}",
     *     summary = "Obtener un canal por su ID",
     *     tags    = {"Canal"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del canal",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Canal encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Canal")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Canal no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $canal = Canal::with([])->findOrFail($id);
        return response()->json($canal);
    }

    /**
     * Actualizar un canal existente.
     *
     * @param Request $request Datos validados para actualización.
     * @param int     $id      ID del canal.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/canales/{id}",
     *     summary = "Actualizar un canal",
     *     tags    = {"Canal"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del canal",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el canal",
     *       * @OA\JsonContent(ref="#/components/schemas/CanalInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Canal actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Canal")
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
        $validated = $request->validate(Canal::rules());
        $canal = Canal::findOrFail($id);
        $canal->update($validated);
        return response()->json($canal, 200);
    }

    /**
     * Eliminar un canal.
     *
     * @param int $id ID del canal.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/canales/{id}",
     *     summary = "Eliminar un canal",
     *     tags    = {"Canal"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del canal",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Canal eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Canal no encontrado"
     *     )
     * )
     */
    public function destroy($id)
    {
        $canal = Canal::findOrFail($id);
        $canal->delete();
        return response()->json(['message' => 'Canal eliminado correctamente']);
    }
}
