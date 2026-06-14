<?php

/**
 * Controlador HTTP para la entidad Proveedor.
 *
 * Gestiona las operaciones CRUD para la tabla `proveedor`.
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

use App\Models\Proveedor;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de proveedores.
 *
 * Administra la lógica de negocio para la entidad Proveedor,
 * cargando relaciones persona y empresa.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Proveedor",
 *     description = "Operaciones relacionadas con proveedores"
 * )
 */
class ProveedorController extends Controller
{
    /**
     * Listar todos los proveedores con paginación.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/proveedores",
     *     summary     = "Listar proveedores",
     *     tags        = {"Proveedor"},
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
     *         description = "Lista paginada de proveedores",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Proveedor")
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
            $proveedores = Proveedor::with(['persona', 'empresa'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($proveedores);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo proveedor.
     *
     * @param Request $request Datos del proveedor a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/proveedores",
     *     summary = "Crear un proveedor",
     *     tags    = {"Proveedor"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Proveedor creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Proveedor")
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
        $validated = $request->validate(Proveedor::rules());
        $proveedor = Proveedor::create($validated);
        return response()->json($proveedor, 201);
    }

    /**
     * Obtener un proveedor por su ID.
     *
     * @param int $id ID del proveedor.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/proveedores/{id}",
     *     summary = "Obtener proveedor por ID",
     *     tags    = {"Proveedor"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del proveedor",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Proveedor encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Proveedor")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Proveedor no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $proveedor = Proveedor::with(['persona', 'empresa'])->findOrFail($id);
        return response()->json($proveedor);
    }

    /**
     * Actualizar un proveedor existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del proveedor.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/proveedores/{id}",
     *     summary = "Actualizar un proveedor",
     *     tags    = {"Proveedor"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del proveedor",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el proveedor",
     *       * @OA\JsonContent(ref="#/components/schemas/ProveedorInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Proveedor actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Proveedor")
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
        $validated = $request->validate(Proveedor::rules());
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($validated);
        return response()->json($proveedor);
    }

    /**
     * Eliminar un proveedor por su ID.
     *
     * @param int $id ID del proveedor.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/proveedores/{id}",
     *     summary = "Eliminar un proveedor",
     *     tags    = {"Proveedor"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del proveedor",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Proveedor eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Proveedor no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();
        return response()->json(['message' => 'Proveedor eliminado correctamente']);
    }
}
