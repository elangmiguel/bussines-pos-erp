<?php

/**
 * Definición del controlador HTTP para la entidad Colaborador.
 *
 * Este archivo contiene la implementación del controlador responsable de gestionar
 * las operaciones relacionadas con la tabla `colaborador`, como listar, crear,
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

use App\Models\Colaborador;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Colaborador.
 *
 * Gestiona la lógica de negocio para los colaboradores, permitiendo
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
 *     name        = "Colaborador",
 *     description = "Operaciones relacionadas con los colaboradores del sistema"
 * )
 */
class ColaboradorController extends Controller
{
    /**
     * Listar todos los colaboradores con paginación.
     *
     * @param Request $request Solicitud con el número de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/colaboradores",
     *     summary     = "Listar todos los colaboradores con paginación",
     *     tags        = {"Colaborador"},
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
     *         description = "Lista paginada de colaboradores",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Colaborador")
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
            $colaboradores = Colaborador::with(['usuario'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($colaboradores);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo colaborador.
     *
     * @param Request $request Solicitud con los datos a registrar.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/colaboradores",
     *     summary = "Crear un colaborador",
     *     tags    = {"Colaborador"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/ColaboradorInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Colaborador creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Colaborador")
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
        $validated = $request->validate(Colaborador::rules());
        $colaborador = Colaborador::create($validated);
        return response()->json($colaborador, 201);
    }

    /**
     * Obtener un colaborador por su ID.
     *
     * @param int $id ID del colaborador.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/colaboradores/{id}",
     *     summary = "Obtener un colaborador por su ID",
     *     tags    = {"Colaborador"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del colaborador",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Colaborador encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Colaborador")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Colaborador no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $colaborador = Colaborador::with(['usuario'])->findOrFail($id);
        return response()->json($colaborador);
    }

    /**
     * Actualizar un colaborador existente.
     *
     * @param Request $request Datos validados para actualización.
     * @param int     $id      ID del colaborador.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/colaboradores/{id}",
     *     summary = "Actualizar un colaborador",
     *     tags    = {"Colaborador"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del colaborador",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el colaborador",
     *       * @OA\JsonContent(ref="#/components/schemas/ColaboradorInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Colaborador actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Colaborador")
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
        $validated = $request->validate(Colaborador::rules());
        $colaborador = Colaborador::findOrFail($id);
        $colaborador->update($validated);
        return response()->json($colaborador, 200);
    }

    /**
     * Eliminar un colaborador.
     *
     * @param int $id ID del colaborador.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/colaboradores/{id}",
     *     summary = "Eliminar un colaborador",
     *     tags    = {"Colaborador"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del colaborador",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Colaborador eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Colaborador no encontrado"
     *     )
     * )
     */
    public function destroy($id)
    {
        $colaborador = Colaborador::findOrFail($id);
        $colaborador->delete();
        return response()
            ->json(['message' => 'Colaborador eliminado correctamente']);
    }
}
