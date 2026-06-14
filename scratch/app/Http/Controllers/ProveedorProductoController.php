<?php

/**
 * Controlador HTTP para la entidad ProveedorProducto.
 *
 * Gestiona las operaciones CRUD para la tabla `proveedor_producto`.
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

use App\Models\ProveedorProducto;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de proveedor_producto.
 *
 * Administra la lógica de negocio para la entidad ProveedorProducto,
 * cargando relaciones proveedor y producto.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "ProveedorProducto",
 *     description = "Operaciones relacionadas con proveedor_producto"
 * )
 */
class ProveedorProductoController extends Controller
{
    /**
     * Listar todos los registros de proveedor_producto con paginación.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/proveedor_productos",
     *     summary     = "Listar proveedor_productos",
     *     tags        = {"ProveedorProducto"},
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
     *         description = "Lista paginada de proveedor_productos",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/ProveedorProducto")
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
            $proveedorProductos = ProveedorProducto::with(['proveedor', 'producto'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($proveedorProductos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo registro proveedor_producto.
     *
     * @param Request $request Datos para crear proveedor_producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/proveedor_productos",
     *     summary = "Crear un proveedor_producto",
     *     tags    = {"ProveedorProducto"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorProductoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "ProveedorProducto creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorProducto")
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
        $validated = $request->validate(ProveedorProducto::rules());
        $proveedorProducto = ProveedorProducto::create($validated);
        return response()->json($proveedorProducto, 201);
    }

    /**
     * Obtener un proveedor_producto por su ID.
     *
     * @param int $id ID del proveedor_producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/proveedor_productos/{id}",
     *     summary = "Obtener proveedor_producto por ID",
     *     tags    = {"ProveedorProducto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del proveedor_producto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "ProveedorProducto encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorProducto")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "ProveedorProducto no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $proveedorProducto = ProveedorProducto::with(['proveedor', 'producto'])
            ->findOrFail($id);
        return response()->json($proveedorProducto);
    }

    /**
     * Actualizar un proveedor_producto existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del proveedor_producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/proveedor_productos/{id}",
     *     summary = "Actualizar un proveedor_producto",
     *     tags    = {"ProveedorProducto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del proveedor_producto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el proveedor_producto",
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorProductoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "ProveedorProducto actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorProducto")
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
        $validated = $request->validate(ProveedorProducto::rules());
        $proveedorProducto = ProveedorProducto::findOrFail($id);
        $proveedorProducto->update($validated);
        return response()->json($proveedorProducto);
    }

    /**
     * Eliminar un proveedor_producto por su ID.
     *
     * @param int $id ID del proveedor_producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/proveedor_productos/{id}",
     *     summary = "Eliminar un proveedor_producto",
     *     tags    = {"ProveedorProducto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del proveedor_producto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "ProveedorProducto eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "ProveedorProducto no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $proveedorProducto = ProveedorProducto::findOrFail($id);
        $proveedorProducto->delete();
        return response()
            ->json(['message' => 'ProveedorProducto eliminado correctamente']);
    }
}
