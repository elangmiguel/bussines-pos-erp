<?php

/**
 * Controlador HTTP para la entidad Empresa.
 *
 * Gestiona las operaciones CRUD para la tabla `empresa`,
 * incluyendo la carga de las relaciones `cajero` y `canal`.
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

use App\Models\Empresa;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de empresas.
 *
 * Administra la lógica de negocio para la entidad Empresa,
 * cargando las relaciones con `cajero` y `canal`.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Empresa",
 *     description = "Operaciones relacionadas con empresas"
 * )
 */
class EmpresaController extends Controller
{
    /**
     * Listar todas las empresas con paginación y relaciones.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/empresas",
     *     summary     = "Listar empresas con cajeros y canales",
     *     tags        = {"Empresa"},
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
     *         description = "Lista paginada de empresas",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Empresa")
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
            $empresas = Empresa::with(['cajero', 'canal'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($empresas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear una nueva empresa.
     *
     * @param Request $request Datos de la empresa a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/empresas",
     *     summary = "Crear una empresa",
     *     tags    = {"Empresa"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/EmpresaInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Empresa creada exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Empresa")
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
        $validated = $request->validate(Empresa::rules());
        $empresa = Empresa::create($validated);
        return response()->json($empresa, 201);
    }

    /**
     * Obtener una empresa por su ID con relaciones.
     *
     * @param int $id ID de la empresa.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/empresas/{id}",
     *     summary = "Obtener empresa por ID con relaciones",
     *     tags    = {"Empresa"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la empresa",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Empresa encontrada",
     *       * @OA\JsonContent(ref="#/components/schemas/Empresa")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Empresa no encontrada"
     *     )
     * )
     */
    public function show(int $id)
    {
        $empresa = Empresa::with(['cajero', 'canal'])->findOrFail($id);
        return response()->json($empresa);
    }

    /**
     * Actualizar una empresa existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID de la empresa.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/empresas/{id}",
     *     summary = "Actualizar una empresa",
     *     tags    = {"Empresa"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la empresa",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar la empresa",
     *       * @OA\JsonContent(ref="#/components/schemas/EmpresaInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Empresa actualizada",
     *       * @OA\JsonContent(ref="#/components/schemas/Empresa")
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
        $validated = $request->validate(Empresa::rules());
        $empresa = Empresa::findOrFail($id);
        $empresa->update($validated);
        return response()->json($empresa);
    }

    /**
     * Eliminar una empresa por su ID.
     *
     * @param int $id ID de la empresa.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/empresas/{id}",
     *     summary = "Eliminar una empresa",
     *     tags    = {"Empresa"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la empresa",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Empresa eliminada correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Empresa no encontrada"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $empresa = Empresa::findOrFail($id);
        $empresa->delete();
        return response()->json(['message' => 'Empresa eliminada correctamente']);
    }
}
