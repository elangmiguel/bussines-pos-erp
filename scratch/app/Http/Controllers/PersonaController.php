<?php

/**
 * Controlador HTTP para la entidad Persona.
 *
 * Gestiona las operaciones CRUD para la tabla `persona`,
 * incluyendo la carga de la relación `contacto`.
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

use App\Models\Persona;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de personas.
 *
 * Administra la lógica de negocio para la entidad Persona,
 * cargando la relación con `contacto`.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Persona",
 *     description = "Operaciones relacionadas con personas"
 * )
 */
class PersonaController extends Controller
{
    /**
     * Listar todas las personas con paginación y relación contacto.
     *
     * @param Request $request Solicitud HTTP con parámetros de paginación.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/personas",
     *     summary     = "Listar personas con contacto",
     *     tags        = {"Persona"},
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
     *         description = "Lista paginada de personas",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Persona")
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
            $personas = Persona::with(['contacto'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($personas);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear una nueva persona.
     *
     * @param Request $request Datos de la persona a crear.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/personas",
     *     summary = "Crear una persona",
     *     tags    = {"Persona"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/PersonaInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Persona creada exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Persona")
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
        $validated = $request->validate(Persona::rules());
        $persona = Persona::create($validated);
        return response()->json($persona, 201);
    }

    /**
     * Obtener una persona por su ID con relación contacto.
     *
     * @param int $id ID de la persona.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/personas/{id}",
     *     summary = "Obtener persona por ID con contacto",
     *     tags    = {"Persona"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la persona",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Persona encontrada",
     *       * @OA\JsonContent(ref="#/components/schemas/Persona")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Persona no encontrada"
     *     )
     * )
     */
    public function show(int $id)
    {
        $persona = Persona::with(['contacto'])->findOrFail($id);
        return response()->json($persona);
    }

    /**
     * Actualizar una persona existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID de la persona.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/personas/{id}",
     *     summary = "Actualizar una persona",
     *     tags    = {"Persona"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la persona",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar la persona",
     *       * @OA\JsonContent(ref="#/components/schemas/PersonaInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Persona actualizada",
     *       * @OA\JsonContent(ref="#/components/schemas/Persona")
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
        $validated = $request->validate(Persona::rules());
        $persona = Persona::findOrFail($id);
        $persona->update($validated);
        return response()->json($persona);
    }

    /**
     * Eliminar una persona por su ID.
     *
     * @param int $id ID de la persona.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/personas/{id}",
     *     summary = "Eliminar una persona",
     *     tags    = {"Persona"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID de la persona",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Persona eliminada correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Persona no encontrada"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $persona = Persona::findOrFail($id);
        $persona->delete();
        return response()->json(['message' => 'Persona eliminada correctamente']);
    }
}
