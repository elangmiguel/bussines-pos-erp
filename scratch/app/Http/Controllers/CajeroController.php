<?php

/**
 * Definición del controlador HTTP para la entidad Cajero.
 *
 * Este archivo contiene la implementación del controlador responsable de gestionar
 * las operaciones relacionadas con la tabla `cajero`, como listar, crear,
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

use App\Models\Cajero;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Cajero.
 *
 * Gestiona la lógica de negocio para los cajeros, permitiendo
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
 *     name        = "Cajero",
 *     description = "Operaciones relacionadas con los cajeros del sistema"
 * )
 */
class CajeroController extends Controller
{
    /**
     * Listar todos los cajeros con paginación.
     *
     * @param Request $request Solicitud con el número de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/cajeros",
     *     summary     = "Listar todos los cajeros con paginación",
     *     tags        = {"Cajero"},
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
     *               * @OA\Items(ref="#/components/schemas/Cajero")
     *             ),
     *           * @OA\Property(
     *                 property = "current_page",
     *                 type     = "integer"
     *             ),
     *           * @OA\Property(
     *                 property = "last_page",
     *                 type     = "integer"
     *             ),
     *           * @OA\Property(
     *                 property = "per_page",
     *                 type     = "integer"
     *             ),
     *           * @OA\Property(
     *                 property = "total",
     *                 type     = "integer"
     *             )
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
            $cajeros = Cajero::with(['colaborador'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($cajeros);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo cajero.
     *
     * @param Request $request Solicitud con los datos a registrar.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/cajeros",
     *     summary = "Crear un cajero",
     *     tags    = {"Cajero"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/CajeroInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Cajero creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Cajero")
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
        $validated = $request->validate(Cajero::rules());
        $cajero = Cajero::create($validated);
        return response()->json($cajero, 201);
    }

    /**
     * Obtener un cajero por su ID.
     *
     * @param int $id ID del cajero.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/cajeros/{id}",
     *     summary = "Obtener un cajero por su ID",
     *     tags    = {"Cajero"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del cajero",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Cajero encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Cajero")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Cajero no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $cajero = Cajero::with(['colaborador'])->findOrFail($id);
        return response()->json($cajero);
    }

    /**
     * Actualizar un cajero existente.
     *
     * @param Request $request Datos validados para actualización.
     * @param int     $id      ID del cajero.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/cajeros/{id}",
     *     summary = "Actualizar un cajero",
     *     tags    = {"Cajero"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del cajero",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el cajero",
     *       * @OA\JsonContent(ref="#/components/schemas/CajeroInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Cajero actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Cajero")
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
        $validated = $request->validate(Cajero::rules());
        $cajero = Cajero::findOrFail($id);
        $cajero->update($validated);
        return response()->json($cajero, 200);
    }

    /**
     * Eliminar un cajero.
     *
     * @param int $id ID del cajero.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/cajeros/{id}",
     *     summary = "Eliminar un cajero",
     *     tags    = {"Cajero"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del cajero",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Cajero eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Cajero no encontrado"
     *     )
     * )
     */
    public function destroy($id)
    {
        $cajero = Cajero::findOrFail($id);
        $cajero->delete();
        return response()->json(['message' => 'Cajero eliminado correctamente']);
    }
}
