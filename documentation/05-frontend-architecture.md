# Frontend Architecture

## Stack

- **Svelte 5** with runes (`$state`, `$derived`, `$effect`)
- **Inertia.js** for page routing and server data
- **TypeScript** (strict mode)
- **Tailwind CSS 4** + **shadcn-svelte** (New York style)
- **bits-ui** for headless primitives
- **lucide-svelte** for icons
- **Laravel Reverb** (WebSocket) for real-time stock updates in POS

---

## Page Structure

```
resources/js/
├── app.ts                         # Inertia client entry
├── ssr.ts                         # SSR entry
├── types/
│   ├── models.ts                  # All Eloquent model shapes
│   ├── pages.ts                   # Per-page prop types
│   └── shared.ts                  # Auth, flash, empresa, turno
├── lib/
│   ├── utils.ts                   # cn(), formatCOP(), formatDate()
│   ├── theme.svelte.ts            # Dark/light mode (existing)
│   ├── currency.ts                # COP formatting, tax calculations
│   ├── permissions.ts             # Permission check helpers
│   └── pos-store.svelte.ts        # POS cart state (Svelte 5 runes)
├── layouts/
│   ├── AppLayout.svelte           # Authenticated shell (existing, extend)
│   ├── AuthLayout.svelte          # Login/register (existing)
│   └── POSLayout.svelte           # Full-screen POS layout (no sidebar)
├── components/
│   ├── ui/                        # shadcn-svelte primitives
│   ├── shared/
│   │   ├── DataTable.svelte       # Reusable paginated table
│   │   ├── SearchInput.svelte     # Debounced search
│   │   ├── MoneyInput.svelte      # COP formatted input
│   │   ├── DateRangePicker.svelte
│   │   ├── FlashMessage.svelte    # Success/error toasts
│   │   ├── ConfirmDialog.svelte
│   │   └── StatusBadge.svelte
│   ├── pos/
│   │   ├── CartItem.svelte
│   │   ├── ProductSearch.svelte   # Barcode + name search
│   │   ├── PaymentModal.svelte    # Multi-payment split
│   │   └── ReceiptPreview.svelte
│   ├── inventario/
│   │   ├── StockBadge.svelte      # Color-coded stock level
│   │   └── MovimientoRow.svelte
│   ├── facturacion/
│   │   ├── FacturaHeader.svelte
│   │   └── DianStatusBadge.svelte
│   └── reportes/
│       ├── ChartVentas.svelte
│       └── KpiCard.svelte
└── pages/
    ├── Dashboard.svelte
    ├── POS/
    │   └── Index.svelte            # Main POS screen
    ├── Inventario/
    │   ├── Index.svelte
    │   ├── Show.svelte             # Product detail + kardex
    │   └── Form.svelte             # Create/edit product
    ├── Clientes/
    │   ├── Index.svelte
    │   ├── Show.svelte             # Client + cartera
    │   └── Form.svelte
    ├── Proveedores/
    │   ├── Index.svelte
    │   ├── Show.svelte
    │   └── OrdenCompra/
    │       ├── Index.svelte
    │       ├── Form.svelte
    │       └── Recepcion.svelte
    ├── Facturacion/
    │   ├── Index.svelte
    │   ├── Show.svelte             # Invoice detail + PDF actions
    │   ├── NotaCredito/
    │   │   └── Form.svelte
    │   └── NotaDebito/
    │       └── Form.svelte
    ├── Caja/
    │   ├── Index.svelte            # Current shift status
    │   └── Turno/
    │       ├── Abrir.svelte
    │       └── Cerrar.svelte       # Cashier reconciliation
    ├── Reportes/
    │   ├── Index.svelte            # Report selector
    │   ├── Ventas.svelte
    │   ├── Inventario.svelte
    │   ├── Cartera.svelte
    │   └── DIAN/
    │       ├── LibroVentas.svelte
    │       └── LibroCompras.svelte
    └── Settings/
        ├── Empresa.svelte
        ├── Usuarios/
        │   ├── Index.svelte
        │   └── Form.svelte
        ├── Productos/
        │   ├── Categorias.svelte
        │   └── Tarifas.svelte
        ├── Caja/
        │   └── Index.svelte
        └── DIAN/
            └── Resolucion.svelte
```

---

## POS Page (`pages/POS/Index.svelte`)

The most critical screen — designed for speed and keyboard navigation.

### Layout (split panels)

```
┌─────────────────────────────────────────────────────┐
│  [Barcode input / product search]    [Cliente ▼]    │
├──────────────────────┬──────────────────────────────┤
│                      │  CARRITO                      │
│  Product grid /      │  ─────────────────────────── │
│  search results      │  Producto A  2  $10.000       │
│                      │  Producto B  1  $25.000       │
│                      │  ─────────────────────────── │
│                      │  Subtotal:        $35.000     │
│                      │  IVA 19%:          $6.650     │
│                      │  TOTAL:           $41.650     │
│                      │                              │
│                      │  [Descuento] [Limpiar]       │
│                      │  [COBRAR →]                  │
└──────────────────────┴──────────────────────────────┘
```

### Cart State (`lib/pos-store.svelte.ts`)

```ts
// Svelte 5 runes — no store imports needed
let items = $state<CartItem[]>([]);
let cliente = $state<Cliente | null>(null);
let descuentoGlobal = $state(0);

const subtotal = $derived(
  items.reduce((sum, i) => sum + i.cantidad * i.precio_unitario * (1 - i.descuento_pct / 100), 0)
);
const totalIva = $derived(
  items.reduce((sum, i) => sum + calcularIva(i), 0)
);
const total = $derived(subtotal + totalIva - descuentoGlobal);
```

### Barcode Scanner

Input field always focused; hardware scanners send a string ending in `Enter`. Debounced search for keyboard typing:

```ts
function onInput(value: string) {
  if (value.length > 5 && isBarcode(value)) {
    buscarPorBarcode(value);
  } else {
    debouncedSearch(value);
  }
}
```

### Payment Modal

Supports split payments: user allocates total across multiple `medios_pago`. Validates sum equals invoice total before submitting.

---

## TypeScript Types

### `types/shared.ts`

```ts
export interface PageProps {
  auth: {
    user: Usuario;
    permisos: Record<string, boolean>;
  };
  empresa: Configuracion & { empresa: Empresa };
  turno_activo: { id: number; caja_id: number } | null;
  flash: { success?: string; error?: string; warning?: string };
}
```

### `types/models.ts` (excerpt)

```ts
export interface Producto {
  id: number;
  codigo: string;
  codigo_barras: string | null;
  nombre: string;
  precio_venta: number;        // COP
  precio_compra: number;
  stock_actual: number;
  stock_minimo: number;
  tarifa_iva: TarifaIva;
  unidad_medida: UnidadMedida;
  categoria: CategoriaProducto | null;
}

export interface Factura {
  id: number;
  numero_completo: string;
  fecha: string;
  cliente: Cliente;
  total: number;
  estado: 'borrador' | 'emitida' | 'anulada';
  estado_dian: 'pendiente' | 'aceptada' | 'rechazada';
  tipo_pago: 'contado' | 'credito';
}
```

---

## Currency & Tax Utilities (`lib/currency.ts`)

```ts
/** Format number as Colombian Peso */
export function formatCOP(amount: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
  }).format(amount);
}

/** Calculate IVA for a cart item */
export function calcularIva(item: CartItem): number {
  const base = item.cantidad * item.precio_unitario * (1 - item.descuento_pct / 100);
  return Math.round(base * (item.tarifa_iva.porcentaje / 100));
}
```

---

## Permission Checks (`lib/permissions.ts`)

```ts
import { page } from '@inertiajs/svelte';

export function puedeHacer(permiso: string): boolean {
  return page.props.auth.permisos[permiso] === true;
}
```

Usage in Svelte:
```svelte
{#if puedeHacer('reportes.dian')}
  <Button href={route('reportes.dian.libro-ventas')}>Libro de Ventas</Button>
{/if}
```

---

## Real-time Stock Updates

When multiple cashiers sell simultaneously, POS stock must stay current. Using Laravel Reverb:

```ts
// In POS page — listen for stock changes
import Echo from 'laravel-echo';

Echo.channel('inventario')
  .listen('StockActualizado', (e: { producto_id: number; stock: number }) => {
    updateStockInSearch(e.producto_id, e.stock);
  });
```

Server dispatches `StockActualizado` event after every `InventarioService::descontarStock()` call.

---

## Navigation & Sidebar

`AppLayout.svelte` sidebar groups by module. Active state derived from Inertia `page.url`. Sidebar collapses to icons on small screens.

```
Dashboard
POS ──────── (highlighted when turno_activo)
Inventario
  └ Productos
  └ Ajustes
  └ Categorías
Clientes
  └ Directorio
  └ Cartera
Proveedores
  └ Directorio
  └ Órdenes de Compra
Facturación
Caja & Fondos
Gastos
Reportes
  └ Ventas
  └ Inventario
  └ Cartera
  └ DIAN
Configuración
```

---

## Key UX Decisions

- **Language**: Full Spanish UI — labels, error messages, status text
- **Currency display**: Always COP with thousands separator (`$1.234.567`)
- **Dates**: `dd/MM/yyyy` format (Colombian standard)
- **Required field for POS**: Turno de caja must be open; middleware redirects if not
- **Thermal receipt**: Opens print dialog targeting 80mm page size
- **Responsive**: Sidebar collapses on tablet; POS works landscape on tablet; not designed for mobile
