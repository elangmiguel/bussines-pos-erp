# Backend Architecture

## Directory Structure

```
application/app/
├── Actions/
│   ├── Fortify/              # Auth actions (existing)
│   ├── Facturas/
│   │   ├── CrearFactura.php
│   │   ├── AnularFactura.php
│   │   └── GenerarCufe.php
│   ├── Inventario/
│   │   ├── AjustarStock.php
│   │   └── RegistrarMovimiento.php
│   ├── Caja/
│   │   ├── AbrirTurno.php
│   │   └── CerrarTurno.php
│   └── Reportes/
│       ├── GenerarLibroVentas.php
│       └── GenerarLibroCompras.php
├── Http/
│   ├── Controllers/
│   │   ├── POS/
│   │   │   └── VentaController.php       # POS transaction endpoint
│   │   ├── Inventario/
│   │   │   ├── ProductoController.php
│   │   │   ├── CategoriaController.php
│   │   │   └── MovimientoController.php
│   │   ├── Clientes/
│   │   │   ├── ClienteController.php
│   │   │   └── CarteraController.php
│   │   ├── Proveedores/
│   │   │   ├── ProveedorController.php
│   │   │   └── OrdenCompraController.php
│   │   ├── Facturacion/
│   │   │   ├── FacturaController.php
│   │   │   ├── NotaCreditoController.php
│   │   │   └── NotaDebitoController.php
│   │   ├── Caja/
│   │   │   ├── CajaController.php
│   │   │   └── TurnoCajaController.php
│   │   ├── Reportes/
│   │   │   └── ReporteController.php
│   │   └── Settings/                     # Existing pattern
│   │       ├── EmpresaController.php
│   │       ├── UsuarioController.php
│   │       └── ConfiguracionController.php
│   ├── Middleware/
│   │   ├── HandleInertiaRequests.php     # Share auth, permisos, config
│   │   └── VerificarTurnoCaja.php        # Ensure POS has open shift
│   └── Requests/                         # Form request validation per entity
├── Models/
│   ├── Persona.php
│   ├── Empresa.php
│   ├── Usuario.php
│   ├── Rol.php
│   ├── Colaborador.php
│   ├── Cajero.php
│   ├── Cliente.php
│   ├── Proveedor.php
│   ├── Producto.php
│   ├── CategoriaProducto.php
│   ├── UnidadMedida.php
│   ├── TarifaIva.php
│   ├── ProveedorProducto.php
│   ├── MovimientoInventario.php
│   ├── MedioPago.php
│   ├── Fondo.php
│   ├── Caja.php
│   ├── TurnoCaja.php
│   ├── ResolucionDian.php
│   ├── Factura.php
│   ├── DetalleFactura.php
│   ├── PagoFactura.php
│   ├── NotaCredito.php
│   ├── NotaDebito.php
│   ├── CuentaPorCobrar.php
│   ├── AbonoCartera.php
│   ├── OrdenCompra.php
│   ├── DetalleOrdenCompra.php
│   ├── RecepcionMercancia.php
│   ├── DetalleRecepcion.php
│   ├── CuentaPorPagar.php
│   ├── Gasto.php
│   ├── CategoriaGasto.php
│   ├── MovimientoFondo.php
│   └── Configuracion.php
├── Services/
│   ├── DianService.php          # CUFE generation, XML UBL 2.1, QR
│   ├── InventarioService.php    # Stock logic + movement registration
│   ├── FacturaService.php       # Invoice creation orchestration
│   ├── CarteraService.php       # Credit and payment tracking
│   ├── ReporteService.php       # Report generation (PDF + Excel)
│   └── FondoService.php         # Fund balance management
└── Policies/
    ├── FacturaPolicy.php
    ├── ProductoPolicy.php
    └── ReportePolicy.php
```

---

## Routing

```
routes/
├── web.php              # Auth routes (Fortify) + dashboard
├── pos.php              # POS module routes
├── inventario.php
├── clientes.php
├── proveedores.php
├── facturacion.php
├── caja.php
├── reportes.php
└── settings.php         # Existing pattern — expand for business settings
```

All routes return `Inertia::render()`. No separate API layer needed since Inertia handles server-to-client data. Use Laravel Wayfinder for typed route helpers on the frontend.

---

## Key Patterns

### Inertia Controller Pattern

```php
// FacturaController.php
public function index(Request $request): Response
{
    return Inertia::render('Facturacion/Index', [
        'facturas' => FacturaResource::collection(
            Factura::with(['cliente', 'usuario'])
                ->filter($request->only(['estado', 'fecha_desde', 'fecha_hasta']))
                ->paginate(20)
        ),
        'estadisticas' => $this->resumen($request),
    ]);
}

public function store(CrearFacturaRequest $request): RedirectResponse
{
    $factura = app(CrearFactura::class)->execute($request->validated());
    return redirect()->route('facturacion.show', $factura)
        ->with('success', "Factura {$factura->numero_completo} creada.");
}
```

### Action Pattern (single-responsibility business logic)

```php
// Actions/Facturas/CrearFactura.php
class CrearFactura
{
    public function __construct(
        private InventarioService $inventario,
        private DianService $dian,
        private FondoService $fondos,
    ) {}

    public function execute(array $data): Factura
    {
        return DB::transaction(function () use ($data) {
            $factura = Factura::create([...]);

            foreach ($data['items'] as $item) {
                $factura->detalles()->create([...]);
                $this->inventario->descontarStock($item['producto_id'], $item['cantidad'], $factura);
            }

            foreach ($data['pagos'] as $pago) {
                $factura->pagos()->create($pago);
                $this->fondos->registrarIngreso($pago['fondo_id'], $pago['monto'], $factura);
            }

            $factura->update([
                'cufe' => $this->dian->generarCufe($factura),
                'xml_dian' => $this->dian->generarXml($factura),
            ]);

            return $factura->fresh();
        });
    }
}
```

### Shared Inertia Props

`HandleInertiaRequests.php` shares globally across all pages:

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user()?->load('rol'),
            'permisos' => $request->user()?->rol->permisos ?? [],
        ],
        'empresa' => fn() => Configuracion::with('empresa')->first(),
        'turno_activo' => fn() => TurnoCaja::abierto()->first()?->only(['id', 'caja_id']),
        'flash' => $request->session()->only(['success', 'error', 'warning']),
    ];
}
```

---

## Services

### `DianService`

Handles all DIAN electronic document logic:

- `generarCufe(Factura $f): string` — SHA-384 hash per DIAN Annex 20
- `generarXml(Factura $f): string` — UBL 2.1 XML
- `generarQr(Factura $f): string` — URL with CUFE
- `enviarDian(Factura $f): array` — POST to Operador Habilitado API
- `generarCufn(NotaCredito $n): string`

### `InventarioService`

- `descontarStock(int $productoId, float $cantidad, Model $referencia): void`
- `incrementarStock(int $productoId, float $cantidad, Model $referencia): void`
- `ajustarStock(int $productoId, float $nuevaCantidad, string $motivo): void`
- Internally always creates a `MovimientoInventario` record

### `ReporteService`

- `libroVentas(Carbon $desde, Carbon $hasta): Collection`
- `libroCompras(Carbon $desde, Carbon $hasta): Collection`
- `antiguedadCartera(): Collection`
- `kardex(Producto $p, Carbon $desde, Carbon $hasta): Collection`
- `exportarExcel(string $tipo, array $params): BinaryFileResponse`
- `exportarPdf(string $tipo, array $params): Response`

---

## Authorization

Using `spatie/laravel-permission` with roles stored in DB. Gates defined in `AuthServiceProvider`:

```php
Gate::define('administrar', fn($user) => $user->rol->nombre === 'administrador');
Gate::define('facturar', fn($user) => in_array($user->rol->nombre, ['administrador','vendedor','cajero']));
Gate::define('ver-reportes-dian', fn($user) => $user->rol->nombre === 'administrador');
```

Blade/Inertia checks: `$page.props.auth.permisos['facturas.create']`

---

## Queue Jobs

Long-running operations dispatched as jobs:

| Job | Trigger |
|---|---|
| `EnviarFacturaDian` | After invoice creation |
| `EnviarEmailFactura` | After invoice creation (if client has email) |
| `GenerarReportePdf` | On report request |
| `ActualizarEstadoDian` | Scheduled hourly — check pending invoices |

---

## PDF Generation

`barryvdh/laravel-dompdf` with Blade templates:

```
resources/views/pdf/
├── factura.blade.php        # Full A4 DIAN-compliant invoice
├── factura_termica.blade.php # 80mm thermal receipt
├── nota_credito.blade.php
└── reportes/
    ├── libro_ventas.blade.php
    └── kardex.blade.php
```
