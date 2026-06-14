<?php

/**
 * Controlador HTTP para la entidad Usuario.
 *
 * Gestiona las operaciones CRUD para la tabla `usuario`.
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

use App\Models\Usuario;
use Illuminate\Http\Request;

/**
 * Controlador para la gestión de usuarios.
 *
 * Administra la lógica de negocio para la entidad Usuario,
 * cargando la relación persona.
 *
 * @category Controlador
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Tag(
 *     name        = "Usuario",
 *     description = "Operaciones relacionadas con usuarios"
 * )
 */
class UsuarioController extends Controller
{
    /**
     * Listar todos los usuarios con paginación y relación persona cargada.
     *
     * @param Request $request Parámetros HTTP de la solicitud.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/usuarios",
     *     summary     = "Listar usuarios",
     *     tags        = {"Usuario"},
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
     *         description = "Lista paginada de usuarios con relación persona",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Usuario")
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
            $usuarios = Usuario::with(['persona'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($usuarios);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo usuario.
     *
     * @param Request $request Datos para crear el usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/usuarios",
     *     summary = "Crear un usuario",
     *     tags    = {"Usuario"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/UsuarioInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Usuario creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Usuario")
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
        $validated = $request->validate(Usuario::rules());
        $usuario = Usuario::create($validated);
        return response()->json($usuario, 201);
    }

    /**
     * Obtener un usuario por su ID con relación persona.
     *
     * @param int $id ID del usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/usuarios/{id}",
     *     summary = "Obtener usuario por ID",
     *     tags    = {"Usuario"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del usuario",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Usuario encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Usuario")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Usuario no encontrado"
     *     )
     * )
     */
    public function show(int $id)
    {
        $usuario = Usuario::with(['persona'])->findOrFail($id);
        return response()->json($usuario);
    }

    /**
     * Actualizar un usuario existente.
     *
     * @param Request $request Datos para actualización.
     * @param int     $id      ID del usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/usuarios/{id}",
     *     summary = "Actualizar un usuario",
     *     tags    = {"Usuario"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del usuario",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el usuario",
     *       * @OA\JsonContent(ref="#/components/schemas/UsuarioInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Usuario actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Usuario")
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
        $validated = $request->validate(Usuario::rules());
        $usuario = Usuario::findOrFail($id);
        $usuario->update($validated);
        return response()->json($usuario);
    }

    /**
     * Eliminar un usuario por su ID.
     *
     * @param int $id ID del usuario.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/usuarios/{id}",
     *     summary = "Eliminar un usuario",
     *     tags    = {"Usuario"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del usuario",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Usuario eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Usuario no encontrado"
     *     )
     * )
     */
    public function destroy(int $id)
    {
        $usuario = Usuario::findOrFail($id);
        $usuario->delete();
        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }
}
