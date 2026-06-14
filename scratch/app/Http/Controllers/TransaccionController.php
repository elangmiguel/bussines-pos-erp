<?php

/**
 * Controlador HTTP para la entidad Transaccion.
 *
 * Gestiona las operaciones CRUD para la tabla `transaccion`.
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

use App\Models\Transaccion;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de transacciones.
 *
 * Administra la lógica de negocio para la entidad Transaccion,
 * cargando relaciones cajero, cliente, proveedor y canal.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Transaccion",
 *     description = "Operaciones relacionadas con transacciones"
 * )
 */
class TransaccionController extends Controller
{
    /**
     * Listar todas las transacciones con paginación y relaciones cargadas.
     *
     * @param Request $request Parámetros HTTP de la solicitud.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/transacciones",
     *     summary     = "Listar transacciones",
     *     tags        = {"Transaccion"},
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
     *         description = "Lista paginada de transacciones con relaciones",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Transaccion")
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
            $transacciones = Transaccion::with(
                [
                    'cajero', 'cliente', 'proveedor', 'canal'
                    ]
            )
                ->paginate(10, ['*'], 'page', $page);
            return response()
                ->json($transacciones);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear una nueva transacción.
     *
     * @param Request $request Datos para crear la transacción.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/transacciones",
     *     summary = "Crear una transacción",
     *     tags    = {"Transaccion"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/TransaccionInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Transacción creada exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Transaccion")
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
        $validated = $request->validate(Transaccion::rules());
        $transaccion = Transaccion::create($validated);
        return response()->json($transaccion, 201);
    }

    /**
     * Obtener una transacción por su ID con relaciones.
     *
     * @param int $id ID de la transacción.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/transacciones/{id}",
     *     summary = "Obtener transacción por ID",
     *     tags    = {"Transaccion"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la transacción",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Transacción encontrada",
     *       * @OA\JsonContent(ref="#/components/schemas/Transaccion")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Transacción no encontrada"
     *     )
     * )
     */
    public function show(int $id)
    {
        $transaccion = Transaccion::with(['cajero', 'cliente', 'proveedor', 'canal'])
            ->findOrFail($id);
        return response()->json($transaccion);
    }

    /**
     * Actualizar una transacción existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID de la transacción.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/transacciones/{id}",
     *     summary = "Actualizar una transacción",
     *     tags    = {"Transaccion"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la transacción",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar la transacción",
     *       * @OA\JsonContent(ref="#/components/schemas/TransaccionInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Transacción actualizada",
     *       * @OA\JsonContent(ref="#/components/schemas/Transaccion")
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
        $validated = $request->validate(Transaccion::rules());
        $transaccion = Transaccion::findOrFail($id);
        $transaccion->update($validated);
        return response()->json($transaccion);
    }

    /**
     * Eliminar una transacción por su ID.
     *
     * @param int $id ID de la transacción.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/transacciones/{id}",
     *     summary = "Eliminar una transacción",
     *     tags    = {"Transaccion"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la transacción",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Transacción eliminada correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Transacción no encontrada"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $transaccion = Transaccion::findOrFail($id);
        $transaccion->delete();
        return response()
            ->json(['message' => 'Transacción eliminada correctamente']);
    }
}
