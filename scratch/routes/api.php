<?php
/**
 * Definición de rutas para documentación Swagger
 *
 * Este archivo contiene la declaración de rutas RESTful expuestas mediante
 * controladores tipo apiResource, utilizadas exclusivamente para la generación
 * de documentación en Swagger/OpenAPI.
 *
 * PHP version 8.1
 *
 * @category Documento
 * @package  App\Http\Controllers
 * @author   Miguel Ángel Rodriguez Ferreira    <miguel-rodriguez13@upc.edu.co>
 * @author   Diego Jose Suarez Cuervo           <diego-suarez3@upc.edu.co>
 * @license  123 License
 * @link     http://localhost:8000/
 */
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/

use App\Http\Controllers\ContactoController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\CanalController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ColaboradorController;
use App\Http\Controllers\CajeroController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ProveedorProductoController;
use App\Http\Controllers\TransaccionController;
use App\Http\Controllers\DetalleController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\FondoController;
use App\Http\Controllers\MovimientoController;

Route::apiResource('contactos', ContactoController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('personas', PersonaController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('canales', CanalController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('clientes', ClienteController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('usuarios', UsuarioController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('colaboradores', ColaboradorController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('cajeros', CajeroController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('empresas', EmpresaController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('productos', ProductoController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('proveedores', ProveedorController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('proveedorProductos', ProveedorProductoController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('transacciones', TransaccionController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('detalles', DetalleController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('cajas', CajaController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('fondos', FondoController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);
Route::apiResource('movimientos', MovimientoController::class)->only(
    [
    'index', 'store', 'show', 'update', 'destroy'
    ]
);


/*
Route::get('/cajas', [CajaController::class, 'index']);        // Listar cajas
Route::post('/cajas', [CajaController::class, 'store']);       // Crear caja
Route::get('/cajas/{id}', [CajaController::class, 'show']);    // Ver caja
Route::put('/cajas/{id}', [CajaController::class, 'update']);  // Actualizar caja
Route::delete('/cajas/{id}', [CajaController::class, 'destroy']); // Eliminar caja

Route::get('/cajeros', [CajeroController::class, 'index']);        // Listar cajeros
Route::post('/cajeros', [CajeroController::class, 'store']);       // Crear cajero
Route::get('/cajeros/{id}', [CajeroController::class, 'show']);    // Ver cajero
Route::put('/cajeros/{id}', [CajeroController::class, 'update']);  // Actualizar cajero
Route::delete('/cajeros/{id}', [CajeroController::class, 'destroy']); // Eliminar cajero

Route::get('/canales', [CanalController::class, 'index']);        // Listar canales
Route::post('/canales', [CanalController::class, 'store']);       // Crear canal
Route::get('/canales/{id}', [CanalController::class, 'show']);    // Ver canal
Route::put('/canales/{id}', [CanalController::class, 'update']);  // Actualizar canal
Route::delete('/canales/{id}', [CanalController::class, 'destroy']); // Eliminar canal

Route::get('/clientes', [ClienteController::class, 'index']);        // Listar clientes
Route::post('/clientes', [ClienteController::class, 'store']);       // Crear cliente
Route::get('/clientes/{id}', [ClienteController::class, 'show']);    // Ver cliente
Route::put('/clientes/{id}', [ClienteController::class, 'update']);  // Actualizar cliente
Route::delete('/clientes/{id}', [ClienteController::class, 'destroy']); // Eliminar cliente

Route::get('/colaboradores', [ColaboradorController::class, 'index']);        // Listar colaboradores
Route::post('/colaboradores', [ColaboradorController::class, 'store']);       // Crear Colaborador
Route::get('/colaboradores/{id}', [ColaboradorController::class, 'show']);    // Ver Colaborador
Route::put('/colaboradores/{id}', [ColaboradorController::class, 'update']);  // Actualizar Colaborador
Route::delete('/colaboradores/{id}', [ColaboradorController::class, 'destroy']); // Eliminar Colaborador

Route::get('/contactos', [ContactoController::class, 'index']);        // Listar contactos
Route::post('/contactos', [ContactoController::class, 'store']);       // Crear Contacto
Route::get('/contactos/{id}', [ContactoController::class, 'show']);    // Ver Contacto
Route::put('/contactos/{id}', [ContactoController::class, 'update']);  // Actualizar Contacto
Route::delete('/contactos/{id}', [ContactoController::class, 'destroy']); // Eliminar Contacto

Route::get('/detalles', [DetalleController::class, 'index']);        // Listar detalles
Route::post('/detalles', [DetalleController::class, 'store']);       // Crear Detalle
Route::get('/detalles/{id}', [DetalleController::class, 'show']);    // Ver Detalle
Route::put('/detalles/{id}', [DetalleController::class, 'update']);  // Actualizar Detalle
Route::delete('/detalles/{id}', [DetalleController::class, 'destroy']); // Eliminar Detalle

Route::get('/empresas', [EmpresaController::class, 'index']);        // Listar empresas
Route::post('/empresas', [EmpresaController::class, 'store']);       // Crear Empresa
Route::get('/empresas/{id}', [EmpresaController::class, 'show']);    // Ver Empresa
Route::put('/empresas/{id}', [EmpresaController::class, 'update']);  // Actualizar Empresa
Route::delete('/empresas/{id}', [EmpresaController::class, 'destroy']); // Eliminar Empresa

Route::get('/fondos', [FondoController::class, 'index']);        // Listar fondos
Route::post('/fondos', [FondoController::class, 'store']);       // Crear Fondo
Route::get('/fondos/{id}', [FondoController::class, 'show']);    // Ver Fondo
Route::put('/fondos/{id}', [FondoController::class, 'update']);  // Actualizar Fondo
Route::delete('/fondos/{id}', [FondoController::class, 'destroy']); // Eliminar Fondo

Route::get('/movimientos', [MovimientoController::class, 'index']);        // Listar movimientos
Route::post('/movimientos', [MovimientoController::class, 'store']);       // Crear Movimiento
Route::get('/movimientos/{id}', [MovimientoController::class, 'show']);    // Ver Movimiento
Route::put('/movimientos/{id}', [MovimientoController::class, 'update']);  // Actualizar Movimiento
Route::delete('/movimientos/{id}', [MovimientoController::class, 'destroy']); // Eliminar Movimiento

Route::get('/personas', [PersonaController::class, 'index']);        // Listar personas
Route::post('/personas', [PersonaController::class, 'store']);       // Crear Persona
Route::get('/personas/{id}', [PersonaController::class, 'show']);    // Ver Persona
Route::put('/personas/{id}', [PersonaController::class, 'update']);  // Actualizar Persona
Route::delete('/personas/{id}', [PersonaController::class, 'destroy']); // Eliminar Persona

Route::get('/productos', [ProductoController::class, 'index']);        // Listar producto
Route::post('/productos', [ProductoController::class, 'store']);       // Crear Producto
Route::get('/productos/{id}', [ProductoController::class, 'show']);    // Ver Producto
Route::put('/productos/{id}', [ProductoController::class, 'update']);  // Actualizar Producto
Route::delete('/productos/{id}', [ProductoController::class, 'destroy']); // Eliminar caja

Route::get('/proveedores', [ProveedorController::class, 'index']);        // Listar proveedores
Route::post('/proveedores', [ProveedorController::class, 'store']);       // Crear Proveedor
Route::get('/proveedores/{id}', [ProveedorController::class, 'show']);    // Ver Proveedor
Route::put('/proveedores/{id}', [ProveedorController::class, 'update']);  // Actualizar Proveedor
Route::delete('/proveedores/{id}', [ProveedorController::class, 'destroy']); // Eliminar Proveedor

Route::get('/proveedoresProductos', [ProveedorProductoController::class, 'index']);        // Listar ProveedoresProductos
Route::post('/proveedoresProductos', [ProveedorProductoController::class, 'store']);       // Crear ProveedorProducto
Route::get('/proveedoresProductos/{id}', [ProveedorProductoController::class, 'show']);    // Ver ProveedorProducto
Route::put('/proveedoresProductos/{id}', [ProveedorProductoController::class, 'update']);  // Actualizar ProveedorProducto
Route::delete('/proveedoresProductos/{id}', [ProveedorProductoController::class, 'destroy']); // Eliminar ProveedorProducto

Route::get('/transacciones', [TransaccionController::class, 'index']);        // Listar Transacciones
Route::post('/transacciones', [TransaccionController::class, 'store']);       // Crear Transaccion
Route::get('/transacciones/{id}', [TransaccionController::class, 'show']);    // Ver Transaccion
Route::put('/transacciones/{id}', [TransaccionController::class, 'update']);  // Actualizar Transaccion
Route::delete('/transacciones/{id}', [TransaccionController::class, 'destroy']); // Eliminar Transaccion

Route::get('/usuarios', [UsuarioController::class, 'index']);        // Listar usuarios
Route::post('/usuarios', [UsuarioController::class, 'store']);       // Crear Usuario
Route::get('/usuarios/{id}', [UsuarioController::class, 'show']);    // Ver Usuario
Route::put('/usuarios/{id}', [UsuarioController::class, 'update']);  // Actualizar Usuario
Route::delete('/usuarios/{id}', [UsuarioController::class, 'destroy']); // Eliminar Usuario
*/





