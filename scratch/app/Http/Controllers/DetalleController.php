<?php

/**
 * Definición del controlador HTTP para la entidad Detalle.
 *
 * Gestiona las operaciones CRUD para la tabla `detalle`,
 * incluyendo la carga de las relaciones `transaccion` y `producto`.
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

use App\Models\Detalle;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Detalle.
 *
 * Administra la lógica de negocio para los detalles de transacciones,
 * cargando las relaciones con `transaccion` y `producto`.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Detalle",
 *     description = "Operaciones relacionadas con los detalles de transacción"
 * )
 */
class DetalleController extends Controller
{
    /**
     * Listar todos los detalles con paginación y relaciones.
     *
     * @param Request $request Solicitud HTTP con parámetro de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/detalles",
     *     summary     = "Listar detalles con sus transacciones y productos",
     *     tags        = {"Detalle"},
     *
     *   * @OA\Parameter(
     *         name        = "page",
     *         in          = "query",
     *         description = "Número de página",
     *         required    = false,
     *       * @OA\Schema(type="integer", example=1)
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Lista paginada de detalles",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Detalle")
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
            $detalles = Detalle::with(['transaccion', 'producto'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($detalles);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo detalle.
     *
     * @param Request $request Datos del detalle a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/detalles",
     *     summary = "Crear un detalle de transacción",
     *     tags    = {"Detalle"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/DetalleInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Detalle creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Detalle")
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
        $validated = $request->validate(Detalle::rules());
        $detalle = Detalle::create($validated);
        return response()->json($detalle, 201);
    }

    /**
     * Obtener un detalle por su ID con relaciones.
     *
     * @param int $id ID del detalle.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/detalles/{id}",
     *     summary = "Obtener detalle por ID con relaciones",
     *     tags    = {"Detalle"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del detalle",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Detalle encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Detalle")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Detalle no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $detalle = Detalle::with(['transaccion', 'producto'])->findOrFail($id);
        return response()->json($detalle);
    }

    /**
     * Actualizar un detalle existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del detalle.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/detalles/{id}",
     *     summary = "Actualizar un detalle",
     *     tags    = {"Detalle"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del detalle",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el detalle",
     *       * @OA\JsonContent(ref="#/components/schemas/DetalleInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Detalle actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Detalle")
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
        $validated = $request->validate(Detalle::rules());
        $detalle = Detalle::findOrFail($id);
        $detalle->update($validated);
        return response()->json($detalle);
    }

    /**
     * Eliminar un detalle por su ID.
     *
     * @param int $id ID del detalle.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/detalles/{id}",
     *     summary = "Eliminar un detalle",
     *     tags    = {"Detalle"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del detalle",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Detalle eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Detalle no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $detalle = Detalle::findOrFail($id);
        $detalle->delete();
        return response()->json(['message' => 'Detalle eliminado correctamente']);
    }
}
