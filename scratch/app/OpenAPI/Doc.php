<?php
/**
 * Archivo de definición de documentación OpenAPI para la API
 * del sistema de punto de venta.
 *
 * Este archivo configura los metadatos generales para la documentación
 * generada con Swagger/OpenAPI.
 *
 * PHP version 8.1
 *
 * @category Documentation
 * @package  App\OpenAPI
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Proyecto académico personal - Universidad Piloto de Colombia
 * @link     https://localhost:8000/api/documentation
 */

namespace App\OpenAPI;

/**
 * Clase contenedora para la informacion general de las anotaciones OpenAPI.
 *
 * @category Documentation
 * @package  App\OpenAPI
 * @author   Miguel Ángel Rodríguez Ferreira <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo        <diego-suarez3@upc.edu.co>
 * @license  Universidad Piloto de Colombia SAM - Todos los Derechos Reservados
 * @link     https://localhost:8000/api/documentation
 *
 * @OA\Info(
 *     version="1.0.0",
 *     title="API - Sistema de Punto de Venta",
 *     description="Documentación de la API REST para el sistema de punto de venta.",
 * @OA\Contact(
 *         email="miguel-rodriguez13@upc.edu.co",
 * ),
 * @OA\License(
 *         name="Universidad Piloto de Colombia SAM - Todos los Derechos Reservados",
 * )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Servidor de desarrollo local"
 * )
 */
class Doc
{
}
