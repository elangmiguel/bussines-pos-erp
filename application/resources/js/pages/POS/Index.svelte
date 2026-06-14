<script lang="ts">
    import { untrack } from 'svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Badge } from '@/components/ui/badge';
    import { Separator } from '@/components/ui/separator';
    import {
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
        DialogFooter,
        DialogClose,
    } from '@/components/ui/dialog';
    import {
        ShoppingCart,
        Search,
        User,
        Plus,
        Minus,
        Trash2,
        CreditCard,
        Banknote,
        X,
        AlertCircle,
        CheckCircle2,
        Package,
    } from 'lucide-svelte';
    import { apiFetch } from '@/lib/api';
    import { carrito } from '@/lib/pos-store.svelte';
    import { formatCOP } from '@/lib/currency';
    import type { Cliente, MedioPago } from '@/types/models';

    // ---------------------------------------------------------------------------
    // Local interfaces
    // ---------------------------------------------------------------------------
    interface ResolucionInfo {
        id: number;
        prefijo: string | null;
        numero_actual: number;
        rango_hasta: number;
        fecha_fin: string;
    }

    interface TurnoInfo {
        id: number;
        caja: { id: number; nombre: string };
        cajero: Record<string, unknown>;
    }

    interface FondoInfo {
        id: number;
        nombre: string;
        tipo: string;
        saldo_actual: number;
        medio_pago_id: number;
    }

    interface ProductoResult {
        id: number;
        codigo: string;
        codigo_barras: string | null;
        nombre: string;
        precio_venta: number;
        stock_actual: number;
        tarifa_iva: { id: number; nombre: string; tipo: string; porcentaje: number } | null;
    }

    interface ClienteResult {
        id: number;
        nombre: string;
        identificacion: string;
        tipo: string;
        tipo_cliente: string;
        credito_activo: boolean;
        limite_credito: number;
        plazo_dias: number;
    }

    // ---------------------------------------------------------------------------
    // Props
    // ---------------------------------------------------------------------------
    let {
        turno_activo,
        medios_pago,
        fondos,
        resolucion_activa,
        resolucion,
        cliente_consumidor_final,
    }: {
        turno_activo: TurnoInfo | null;
        medios_pago: MedioPago[];
        fondos: FondoInfo[];
        resolucion_activa: boolean;
        resolucion: ResolucionInfo | null;
        cliente_consumidor_final: ClienteResult | null;
    } = $props();

    // ---------------------------------------------------------------------------
    // Product search
    // ---------------------------------------------------------------------------
    let searchQuery = $state('');
    let productResults = $state<ProductoResult[]>([]);
    let searchLoading = $state(false);
    let showProductDropdown = $state(false);
    let searchInputEl: HTMLInputElement | undefined = $state();
    let searchDebounce: ReturnType<typeof setTimeout> | null = null;

    function focusSearch(): void {
        setTimeout(() => searchInputEl?.focus(), 60);
    }

    async function doProductSearch(q: string): Promise<void> {
        if (!q.trim()) {
            productResults = [];
            showProductDropdown = false;
            return;
        }
        searchLoading = true;
        try {
            const res = await apiFetch(`/pos/buscar-producto?q=${encodeURIComponent(q)}`);
            if (res.ok) {
                productResults = await res.json();
                showProductDropdown = productResults.length > 0;
            }
        } catch {
            productResults = [];
            showProductDropdown = false;
        } finally {
            searchLoading = false;
        }
    }

    function onSearchInput(): void {
        const q = searchQuery.trim();
        if (!q) {
            productResults = [];
            showProductDropdown = false;
            return;
        }

        if (searchDebounce) clearTimeout(searchDebounce);

        searchDebounce = setTimeout(async () => {
            await doProductSearch(q);
            // Auto-add if exact barcode/code match
            untrack(() => {
                if (
                    productResults.length === 1 &&
                    (productResults[0].codigo === q || productResults[0].codigo_barras === q)
                ) {
                    carrito.agregarProducto(productResults[0]);
                    searchQuery = '';
                    productResults = [];
                    showProductDropdown = false;
                    focusSearch();
                }
            });
        }, 300);
    }

    function selectProduct(p: ProductoResult): void {
        carrito.agregarProducto(p);
        searchQuery = '';
        productResults = [];
        showProductDropdown = false;
        focusSearch();
    }

    // ---------------------------------------------------------------------------
    // Client search
    // ---------------------------------------------------------------------------
    let clienteQuery = $state('');
    let clienteResults = $state<ClienteResult[]>([]);
    let clienteLoading = $state(false);
    let showClienteDropdown = $state(false);
    let clienteDebounce: ReturnType<typeof setTimeout> | null = null;

    async function doClienteSearch(q: string): Promise<void> {
        if (!q.trim()) {
            clienteResults = [];
            showClienteDropdown = false;
            return;
        }
        clienteLoading = true;
        try {
            const res = await apiFetch(`/pos/buscar-cliente?q=${encodeURIComponent(q)}`);
            if (res.ok) {
                clienteResults = await res.json();
                showClienteDropdown = clienteResults.length > 0;
            }
        } catch {
            clienteResults = [];
            showClienteDropdown = false;
        } finally {
            clienteLoading = false;
        }
    }

    function onClienteInput(): void {
        if (clienteDebounce) clearTimeout(clienteDebounce);
        clienteDebounce = setTimeout(() => doClienteSearch(clienteQuery), 300);
    }

    function selectCliente(c: ClienteResult): void {
        carrito.cliente = c as unknown as Cliente;
        clienteQuery = c.nombre;
        showClienteDropdown = false;
        clienteResults = [];
        if (c.credito_activo && c.plazo_dias) {
            carrito.plazoDias = c.plazo_dias;
        }
    }

    function clearCliente(): void {
        carrito.cliente = null;
        clienteQuery = '';
        showClienteDropdown = false;
    }

    function setConsumidorFinal(): void {
        if (cliente_consumidor_final) {
            carrito.cliente = cliente_consumidor_final as unknown as Cliente;
            clienteQuery = cliente_consumidor_final.nombre;
        } else {
            carrito.cliente = null;
            clienteQuery = 'Consumidor Final';
        }
        showClienteDropdown = false;
    }

    const clienteActivo = $derived(carrito.cliente as unknown as ClienteResult | null);

    // ---------------------------------------------------------------------------
    // Payment section — medio pago selection (right panel, contado)
    // ---------------------------------------------------------------------------
    let selectedMedioId = $state<number | null>(null);
    let pagoMonto = $state('');
    let pagoReferencia = $state('');

    const mediosDisponibles = $derived(
        medios_pago.filter((m) => !carrito.pagos.some((p) => p.medio_pago_id === m.id)),
    );

    function agregarPago(): void {
        if (!selectedMedioId || !pagoMonto) return;
        const medio = medios_pago.find((m) => m.id === selectedMedioId);
        if (!medio) return;
        const monto = parseFloat(pagoMonto);
        if (isNaN(monto) || monto <= 0) return;
        carrito.agregarPago({
            medio_pago_id: medio.id,
            nombre: medio.nombre,
            tipo: medio.tipo,
            monto,
            referencia: pagoReferencia,
        });
        selectedMedioId = null;
        pagoMonto = '';
        pagoReferencia = '';
    }

    function preseleccionarMonto(medioId: number): void {
        selectedMedioId = medioId;
        // Prefill remaining amount
        const restante = Math.max(0, carrito.total - carrito.totalPagado);
        pagoMonto = restante > 0 ? String(Math.round(restante)) : '';
    }

    // ---------------------------------------------------------------------------
    // Confirmation dialog
    // ---------------------------------------------------------------------------
    let dialogOpen = $state(false);
    let submitting = $state(false);
    let submitError = $state('');
    let successFactura = $state('');
    let showSuccessBanner = $state(false);

    const canConfirm = $derived(
        carrito.tipoPago === 'credito'
            ? carrito.puedeFacturar
            : carrito.puedeFacturar && carrito.pagos.length > 0 && carrito.faltante <= 0,
    );

    function openDialog(): void {
        if (!carrito.puedeFacturar) return;
        submitError = '';
        dialogOpen = true;
    }

    async function confirmarVenta(): Promise<void> {
        if (!turno_activo) {
            submitError = 'No hay un turno de caja activo.';
            return;
        }
        if (!carrito.cliente) {
            submitError = 'Seleccione un cliente.';
            return;
        }
        if (carrito.tipoPago === 'contado' && carrito.faltante > 0) {
            submitError = 'El monto pagado es insuficiente.';
            return;
        }

        submitting = true;
        submitError = '';

        const payload = {
            turno_caja_id: turno_activo.id,
            cliente_id: (carrito.cliente as unknown as ClienteResult).id,
            tipo_pago: carrito.tipoPago,
            descuento_global: carrito.descuentoGlobal,
            ...(carrito.tipoPago === 'credito' ? { plazo_dias: carrito.plazoDias } : {}),
            ...(carrito.observaciones ? { observaciones: carrito.observaciones } : {}),
            items: carrito.items.map((i) => ({
                producto_id: i.producto_id,
                cantidad: i.cantidad,
                precio_unitario: i.precio_unitario,
                descuento_pct: i.descuento_pct,
            })),
            pagos:
                carrito.tipoPago === 'contado'
                    ? carrito.pagos.map((p) => ({
                          medio_pago_id: p.medio_pago_id,
                          monto: p.monto,
                          ...(p.referencia ? { referencia: p.referencia } : {}),
                      }))
                    : [],
        };

        try {
            const res = await apiFetch('/pos/ventas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });

            if (res.status === 419) {
                submitError =
                    'Su sesión ha expirado. Recargue la página para continuar (la venta no fue registrada).';
                return;
            }

            const data = await res.json();

            if (!res.ok || !data.success) {
                submitError = data.message ?? 'Error al procesar la venta.';
                return;
            }

            successFactura = data.numero_completo;
            dialogOpen = false;
            showSuccessBanner = true;
            carrito.limpiar();
            setConsumidorFinal();
            focusSearch();

            // Auto-dismiss after 8 s
            setTimeout(() => {
                showSuccessBanner = false;
            }, 8000);
        } catch {
            submitError = 'Error de conexión. Intente nuevamente.';
        } finally {
            submitting = false;
        }
    }

    // ---------------------------------------------------------------------------
    // IVA breakdown for summary
    // ---------------------------------------------------------------------------
    const iva5 = $derived(
        carrito.items
            .filter((i) => i.tarifa_tipo === 'iva' && i.tarifa_porcentaje === 5)
            .reduce((s, i) => s + i.iva, 0),
    );
    const iva19 = $derived(
        carrito.items
            .filter((i) => i.tarifa_tipo === 'iva' && i.tarifa_porcentaje === 19)
            .reduce((s, i) => s + i.iva, 0),
    );
    const otroIva = $derived(
        carrito.items
            .filter(
                (i) =>
                    i.tarifa_tipo === 'iva' &&
                    i.tarifa_porcentaje !== 5 &&
                    i.tarifa_porcentaje !== 19,
            )
            .reduce((s, i) => s + i.iva, 0),
    );
</script>

<AppLayout>
    <!-- Full-height POS shell — fills the content area provided by AppLayout -->
    <div class="flex h-[calc(100svh-4rem)] flex-col overflow-hidden bg-muted/30">

        <!-- ===================================================================
             ALERT BANNERS
        =================================================================== -->
        {#if !resolucion_activa || !turno_activo}
            <div class="flex shrink-0 flex-col gap-1 border-b bg-destructive/10 px-4 py-2">
                {#if !resolucion_activa}
                    <div class="flex items-center gap-2 text-sm text-destructive">
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>
                            No hay una resolución DIAN activa. No se pueden emitir facturas electrónicas.
                        </span>
                    </div>
                {/if}
                {#if !turno_activo}
                    <div class="flex items-center gap-2 text-sm text-destructive">
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>
                            No hay un turno de caja abierto.
                            <a href="/caja/turnos/abrir" class="underline font-medium">Abrir turno</a>
                        </span>
                    </div>
                {/if}
            </div>
        {/if}

        <!-- ===================================================================
             MAIN TWO-COLUMN LAYOUT
        =================================================================== -->
        <div class="flex flex-1 overflow-hidden">

            <!-- ================================================================
                 LEFT PANEL — 65% — Product search + cart table
            ================================================================ -->
            <div class="flex w-[65%] flex-col overflow-hidden border-r bg-background">

                <!-- Product search bar -->
                <div class="shrink-0 border-b px-4 py-3">
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <input
                            bind:this={searchInputEl}
                            bind:value={searchQuery}
                            oninput={onSearchInput}
                            onblur={() => setTimeout(() => { showProductDropdown = false; }, 180)}
                            onfocus={() => { if (searchQuery.trim()) showProductDropdown = productResults.length > 0; }}
                            type="text"
                            placeholder="Buscar por código, código de barras o nombre…"
                            autocomplete="off"
                            class="h-10 w-full rounded-md border bg-background pl-9 pr-4 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        />
                        {#if searchLoading}
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">
                                Buscando…
                            </span>
                        {:else if searchQuery}
                            <button
                                type="button"
                                onclick={() => { searchQuery = ''; productResults = []; showProductDropdown = false; focusSearch(); }}
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X class="h-4 w-4" />
                            </button>
                        {/if}

                        <!-- Product dropdown results -->
                        {#if showProductDropdown && productResults.length > 0}
                            <div class="absolute left-0 top-full z-50 mt-1 max-h-72 w-full overflow-y-auto rounded-md border bg-popover shadow-lg">
                                {#each productResults as p (p.id)}
                                    <button
                                        type="button"
                                        onmousedown={() => selectProduct(p)}
                                        class="flex w-full items-center gap-3 px-3 py-2 text-left text-sm hover:bg-accent"
                                    >
                                        <Package class="h-4 w-4 shrink-0 text-muted-foreground" />
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate font-medium">{p.nombre}</p>
                                            <p class="text-xs text-muted-foreground">{p.codigo}{p.codigo_barras ? ` · ${p.codigo_barras}` : ''}</p>
                                        </div>
                                        <div class="shrink-0 text-right">
                                            <p class="font-semibold text-primary">{formatCOP(p.precio_venta)}</p>
                                            <p class="text-xs text-muted-foreground">Stock: {p.stock_actual}</p>
                                        </div>
                                    </button>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Cart table -->
                <div class="flex-1 overflow-auto">
                    {#if carrito.items.length === 0}
                        <div class="flex h-full flex-col items-center justify-center gap-3 text-muted-foreground">
                            <ShoppingCart class="h-14 w-14 opacity-20" />
                            <p class="text-sm">El carrito está vacío</p>
                            <p class="text-xs">Busca productos arriba o escanea un código de barras</p>
                        </div>
                    {:else}
                        <table class="w-full text-sm">
                            <thead class="sticky top-0 border-b bg-muted/50 text-xs text-muted-foreground">
                                <tr>
                                    <th class="px-3 py-2 text-left font-medium">Producto</th>
                                    <th class="w-32 px-2 py-2 text-center font-medium">Cantidad</th>
                                    <th class="w-28 px-2 py-2 text-right font-medium">Precio unit.</th>
                                    <th class="w-20 px-2 py-2 text-center font-medium">Desc %</th>
                                    <th class="w-24 px-2 py-2 text-right font-medium">Subtotal</th>
                                    <th class="w-20 px-2 py-2 text-right font-medium">IVA</th>
                                    <th class="w-24 px-2 py-2 text-right font-medium">Total</th>
                                    <th class="w-10 px-2 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                {#each carrito.items as item, idx (item.producto_id)}
                                    <tr class="group hover:bg-muted/30">
                                        <td class="px-3 py-2">
                                            <p class="font-medium leading-snug">{item.nombre}</p>
                                            <p class="text-xs text-muted-foreground">{item.codigo}</p>
                                        </td>

                                        <!-- Quantity -->
                                        <td class="px-2 py-2">
                                            <div class="flex items-center justify-center gap-1">
                                                <button
                                                    type="button"
                                                    onclick={() => carrito.actualizarCantidad(idx, item.cantidad - 1)}
                                                    class="flex h-6 w-6 items-center justify-center rounded border text-muted-foreground hover:bg-muted"
                                                >
                                                    <Minus class="h-3 w-3" />
                                                </button>
                                                <input
                                                    type="number"
                                                    value={item.cantidad}
                                                    onchange={(e) => {
                                                        const v = parseFloat((e.target as HTMLInputElement).value);
                                                        if (!isNaN(v)) carrito.actualizarCantidad(idx, v);
                                                    }}
                                                    min="0.001"
                                                    step="1"
                                                    class="h-6 w-14 rounded border bg-background text-center text-xs focus:outline-none focus:ring-1 focus:ring-ring"
                                                />
                                                <button
                                                    type="button"
                                                    onclick={() => carrito.actualizarCantidad(idx, item.cantidad + 1)}
                                                    class="flex h-6 w-6 items-center justify-center rounded border text-muted-foreground hover:bg-muted"
                                                >
                                                    <Plus class="h-3 w-3" />
                                                </button>
                                            </div>
                                        </td>

                                        <!-- Unit price -->
                                        <td class="px-2 py-2">
                                            <input
                                                type="number"
                                                value={item.precio_unitario}
                                                onchange={(e) => {
                                                    const v = parseFloat((e.target as HTMLInputElement).value);
                                                    if (!isNaN(v) && v >= 0) carrito.actualizarPrecio(idx, v);
                                                }}
                                                min="0"
                                                class="h-6 w-full rounded border bg-background px-1 text-right text-xs focus:outline-none focus:ring-1 focus:ring-ring"
                                            />
                                        </td>

                                        <!-- Discount -->
                                        <td class="px-2 py-2">
                                            <div class="flex items-center gap-0.5">
                                                <input
                                                    type="number"
                                                    value={item.descuento_pct}
                                                    onchange={(e) => {
                                                        const v = parseFloat((e.target as HTMLInputElement).value);
                                                        if (!isNaN(v)) carrito.actualizarDescuento(idx, Math.min(100, Math.max(0, v)));
                                                    }}
                                                    min="0"
                                                    max="100"
                                                    class="h-6 w-12 rounded border bg-background px-1 text-right text-xs focus:outline-none focus:ring-1 focus:ring-ring"
                                                />
                                                <span class="text-xs text-muted-foreground">%</span>
                                            </div>
                                        </td>

                                        <!-- Subtotal -->
                                        <td class="px-2 py-2 text-right text-xs">{formatCOP(item.subtotal)}</td>

                                        <!-- IVA -->
                                        <td class="px-2 py-2 text-right text-xs text-muted-foreground">
                                            {#if item.iva > 0}
                                                {formatCOP(item.iva)}
                                                <div class="text-xs text-muted-foreground/60">{item.tarifa_porcentaje}%</div>
                                            {:else}
                                                <span class="text-muted-foreground/40">—</span>
                                            {/if}
                                        </td>

                                        <!-- Total -->
                                        <td class="px-2 py-2 text-right font-semibold">{formatCOP(item.total)}</td>

                                        <!-- Remove -->
                                        <td class="px-2 py-2 text-center">
                                            <button
                                                type="button"
                                                onclick={() => carrito.quitarItem(idx)}
                                                class="text-muted-foreground/40 hover:text-destructive"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                            </button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    {/if}
                </div>

                <!-- Bottom strip — global discount -->
                <div class="shrink-0 border-t bg-muted/20 px-4 py-2">
                    <div class="flex items-center gap-3">
                        <Label for="descuento-global" class="text-sm text-muted-foreground whitespace-nowrap">
                            Descuento global ($)
                        </Label>
                        <Input
                            id="descuento-global"
                            type="number"
                            value={carrito.descuentoGlobal}
                            onchange={(e: Event) => {
                                const v = parseFloat((e.target as HTMLInputElement).value);
                                carrito.descuentoGlobal = isNaN(v) ? 0 : Math.max(0, v);
                            }}
                            min="0"
                            class="h-8 w-36 text-right text-sm"
                            placeholder="0"
                        />
                        {#if carrito.items.length > 0}
                            <button
                                type="button"
                                onclick={() => carrito.limpiar()}
                                class="ml-auto text-xs text-destructive hover:underline"
                            >
                                Limpiar carrito
                            </button>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- ================================================================
                 RIGHT PANEL — 35% — Client, summary, payments, cobrar
            ================================================================ -->
            <div class="flex w-[35%] flex-col overflow-hidden bg-background">
                <div class="flex flex-1 flex-col gap-0 overflow-y-auto">

                    <!-- --------------------------------------------------------
                         Client selector
                    -------------------------------------------------------- -->
                    <div class="border-b px-4 py-3">
                        <p class="mb-1.5 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            <User class="h-3.5 w-3.5" />
                            Cliente
                        </p>

                        {#if carrito.cliente}
                            <div class="flex items-center gap-2">
                                <Badge variant="secondary" class="flex-1 justify-start gap-1.5 py-1 text-sm font-medium">
                                    <User class="h-3.5 w-3.5 shrink-0" />
                                    <span class="truncate">{clienteActivo?.nombre}</span>
                                </Badge>
                                {#if clienteActivo?.credito_activo}
                                    <Badge variant="outline" class="shrink-0 text-xs text-emerald-600">
                                        Crédito
                                    </Badge>
                                {/if}
                                <button
                                    type="button"
                                    onclick={clearCliente}
                                    class="shrink-0 text-muted-foreground hover:text-foreground"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        {:else}
                            <div class="relative">
                                <Input
                                    bind:value={clienteQuery}
                                    oninput={onClienteInput}
                                    onfocus={() => { if (clienteQuery.trim()) showClienteDropdown = clienteResults.length > 0; }}
                                    onblur={() => setTimeout(() => { showClienteDropdown = false; }, 180)}
                                    placeholder="Buscar cliente por nombre o NIT…"
                                    class="h-9 text-sm"
                                />
                                {#if clienteLoading}
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">…</span>
                                {/if}

                                {#if showClienteDropdown && clienteResults.length > 0}
                                    <div class="absolute left-0 top-full z-50 mt-1 max-h-52 w-full overflow-y-auto rounded-md border bg-popover shadow-lg">
                                        {#each clienteResults as c (c.id)}
                                            <button
                                                type="button"
                                                onmousedown={() => selectCliente(c)}
                                                class="flex w-full flex-col px-3 py-2 text-left hover:bg-accent"
                                            >
                                                <span class="text-sm font-medium">{c.nombre}</span>
                                                <span class="text-xs text-muted-foreground">{c.identificacion}</span>
                                                {#if c.credito_activo}
                                                    <Badge variant="outline" class="mt-0.5 w-fit text-xs text-emerald-600">
                                                        Crédito {formatCOP(c.limite_credito)}
                                                    </Badge>
                                                {/if}
                                            </button>
                                        {/each}
                                    </div>
                                {/if}
                            </div>
                        {/if}

                        <button
                            type="button"
                            onclick={setConsumidorFinal}
                            class="mt-1.5 text-xs text-muted-foreground underline-offset-2 hover:underline"
                        >
                            Usar Consumidor Final
                        </button>
                    </div>

                    <!-- --------------------------------------------------------
                         Cart summary
                    -------------------------------------------------------- -->
                    <div class="border-b px-4 py-3">
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between text-muted-foreground">
                                <span>Subtotal</span>
                                <span>{formatCOP(carrito.subtotal)}</span>
                            </div>
                            {#if iva5 > 0}
                                <div class="flex justify-between text-muted-foreground">
                                    <span>IVA 5%</span>
                                    <span>{formatCOP(iva5)}</span>
                                </div>
                            {/if}
                            {#if iva19 > 0}
                                <div class="flex justify-between text-muted-foreground">
                                    <span>IVA 19%</span>
                                    <span>{formatCOP(iva19)}</span>
                                </div>
                            {/if}
                            {#if otroIva > 0}
                                <div class="flex justify-between text-muted-foreground">
                                    <span>Otros IVA</span>
                                    <span>{formatCOP(otroIva)}</span>
                                </div>
                            {/if}
                            {#if carrito.descuentoGlobal > 0}
                                <div class="flex justify-between text-orange-600">
                                    <span>Descuento global</span>
                                    <span>- {formatCOP(carrito.descuentoGlobal)}</span>
                                </div>
                            {/if}
                        </div>
                        <Separator class="my-2" />
                        <div class="flex items-baseline justify-between">
                            <span class="text-base font-bold">TOTAL</span>
                            <span class="text-2xl font-bold text-primary">{formatCOP(carrito.total)}</span>
                        </div>
                    </div>

                    <!-- --------------------------------------------------------
                         Tipo de pago
                    -------------------------------------------------------- -->
                    <div class="border-b px-4 py-3">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Tipo de pago
                        </p>
                        <div class="flex gap-2">
                            <button
                                type="button"
                                onclick={() => { carrito.tipoPago = 'contado'; }}
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-md border py-2 text-sm font-medium transition-colors
                                    {carrito.tipoPago === 'contado'
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border bg-background text-muted-foreground hover:bg-muted'}"
                            >
                                <Banknote class="h-4 w-4" />
                                Contado
                            </button>
                            <button
                                type="button"
                                onclick={() => { carrito.tipoPago = 'credito'; }}
                                disabled={!clienteActivo?.credito_activo}
                                class="flex flex-1 items-center justify-center gap-1.5 rounded-md border py-2 text-sm font-medium transition-colors
                                    {carrito.tipoPago === 'credito'
                                        ? 'border-purple-500 bg-purple-500 text-white'
                                        : 'border-border bg-background text-muted-foreground hover:bg-muted'}
                                    disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <CreditCard class="h-4 w-4" />
                                Crédito
                            </button>
                        </div>

                        {#if carrito.tipoPago === 'credito'}
                            <div class="mt-2 flex items-center gap-2">
                                <Label for="plazo-dias" class="shrink-0 text-xs text-muted-foreground">Plazo (días)</Label>
                                <Input
                                    id="plazo-dias"
                                    type="number"
                                    value={carrito.plazoDias}
                                    onchange={(e: Event) => {
                                        const v = parseInt((e.target as HTMLInputElement).value);
                                        carrito.plazoDias = isNaN(v) ? 30 : Math.max(1, v);
                                    }}
                                    min="1"
                                    class="h-8 w-24 text-sm"
                                />
                            </div>
                        {/if}
                    </div>

                    <!-- --------------------------------------------------------
                         Pagos (only for contado)
                    -------------------------------------------------------- -->
                    {#if carrito.tipoPago === 'contado'}
                        <div class="border-b px-4 py-3">
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                Medios de pago
                            </p>

                            <!-- Existing pagos -->
                            {#if carrito.pagos.length > 0}
                                <div class="mb-2 space-y-1.5">
                                    {#each carrito.pagos as pago, idx (pago.medio_pago_id)}
                                        <div class="flex items-center gap-2 rounded-md border bg-muted/30 px-3 py-1.5 text-sm">
                                            <span class="flex-1 font-medium">{pago.nombre}</span>
                                            {#if pago.referencia}
                                                <span class="truncate text-xs text-muted-foreground max-w-[6rem]">{pago.referencia}</span>
                                            {/if}
                                            <span class="font-semibold">{formatCOP(pago.monto)}</span>
                                            <button
                                                type="button"
                                                onclick={() => carrito.quitarPago(idx)}
                                                class="text-muted-foreground hover:text-destructive"
                                            >
                                                <X class="h-3.5 w-3.5" />
                                            </button>
                                        </div>
                                    {/each}
                                </div>
                            {/if}

                            <!-- Medio pago selector cards -->
                            {#if mediosDisponibles.length > 0}
                                <div class="mb-2 flex flex-wrap gap-1.5">
                                    {#each mediosDisponibles as mp (mp.id)}
                                        <button
                                            type="button"
                                            onclick={() => preseleccionarMonto(mp.id)}
                                            class="rounded-md border px-2.5 py-1 text-xs font-medium transition-colors
                                                {selectedMedioId === mp.id
                                                    ? 'border-primary bg-primary text-primary-foreground'
                                                    : 'border-border bg-background hover:bg-muted'}"
                                        >
                                            {mp.nombre}
                                        </button>
                                    {/each}
                                </div>

                                {#if selectedMedioId !== null}
                                    <div class="flex items-center gap-2">
                                        <Input
                                            type="number"
                                            bind:value={pagoMonto}
                                            placeholder="Monto"
                                            min="0"
                                            class="h-8 flex-1 text-sm"
                                        />
                                        <Input
                                            type="text"
                                            bind:value={pagoReferencia}
                                            placeholder="Ref. (opcional)"
                                            class="h-8 flex-1 text-sm"
                                        />
                                        <Button
                                            type="button"
                                            onclick={agregarPago}
                                            disabled={!pagoMonto}
                                            size="sm"
                                            class="shrink-0"
                                        >
                                            <Plus class="h-3.5 w-3.5" />
                                        </Button>
                                    </div>
                                {/if}
                            {/if}

                            <!-- Totals bar -->
                            {#if carrito.pagos.length > 0 || carrito.total > 0}
                                <div class="mt-2 space-y-0.5 rounded-md bg-muted/40 px-3 py-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-muted-foreground">Pagado</span>
                                        <span class="font-medium">{formatCOP(carrito.totalPagado)}</span>
                                    </div>
                                    {#if carrito.faltante > 0}
                                        <div class="flex justify-between text-destructive">
                                            <span>Faltante</span>
                                            <span class="font-bold">{formatCOP(carrito.faltante)}</span>
                                        </div>
                                    {/if}
                                    {#if carrito.cambio > 0}
                                        <div class="flex justify-between text-emerald-600">
                                            <span>Cambio</span>
                                            <span class="font-bold">{formatCOP(carrito.cambio)}</span>
                                        </div>
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <!-- --------------------------------------------------------
                         Observaciones
                    -------------------------------------------------------- -->
                    <div class="border-b px-4 py-3">
                        <Label for="observaciones" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Observaciones
                        </Label>
                        <textarea
                            id="observaciones"
                            bind:value={carrito.observaciones}
                            rows={2}
                            placeholder="Observaciones opcionales…"
                            class="w-full resize-none rounded-md border bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring"
                        ></textarea>
                    </div>
                </div>

                <!-- ============================================================
                     COBRAR button — sticky at bottom
                ============================================================ -->
                <div class="shrink-0 border-t bg-background px-4 py-3">
                    <Button
                        onclick={openDialog}
                        disabled={!carrito.puedeFacturar || !resolucion_activa || !turno_activo}
                        class="h-14 w-full bg-emerald-600 text-lg font-bold hover:bg-emerald-700 disabled:opacity-40"
                    >
                        <ShoppingCart class="mr-2 h-5 w-5" />
                        COBRAR
                        {#if carrito.items.length > 0}
                            <span class="ml-2">{formatCOP(carrito.total)}</span>
                        {/if}
                    </Button>

                    {#if turno_activo}
                        <p class="mt-1 text-center text-xs text-muted-foreground">
                            Caja: {turno_activo.caja.nombre}
                        </p>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</AppLayout>

<!-- =============================================================================
     CONFIRMATION DIALOG
============================================================================= -->
<Dialog bind:open={dialogOpen}>
    <DialogContent class="max-w-md">
        <DialogHeader>
            <DialogTitle>Confirmar venta</DialogTitle>
        </DialogHeader>

        <!-- Summary table inside dialog -->
        <div class="space-y-1 rounded-md bg-muted/40 px-4 py-3 text-sm">
            <div class="flex justify-between text-muted-foreground">
                <span>Cliente</span>
                <span class="font-medium text-foreground">{clienteActivo?.nombre ?? '—'}</span>
            </div>
            <div class="flex justify-between text-muted-foreground">
                <span>Subtotal</span>
                <span>{formatCOP(carrito.subtotal)}</span>
            </div>
            {#if carrito.totalIva > 0}
                <div class="flex justify-between text-muted-foreground">
                    <span>IVA total</span>
                    <span>{formatCOP(carrito.totalIva)}</span>
                </div>
            {/if}
            {#if carrito.descuentoGlobal > 0}
                <div class="flex justify-between text-orange-600">
                    <span>Descuento global</span>
                    <span>- {formatCOP(carrito.descuentoGlobal)}</span>
                </div>
            {/if}
            <Separator class="my-1" />
            <div class="flex justify-between text-base font-bold">
                <span>TOTAL</span>
                <span class="text-primary">{formatCOP(carrito.total)}</span>
            </div>
        </div>

        {#if carrito.tipoPago === 'contado'}
            <div class="space-y-1 rounded-md border px-4 py-3 text-sm">
                {#each carrito.pagos as pago (pago.medio_pago_id)}
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">{pago.nombre}</span>
                        <span>{formatCOP(pago.monto)}</span>
                    </div>
                {/each}
                <Separator class="my-1" />
                {#if carrito.cambio > 0}
                    <div class="flex justify-between font-semibold text-emerald-600">
                        <span>Cambio</span>
                        <span>{formatCOP(carrito.cambio)}</span>
                    </div>
                {/if}
                {#if carrito.faltante > 0}
                    <div class="flex justify-between font-semibold text-destructive">
                        <span>Faltante</span>
                        <span>{formatCOP(carrito.faltante)}</span>
                    </div>
                {/if}
            </div>
        {:else}
            <div class="rounded-md bg-purple-50 px-4 py-3 text-sm dark:bg-purple-950">
                <p class="font-medium text-purple-800 dark:text-purple-200">Venta a crédito</p>
                <p class="mt-0.5 text-purple-600 dark:text-purple-400">
                    Se generará una cuenta por cobrar de {formatCOP(carrito.total)} con
                    vencimiento en {carrito.plazoDias} día{carrito.plazoDias !== 1 ? 's' : ''}.
                </p>
            </div>
        {/if}

        {#if submitError}
            <div class="flex items-center gap-2 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                <AlertCircle class="h-4 w-4 shrink-0" />
                {submitError}
            </div>
        {/if}

        <DialogFooter class="gap-2">
            <DialogClose>
                <Button variant="outline" disabled={submitting}>Cancelar</Button>
            </DialogClose>
            <Button
                onclick={confirmarVenta}
                disabled={!canConfirm || submitting}
                class="flex-1 bg-emerald-600 hover:bg-emerald-700"
            >
                {#if submitting}
                    Procesando…
                {:else}
                    <CheckCircle2 class="mr-2 h-4 w-4" />
                    Confirmar y emitir factura
                {/if}
            </Button>
        </DialogFooter>
    </DialogContent>
</Dialog>

<!-- =============================================================================
     SUCCESS BANNER
============================================================================= -->
{#if showSuccessBanner}
    <div class="fixed bottom-6 right-6 z-50 flex w-80 items-start gap-3 rounded-xl border border-emerald-200 bg-background p-4 shadow-2xl dark:border-emerald-800">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900">
            <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold">Venta registrada exitosamente</p>
            <p class="mt-0.5 text-sm text-muted-foreground">
                Factura: <span class="font-mono font-bold text-foreground">{successFactura}</span>
            </p>
        </div>
        <button
            type="button"
            onclick={() => { showSuccessBanner = false; }}
            class="shrink-0 text-muted-foreground hover:text-foreground"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
{/if}
