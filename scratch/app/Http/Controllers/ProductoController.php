<?php

/**
 * Controlador HTTP para la entidad Producto.
 *
 * Gestiona las operaciones CRUD para la tabla `producto`.
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

use App\Models\Producto;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de productos.
 *
 * Administra la lógica de negocio para la entidad Producto,
 * sin cargar relaciones adicionales.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Producto",
 *     description = "Operaciones relacionadas con productos"
 * )
 */
class ProductoController extends Controller
{
    /**
     * Listar todos los productos con paginación.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/productos",
     *     summary     = "Listar productos",
     *     tags        = {"Producto"},
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
     *         description = "Lista paginada de productos",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Producto")
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
            $productos = Producto::with([])->paginate(10, ['*'], 'page', $page);
            return response()->json($productos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo producto.
     *
     * @param Request $request Datos del producto a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/productos",
     *     summary = "Crear un producto",
     *     tags    = {"Producto"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/ProductoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Producto creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Producto")
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
        $validated = $request->validate(Producto::rules());
        $producto = Producto::create($validated);
        return response()->json($producto, 201);
    }

    /**
     * Obtener un producto por su ID.
     *
     * @param int $id ID del producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/productos/{id}",
     *     summary = "Obtener producto por ID",
     *     tags    = {"Producto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del producto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Producto encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Producto")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Producto no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $producto = Producto::with([])->findOrFail($id);
        return response()->json($producto);
    }

    /**
     * Actualizar un producto existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/productos/{id}",
     *     summary = "Actualizar un producto",
     *     tags    = {"Producto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del producto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el producto",
     *       * @OA\JsonContent(ref="#/components/schemas/ProductoInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Producto actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Producto")
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
        $validated = $request->validate(Producto::rules());
        $producto = Producto::findOrFail($id);
        $producto->update($validated);
        return response()->json($producto);
    }

    /**
     * Eliminar un producto por su ID.
     *
     * @param int $id ID del producto.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/productos/{id}",
     *     summary = "Eliminar un producto",
     *     tags    = {"Producto"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del producto",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Producto eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Producto no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $producto = Producto::findOrFail($id);
        $producto->delete();
        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
