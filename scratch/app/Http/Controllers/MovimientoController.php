<?php

/**
 * Controlador HTTP para la entidad Movimiento.
 *
 * Gestiona las operaciones CRUD para la tabla `movimiento`,
 * incluyendo la carga de las relaciones `caja`, `fondo` y `transaccion`.
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

use App\Models\Movimiento;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de movimientos.
 *
 * Administra la lógica de negocio para la entidad Movimiento,
 * cargando las relaciones con `caja`, `fondo` y `transaccion`.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Movimiento",
 *     description = "Operaciones relacionadas con movimientos"
 * )
 */
class MovimientoController extends Controller
{
    /**
     * Listar todos los movimientos con paginación y relaciones.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/movimientos",
     *     summary     = "Listar movimientos con caja, fondo y transaccion",
     *     tags        = {"Movimiento"},
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
     *         description = "Lista paginada de movimientos",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Movimiento")
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
            $movimientos = Movimiento::with(['caja', 'fondo', 'transaccion'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($movimientos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo movimiento.
     *
     * @param Request $request Datos del movimiento a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/movimientos",
     *     summary = "Crear un movimiento",
     *     tags    = {"Movimiento"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/MovimientoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Movimiento creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Movimiento")
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
        $validated = $request->validate(Movimiento::rules());
        $movimiento = Movimiento::create($validated);
        return response()->json($movimiento, 201);
    }

    /**
     * Obtener un movimiento por su ID con relaciones caja, fondo y transaccion.
     *
     * @param int $id ID del movimiento.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/movimientos/{id}",
     *     summary = "Obtener movimiento por ID con relaciones",
     *     tags    = {"Movimiento"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del movimiento",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Movimiento encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Movimiento")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Movimiento no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $movimiento = Movimiento::with(['caja', 'fondo', 'transaccion'])
            ->findOrFail($id);
        return response()->json($movimiento);
    }

    /**
     * Actualizar un movimiento existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del movimiento.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/movimientos/{id}",
     *     summary = "Actualizar un movimiento",
     *     tags    = {"Movimiento"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del movimiento",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el movimiento",
     *       * @OA\JsonContent(ref="#/components/schemas/MovimientoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Movimiento actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Movimiento")
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
        $validated = $request->validate(Movimiento::rules());
        $movimiento = Movimiento::findOrFail($id);
        $movimiento->update($validated);
        return response()->json($movimiento);
    }

    /**
     * Eliminar un movimiento por su ID.
     *
     * @param int $id ID del movimiento.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/movimientos/{id}",
     *     summary = "Eliminar un movimiento",
     *     tags    = {"Movimiento"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del movimiento",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Movimiento eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Movimiento no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $movimiento = Movimiento::findOrFail($id);
        $movimiento->delete();
        return response()->json(['message' => 'Movimiento eliminado correctamente']);
    }
}
