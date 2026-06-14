<?php

/**
 * Definición del controlador HTTP para la entidad Cliente.
 *
 * Este archivo contiene la implementación del controlador responsable de gestionar
 * las operaciones relacionadas con la tabla `cliente`, como listar, crear,
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

use App\Models\Cliente;
use Illuminate\Http\Request;

/**
 * Controlador para operaciones relacionadas con la entidad Cliente.
 *
 * Gestiona la lógica de negocio para los clientes, permitiendo
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
 *     name        = "Cliente",
 *     description = "Operaciones relacionadas con los clientes del sistema"
 * )
 */
class ClienteController extends Controller
{
    /**
     * Listar todos los clientes con paginación.
     *
     * @param Request $request Solicitud con el número de página.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path        = "/api/clientes",
     *     summary     = "Listar todos los clientes con paginación",
     *     tags        = {"Cliente"},
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
     *         description = "Lista paginada de clientes",
     *       * @OA\JsonContent(
     *             type       = "object",
     *           * @OA\Property(
     *                 property = "data",
     *                 type     = "array",
     *               * @OA\Items(ref="#/components/schemas/Cliente")
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
            $clientes = Cliente::with(['persona'])
                ->paginate(10, ['*'], 'page', $page);
            return response()->json($clientes);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Crear un nuevo cliente.
     *
     * @param Request $request Solicitud con los datos a registrar.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Post(
     *     path    = "/api/clientes",
     *     summary = "Crear un cliente",
     *     tags    = {"Cliente"},
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *       * @OA\JsonContent(ref="#/components/schemas/ClienteInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 201,
     *         description = "Cliente creado exitosamente",
     *       * @OA\JsonContent(ref="#/components/schemas/Cliente")
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
        $validated = $request->validate(Cliente::rules());
        $cliente = Cliente::create($validated);
        return response()->json($cliente, 201);
    }

    /**
     * Obtener un cliente por su ID.
     *
     * @param int $id ID del cliente.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Get(
     *     path    = "/api/clientes/{id}",
     *     summary = "Obtener un cliente por su ID",
     *     tags    = {"Cliente"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del cliente",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Cliente encontrado",
     *       * @OA\JsonContent(ref="#/components/schemas/Cliente")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Cliente no encontrado"
     *     )
     * )
     */
    public function show($id)
    {
        $cliente = Cliente::with(['persona'])->findOrFail($id);
        return response()->json($cliente);
    }

    /**
     * Actualizar un cliente existente.
     *
     * @param Request $request Datos validados para actualización.
     * @param int     $id      ID del cliente.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Put(
     *     path    = "/api/clientes/{id}",
     *     summary = "Actualizar un cliente",
     *     tags    = {"Cliente"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del cliente",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\RequestBody(
     *         required    = true,
     *         description = "Datos para actualizar el cliente",
     *       * @OA\JsonContent(ref="#/components/schemas/ClienteInput")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Cliente actualizado",
     *       * @OA\JsonContent(ref="#/components/schemas/Cliente")
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
        $validated = $request->validate(Cliente::rules());
        $cliente = Cliente::findOrFail($id);
        $cliente->update($validated);
        return response()->json($cliente, 200);
    }

    /**
     * Eliminar un cliente.
     *
     * @param int $id ID del cliente.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @OA\Delete(
     *     path    = "/api/clientes/{id}",
     *     summary = "Eliminar un cliente",
     *     tags    = {"Cliente"},
     *
     *   * @OA\Parameter(
     *         name        = "id",
     *         in          = "path",
     *         required    = true,
     *         description = "ID del cliente",
     *       * @OA\Schema(type="integer")
     *     ),
     *
     *   * @OA\Response(
     *         response    = 200,
     *         description = "Cliente eliminado correctamente"
     *     ),
     *
     *   * @OA\Response(
     *         response    = 404,
     *         description = "Cliente no encontrado"
     *     )
     * )
     */
    public function destroy($id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();
        return response()->json(['message' => 'Cliente eliminado correctamente']);
    }
}
