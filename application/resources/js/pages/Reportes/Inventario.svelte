<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import BarChart2 from 'lucide-svelte/icons/bar-chart-2';
    import Download from 'lucide-svelte/icons/download';
    import Package from 'lucide-svelte/icons/package';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { formatCOP } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface Producto {
        id: number;
        codigo: string;
        nombre: string;
        stock_actual: number;
        stock_minimo: number;
        stock_maximo: number;
        precio_compra: number;
        precio_venta: number;
        categoria: string | null;
        tarifa_iva: number | null;
        activo: boolean;
    }

    interface KardexMovimiento {
        fecha: string;
        tipo: string;
        referencia_tipo: string | null;
        referencia_id: number | null;
        cantidad: number;
        stock_anterior: number;
        stock_nuevo: number;
        costo_unitario: number;
        observaciones: string | null;
    }

    interface Kardex {
        producto: Producto;
        movimientos: KardexMovimiento[];
    }

    interface Filtros {
        producto_id: number | null;
        desde: string;
        hasta: string;
    }

    interface Props {
        productos: Producto[];
        kardex: Kardex | null;
        filtros: Filtros;
    }

    let { productos, kardex, filtros }: Props = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'Inventario', href: '/reportes/inventario' },
    ];

    let busqueda = $state('');
    let soloActivos = $state(true);
    let productoId = $state<number | ''>(untrack(() => filtros.producto_id ?? ''));
    let desde = $state(untrack(() => filtros.desde));
    let hasta = $state(untrack(() => filtros.hasta));

    const productosFiltrados = $derived(
        productos.filter((p) => {
            if (soloActivos && !p.activo) return false;
            if (!busqueda) return true;
            const q = busqueda.toLowerCase();
            return (
                p.nombre.toLowerCase().includes(q) ||
                p.codigo.toLowerCase().includes(q) ||
                (p.categoria?.toLowerCase().includes(q) ?? false)
            );
        }),
    );

    // Group by category
    const porCategoria = $derived(() => {
        const map = new Map<string, Producto[]>();
        for (const p of productosFiltrados) {
            const cat = p.categoria ?? 'Sin categoría';
            if (!map.has(cat)) map.set(cat, []);
            map.get(cat)!.push(p);
        }
        return map;
    });

    const totalValorizado = $derived(
        productosFiltrados.reduce((s, p) => s + p.stock_actual * p.precio_compra, 0),
    );

    const totalProductos = $derived(productosFiltrados.length);
    const productosBajoStock = $derived(
        productosFiltrados.filter((p) => p.activo && p.stock_actual <= p.stock_minimo).length,
    );

    function verKardex() {
        if (!productoId) return;
        router.get(
            '/reportes/inventario',
            { producto_id: productoId, desde, hasta },
            { preserveState: true },
        );
    }

    function limpiarKardex() {
        productoId = '';
        router.get('/reportes/inventario', {}, { preserveState: false });
    }

    function exportarCSV() {
        const headers = ['Código', 'Nombre', 'Categoría', 'Stock Actual', 'Stock Mín', 'Precio Compra', 'Precio Venta', 'Valor Total', 'Activo'];
        const rows = productosFiltrados.map((p) => [
            p.codigo,
            p.nombre,
            p.categoria ?? 'Sin categoría',
            p.stock_actual,
            p.stock_minimo,
            p.precio_compra,
            p.precio_venta,
            (p.stock_actual * p.precio_compra).toFixed(2),
            p.activo ? 'Sí' : 'No',
        ]);

        const csv = [headers, ...rows].map((r) => r.map((c) => `"${c}"`).join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `inventario_${new Date().toISOString().slice(0, 10)}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }

    function tipoMovimientoLabel(tipo: string): string {
        const labels: Record<string, string> = {
            entrada: 'Entrada',
            salida: 'Salida',
            ajuste_positivo: 'Ajuste +',
            ajuste_negativo: 'Ajuste −',
            factura: 'Venta',
            anulacion_factura: 'Anulación',
            recepcion_compra: 'Compra',
        };
        return labels[tipo] ?? tipo;
    }

    function tipoColor(tipo: string): string {
        if (['entrada', 'ajuste_positivo', 'recepcion_compra', 'anulacion_factura'].includes(tipo)) {
            return 'text-green-700 dark:text-green-400';
        }
        return 'text-red-700 dark:text-red-400';
    }
</script>

<AppHead title="Reporte de Inventario" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-4 md:p-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-foreground">Reporte de Inventario</h1>
                <p class="text-sm text-muted-foreground">Valorización y kardex de productos</p>
            </div>
            <button
                type="button"
                onclick={exportarCSV}
                class="inline-flex items-center gap-2 rounded-md border border-input bg-background px-4 py-2 text-sm font-medium shadow-sm hover:bg-accent"
            >
                <Download class="size-4" />
                Exportar CSV
            </button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Total Valorizado</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{formatCOP(totalValorizado)}</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">{totalProductos} productos</p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-2.5 dark:bg-blue-900/30">
                        <BarChart2 class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Productos Activos</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">
                            {productos.filter((p) => p.activo).length}
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            de {productos.length} totales
                        </p>
                    </div>
                    <div class="rounded-lg bg-green-100 p-2.5 dark:bg-green-900/30">
                        <Package class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">Bajo Stock Mínimo</p>
                        <p class="mt-1 text-2xl font-bold {productosBajoStock > 0 ? 'text-red-600 dark:text-red-400' : 'text-foreground'}">
                            {productosBajoStock}
                        </p>
                        <p class="mt-0.5 text-xs text-muted-foreground">productos con alerta</p>
                    </div>
                    <div class="rounded-lg p-2.5 {productosBajoStock > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-800'}">
                        <Package class="size-5 {productosBajoStock > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500'}" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Kardex selector -->
        <div class="rounded-xl border border-sidebar-border/70 bg-card p-5">
            <h2 class="mb-3 text-sm font-semibold text-foreground">Kardex por Producto</h2>
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground" for="producto-select">Producto</label>
                    <select
                        id="producto-select"
                        bind:value={productoId}
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    >
                        <option value="">-- Seleccionar --</option>
                        {#each productos as p (p.id)}
                            <option value={p.id}>{p.codigo} — {p.nombre}</option>
                        {/each}
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground" for="kardex-desde">Desde</label>
                    <input
                        id="kardex-desde"
                        type="date"
                        bind:value={desde}
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground" for="kardex-hasta">Hasta</label>
                    <input
                        id="kardex-hasta"
                        type="date"
                        bind:value={hasta}
                        class="h-9 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                    />
                </div>
                <button
                    type="button"
                    onclick={verKardex}
                    disabled={!productoId}
                    class="h-9 rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    Ver Kardex
                </button>
                {#if kardex}
                    <button
                        type="button"
                        onclick={limpiarKardex}
                        class="h-9 rounded-md border border-input bg-background px-4 text-sm hover:bg-accent"
                    >
                        Limpiar
                    </button>
                {/if}
            </div>
        </div>

        <!-- Kardex table -->
        {#if kardex}
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5">
                <h2 class="mb-1 text-sm font-semibold text-foreground">
                    Kardex: {kardex.producto.nombre}
                </h2>
                <p class="mb-4 text-xs text-muted-foreground">
                    Código: {kardex.producto.codigo} · Stock actual: {kardex.producto.stock_actual}
                </p>

                {#if kardex.movimientos.length === 0}
                    <p class="py-6 text-center text-sm text-muted-foreground">Sin movimientos en el período</p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-sidebar-border/50">
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Fecha</th>
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Tipo</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Cantidad</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Stock Ant.</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Stock Nuevo</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Costo Unit.</th>
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each kardex.movimientos as mov, i (i)}
                                    <tr class="border-b border-sidebar-border/30 last:border-0">
                                        <td class="py-2 pr-3 text-xs text-muted-foreground">{mov.fecha}</td>
                                        <td class="py-2 pr-3">
                                            <span class="text-xs font-medium {tipoColor(mov.tipo)}">
                                                {tipoMovimientoLabel(mov.tipo)}
                                            </span>
                                        </td>
                                        <td class="py-2 pr-3 text-right text-xs font-semibold {tipoColor(mov.tipo)}">
                                            {mov.cantidad > 0 ? '+' : ''}{mov.cantidad}
                                        </td>
                                        <td class="py-2 pr-3 text-right text-xs">{mov.stock_anterior}</td>
                                        <td class="py-2 pr-3 text-right text-xs font-medium text-foreground">{mov.stock_nuevo}</td>
                                        <td class="py-2 pr-3 text-right text-xs">{formatCOP(mov.costo_unitario)}</td>
                                        <td class="py-2 text-xs text-muted-foreground">{mov.observaciones ?? '—'}</td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>
        {/if}

        <!-- Inventory table -->
        <div class="rounded-xl border border-sidebar-border/70 bg-card p-5">
            <div class="mb-4 flex flex-wrap items-center gap-3">
                <h2 class="flex-1 text-sm font-semibold text-foreground">Inventario Valorizado</h2>
                <input
                    type="text"
                    placeholder="Buscar producto o categoría..."
                    bind:value={busqueda}
                    class="h-9 w-64 rounded-md border border-input bg-background px-3 text-sm focus:outline-none focus:ring-2 focus:ring-ring"
                />
                <label class="flex cursor-pointer items-center gap-2 text-sm">
                    <input type="checkbox" bind:checked={soloActivos} class="rounded border-input" />
                    Solo activos
                </label>
            </div>

            {#each [...porCategoria()] as [categoria, items] (categoria)}
                {@const subtotal = items.reduce((s, p) => s + p.stock_actual * p.precio_compra, 0)}
                <div class="mb-4">
                    <div class="mb-1 flex items-center justify-between rounded-md bg-muted/40 px-3 py-2">
                        <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            {categoria} ({items.length})
                        </span>
                        <span class="text-xs font-semibold text-foreground">{formatCOP(subtotal)}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-sidebar-border/30">
                                    <th class="py-1.5 pr-3 text-left text-xs font-medium text-muted-foreground">Código</th>
                                    <th class="py-1.5 pr-3 text-left text-xs font-medium text-muted-foreground">Nombre</th>
                                    <th class="py-1.5 pr-3 text-right text-xs font-medium text-muted-foreground">Stock</th>
                                    <th class="py-1.5 pr-3 text-right text-xs font-medium text-muted-foreground">Mín</th>
                                    <th class="py-1.5 pr-3 text-right text-xs font-medium text-muted-foreground">P. Compra</th>
                                    <th class="py-1.5 pr-3 text-right text-xs font-medium text-muted-foreground">P. Venta</th>
                                    <th class="py-1.5 text-right text-xs font-medium text-muted-foreground">Valor Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each items as p (p.id)}
                                    <tr class="border-b border-sidebar-border/20 last:border-0 {!p.activo ? 'opacity-50' : ''}">
                                        <td class="py-2 pr-3 text-xs font-mono text-muted-foreground">{p.codigo}</td>
                                        <td class="py-2 pr-3 text-sm text-foreground">
                                            {p.nombre}
                                            {#if p.activo && p.stock_actual <= p.stock_minimo}
                                                <span class="ml-1 inline-flex rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                                    bajo stock
                                                </span>
                                            {/if}
                                        </td>
                                        <td class="py-2 pr-3 text-right text-sm font-medium {p.activo && p.stock_actual <= p.stock_minimo ? 'text-red-600 dark:text-red-400' : 'text-foreground'}">
                                            {p.stock_actual.toLocaleString('es-CO')}
                                        </td>
                                        <td class="py-2 pr-3 text-right text-xs text-muted-foreground">{p.stock_minimo}</td>
                                        <td class="py-2 pr-3 text-right text-xs">{formatCOP(p.precio_compra)}</td>
                                        <td class="py-2 pr-3 text-right text-xs">{formatCOP(p.precio_venta)}</td>
                                        <td class="py-2 text-right text-xs font-semibold text-foreground">
                                            {formatCOP(p.stock_actual * p.precio_compra)}
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>
            {/each}

            <!-- Grand total -->
            <div class="mt-2 flex justify-end border-t border-sidebar-border/50 pt-3">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-semibold text-muted-foreground">Total valorizado:</span>
                    <span class="text-lg font-bold text-foreground">{formatCOP(totalValorizado)}</span>
                </div>
            </div>
        </div>

    </div>
</AppLayout>
