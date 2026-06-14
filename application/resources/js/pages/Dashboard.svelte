<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { Link } from '@inertiajs/svelte';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import BarChart2 from 'lucide-svelte/icons/bar-chart-2';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import DollarSign from 'lucide-svelte/icons/dollar-sign';
    import Info from 'lucide-svelte/icons/info';
    import ShoppingCart from 'lucide-svelte/icons/shopping-cart';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { formatCOP, formatFechaHora } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface VentasStats {
        total: number;
        count: number;
    }

    interface VentaDia {
        dia: string;
        total: number;
        fecha: string;
    }

    interface TopProducto {
        nombre: string;
        codigo: string;
        total_vendido: number;
        cantidad: number;
    }

    interface TurnoActivo {
        id: number;
        caja_id: number;
        apertura: string;
        estado: string;
        caja: { id: number; nombre: string } | null;
        cajero_nombre: string;
    }

    interface UltimaFactura {
        id: number;
        numero_completo: string;
        fecha: string;
        total: number;
        estado: string;
        cliente_nombre: string;
    }

    interface Props {
        ventas_hoy: VentasStats;
        ventas_mes: VentasStats;
        ventas_semana: VentaDia[];
        cartera_total: number;
        cartera_vencida: number;
        productos_bajo_stock: number;
        top_productos_mes: TopProducto[];
        turno_activo: TurnoActivo | null;
        ultimas_facturas: UltimaFactura[];
        gastos_mes: number;
    }

    let {
        ventas_hoy,
        ventas_mes,
        ventas_semana,
        cartera_total,
        cartera_vencida,
        productos_bajo_stock,
        top_productos_mes,
        turno_activo,
        ultimas_facturas,
        gastos_mes,
    }: Props = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
    ];

    const maxVentaSemana = $derived(
        Math.max(...ventas_semana.map((d) => d.total), 1),
    );

    const totalSemana = $derived(
        ventas_semana.reduce((acc, d) => acc + d.total, 0),
    );

    const hasAlerts = $derived(
        productos_bajo_stock > 0 ||
            cartera_vencida > 0 ||
            turno_activo === null,
    );

    function barHeight(total: number): string {
        if (total <= 0) return '4px';
        const pct = (total / maxVentaSemana) * 100;
        return `${Math.max(pct, 4)}%`;
    }

    function estadoBadgeClass(estado: string): string {
        switch (estado) {
            case 'emitida':
                return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300';
            case 'anulada':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300';
            case 'borrador':
                return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
            default:
                return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        }
    }

    function estadoLabel(estado: string): string {
        switch (estado) {
            case 'emitida':
                return 'Emitida';
            case 'anulada':
                return 'Anulada';
            case 'borrador':
                return 'Borrador';
            default:
                return estado;
        }
    }
</script>

<AppHead title="Dashboard" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-4 md:p-6">

        <!-- Row 1: KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <!-- Ventas Hoy -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Ventas Hoy</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{formatCOP(ventas_hoy.total)}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{ventas_hoy.count} {ventas_hoy.count === 1 ? 'factura' : 'facturas'}</p>
                    </div>
                    <div class="rounded-lg bg-green-100 p-2.5 dark:bg-green-900/30">
                        <TrendingUp class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            <!-- Ventas Este Mes -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Ventas Este Mes</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{formatCOP(ventas_mes.total)}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{ventas_mes.count} {ventas_mes.count === 1 ? 'factura' : 'facturas'}</p>
                    </div>
                    <div class="rounded-lg bg-blue-100 p-2.5 dark:bg-blue-900/30">
                        <BarChart2 class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            <!-- Cartera Vencida -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Cartera Vencida</p>
                        <p class="mt-1 text-2xl font-bold {cartera_vencida > 0 ? 'text-red-600 dark:text-red-400' : 'text-foreground'}">
                            {formatCOP(cartera_vencida)}
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">Total cartera: {formatCOP(cartera_total)}</p>
                    </div>
                    <div class="rounded-lg p-2.5 {cartera_vencida > 0 ? 'bg-red-100 dark:bg-red-900/30' : 'bg-gray-100 dark:bg-gray-800'}">
                        <CreditCard class="size-5 {cartera_vencida > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400'}" />
                    </div>
                </div>
            </div>

            <!-- Gastos del Mes -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-muted-foreground">Gastos del Mes</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{formatCOP(gastos_mes)}</p>
                        <p class="mt-1 text-xs text-muted-foreground">Gastos registrados</p>
                    </div>
                    <div class="rounded-lg bg-orange-100 p-2.5 dark:bg-orange-900/30">
                        <DollarSign class="size-5 text-orange-600 dark:text-orange-400" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Alerts bar -->
        {#if hasAlerts}
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                {#if productos_bajo_stock > 0}
                    <Link
                        href="/inventario/productos?bajo_stock=1"
                        class="flex items-center gap-2 rounded-lg border border-yellow-300 bg-yellow-50 px-4 py-2.5 text-sm font-medium text-yellow-800 transition-colors hover:bg-yellow-100 dark:border-yellow-700 dark:bg-yellow-900/20 dark:text-yellow-300 dark:hover:bg-yellow-900/30"
                    >
                        <AlertTriangle class="size-4 shrink-0" />
                        {productos_bajo_stock} {productos_bajo_stock === 1 ? 'producto con stock bajo' : 'productos con stock bajo'}
                    </Link>
                {/if}

                {#if cartera_vencida > 0}
                    <Link
                        href="/cartera?vencida=1"
                        class="flex items-center gap-2 rounded-lg border border-red-300 bg-red-50 px-4 py-2.5 text-sm font-medium text-red-800 transition-colors hover:bg-red-100 dark:border-red-700 dark:bg-red-900/20 dark:text-red-300 dark:hover:bg-red-900/30"
                    >
                        <AlertTriangle class="size-4 shrink-0" />
                        Cartera vencida: {formatCOP(cartera_vencida)}
                    </Link>
                {/if}

                {#if turno_activo === null}
                    <Link
                        href="/caja"
                        class="flex items-center gap-2 rounded-lg border border-blue-300 bg-blue-50 px-4 py-2.5 text-sm font-medium text-blue-800 transition-colors hover:bg-blue-100 dark:border-blue-700 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                    >
                        <Info class="size-4 shrink-0" />
                        No hay turno de caja abierto
                    </Link>
                {/if}
            </div>
        {/if}

        <!-- Row 3: Chart + Turno -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <!-- Weekly Sales Chart (60%) -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border lg:col-span-3">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-foreground">Ventas Últimos 7 Días</h3>
                        <p class="text-xs text-muted-foreground">Total: {formatCOP(totalSemana)}</p>
                    </div>
                    <ShoppingCart class="size-4 text-muted-foreground" />
                </div>
                <div class="flex h-40 items-end gap-1.5">
                    {#each ventas_semana as item (item.fecha)}
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <div
                                class="w-full rounded-t-sm bg-primary/80 transition-all hover:bg-primary"
                                style="height: {barHeight(item.total)}"
                                title="{item.dia}: {formatCOP(item.total)}"
                            ></div>
                            <span class="text-[10px] text-muted-foreground">{item.dia}</span>
                        </div>
                    {/each}
                </div>
            </div>

            <!-- Turno de Caja Card (40%) -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border lg:col-span-2">
                <h3 class="mb-4 text-sm font-semibold text-foreground">Turno de Caja</h3>

                {#if turno_activo}
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 rounded-full bg-green-500"></span>
                            <span class="text-sm font-medium text-green-700 dark:text-green-400">Turno activo</span>
                        </div>
                        <div class="space-y-2 rounded-lg bg-muted/40 p-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Caja</span>
                                <span class="font-medium text-foreground">{turno_activo.caja?.nombre ?? '-'}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Cajero</span>
                                <span class="font-medium text-foreground">{turno_activo.cajero_nombre}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Apertura</span>
                                <span class="font-medium text-foreground">{formatFechaHora(turno_activo.apertura)}</span>
                            </div>
                        </div>
                        <Link
                            href="/caja/turnos/{turno_activo.id}"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Ver turno
                        </Link>
                    </div>
                {:else}
                    <div class="flex flex-col items-center justify-center gap-4 py-6 text-center">
                        <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                            <ShoppingCart class="size-5 text-muted-foreground" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-foreground">Sin turno activo</p>
                            <p class="mt-0.5 text-xs text-muted-foreground">No hay ningún turno de caja abierto</p>
                        </div>
                        <Link
                            href="/caja/turnos/abrir"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                        >
                            Abrir turno
                        </Link>
                    </div>
                {/if}
            </div>
        </div>

        <!-- Row 4: Top Products + Last Invoices -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            <!-- Top 5 Productos del Mes -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-foreground">Top 5 Productos del Mes</h3>
                    <Link
                        href="/reportes/ventas"
                        class="text-xs text-primary hover:underline"
                    >
                        Ver reporte completo
                    </Link>
                </div>

                {#if top_productos_mes.length === 0}
                    <p class="py-6 text-center text-sm text-muted-foreground">Sin ventas registradas este mes</p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-sidebar-border/50">
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">#</th>
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Nombre</th>
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Código</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Cant.</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each top_productos_mes as producto, i (producto.codigo)}
                                    <tr class="border-b border-sidebar-border/30 last:border-0">
                                        <td class="py-2.5 pr-2 text-xs font-bold text-muted-foreground">{i + 1}</td>
                                        <td class="py-2.5 pr-2 font-medium text-foreground">{producto.nombre}</td>
                                        <td class="py-2.5 pr-2 text-xs text-muted-foreground">{producto.codigo}</td>
                                        <td class="py-2.5 pr-2 text-right text-xs">{producto.cantidad.toLocaleString('es-CO')}</td>
                                        <td class="py-2.5 text-right text-xs font-semibold text-foreground">{formatCOP(producto.total_vendido)}</td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>

            <!-- Últimas 5 Facturas -->
            <div class="rounded-xl border border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-foreground">Últimas Facturas</h3>
                    <Link
                        href="/facturacion"
                        class="text-xs text-primary hover:underline"
                    >
                        Ver todas
                    </Link>
                </div>

                {#if ultimas_facturas.length === 0}
                    <p class="py-6 text-center text-sm text-muted-foreground">Sin facturas registradas</p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-sidebar-border/50">
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Número</th>
                                    <th class="pb-2 text-left text-xs font-medium text-muted-foreground">Cliente</th>
                                    <th class="pb-2 text-right text-xs font-medium text-muted-foreground">Total</th>
                                    <th class="pb-2 text-center text-xs font-medium text-muted-foreground">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each ultimas_facturas as factura (factura.id)}
                                    <tr class="border-b border-sidebar-border/30 last:border-0">
                                        <td class="py-2.5 pr-2 text-xs font-medium text-foreground">{factura.numero_completo}</td>
                                        <td class="py-2.5 pr-2 max-w-[120px] truncate text-xs text-muted-foreground" title={factura.cliente_nombre}>{factura.cliente_nombre}</td>
                                        <td class="py-2.5 pr-2 text-right text-xs font-semibold text-foreground">{formatCOP(factura.total)}</td>
                                        <td class="py-2.5 text-center">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-medium {estadoBadgeClass(factura.estado)}">
                                                {estadoLabel(factura.estado)}
                                            </span>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </div>
        </div>

    </div>
</AppLayout>
