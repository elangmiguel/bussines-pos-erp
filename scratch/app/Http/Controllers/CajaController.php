<?php

/**
 * Definición del controlador HTTP para la entidad Caja.
 *
 * Este archivo contiene la implementación del controlador responsable de gestionar
 * las operaciones relacionadas con la tabla `caja`, como listar, crear,
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

use App\Models\Caja;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Caja.
 *
 * Gestiona la lógica de negocio para las cajas, permitiendo
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
 *     name        = "Caja",
 *     description = "Operaciones relacionadas con las cajas del sistema"
 * )
 */
class CajaController extends Controller
{
    /**
     * Listar todas las cajas con paginación.
     *
     * @param Request $request Solicitud con el número de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/cajas",
     *     summary     = "Listar todas las cajas con paginación",
     *     tags        = {"Caja"},
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
     *         description = "Lista paginada de cajas",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Caja")
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
            $cajas = Caja::with(['cajero', 'canal'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($cajas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear una nueva caja.
     *
     * @param Request $request Solicitud con los datos a registrar.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/cajas",
     *     summary = "Crear una caja",
     *     tags    = {"Caja"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/CajaInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Caja creada exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Caja")
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
        $validated = $request->validate(Caja::rules());
        $caja = Caja::create($validated);
        return response()->json($caja, 201);
    }

    /**
     * Obtener una caja por su ID.
     *
     * @param int $id ID de la caja.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/cajas/{id}",
     *     summary = "Obtener una caja por su ID",
     *     tags    = {"Caja"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la caja",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Caja encontrada",
     *       * @OA\JsonContent(ref="#/components/schemas/Caja")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Caja no encontrada"
     *     )
     * )
     */
    public function show($id)
    {
        $caja = Caja::with(['cajero', 'canal'])->findOrFail($id);
        return response()->json($caja);
    }

    /**
     * Actualizar una caja existente.
     *
     * @param Request $request Datos validados para actualización.
     * @param int     $id      ID de la caja.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/cajas/{id}",
     *     summary = "Actualizar una caja",
     *     tags    = {"Caja"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la caja",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar la caja",
     *       * @OA\JsonContent(ref="#/components/schemas/CajaInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Caja actualizada",
     *       * @OA\JsonContent(ref="#/components/schemas/Caja")
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
        $validated = $request->validate(Caja::rules());
        $caja = Caja::findOrFail($id);
        $caja->update($validated);
        return response()->json($caja, 200);
    }

    /**
     * Eliminar una caja.
     *
     * @param int $id ID de la caja.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/cajas/{id}",
     *     summary = "Eliminar una caja",
     *     tags    = {"Caja"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la caja",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Caja eliminada correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Caja no encontrada"
     *     )
     * )
     */
    public function destroy($id)
    {
        $caja = Caja::findOrFail($id);
        $caja->delete();
        return response()->json(['message' => 'Caja eliminada correctamente']);
    }
}
