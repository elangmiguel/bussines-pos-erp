<?php

/**
 * Controlador HTTP para la entidad Fondo.
 *
 * Gestiona las operaciones CRUD para la tabla `fondo`,
 * incluyendo la carga de la relación `canal`.
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

use App\Models\Fondo;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de fondos.
 *
 * Administra la lógica de negocio para la entidad Fondo,
 * cargando la relación con `canal`.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Fondo",
 *     description = "Operaciones relacionadas con fondos"
 * )
 */
class FondoController extends Controller
{
    /**
     * Listar todos los fondos con paginación y relaciones.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/fondos",
     *     summary     = "Listar fondos con canal",
     *     tags        = {"Fondo"},
     *
     *   * @OA\Parameter(
     *         name        = "page",
     *         in          = "query",
     *         description = "Número de página para paginación",
     *         required    = false,
     *       * @OA\Schema(type="integer", example=1)
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Lista paginada de fondos",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Fondo")
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
            $fondos = Fondo::with(['canal'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($fondos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo fondo.
     *
     * @param Request $request Datos del fondo a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/fondos",
     *     summary = "Crear un fondo",
     *     tags    = {"Fondo"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/FondoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Fondo creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Fondo")
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
        $validated = $request->validate(Fondo::rules());
        $fondo = Fondo::create($validated);
        return response()->json($fondo, 201);
    }

    /**
     * Obtener un fondo por su ID con relación canal.
     *
     * @param int $id ID del fondo.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/fondos/{id}",
     *     summary = "Obtener fondo por ID con relación canal",
     *     tags    = {"Fondo"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del fondo",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Fondo encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Fondo")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Fondo no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $fondo = Fondo::with(['canal'])->findOrFail($id);
        return response()->json($fondo);
    }

    /**
     * Actualizar un fondo existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del fondo.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/fondos/{id}",
     *     summary = "Actualizar un fondo",
     *     tags    = {"Fondo"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del fondo",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el fondo",
     *       * @OA\JsonContent(ref="#/components/schemas/FondoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Fondo actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Fondo")
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
        $validated = $request->validate(Fondo::rules());
        $fondo = Fondo::findOrFail($id);
        $fondo->update($validated);
        return response()->json($fondo);
    }

    /**
     * Eliminar un fondo por su ID.
     *
     * @param int $id ID del fondo.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/fondos/{id}",
     *     summary = "Eliminar un fondo",
     *     tags    = {"Fondo"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del fondo",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Fondo eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Fondo no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $fondo = Fondo::findOrFail($id);
        $fondo->delete();
        return response()->json(['message' => 'Fondo eliminado correctamente']);
    }
}
