<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { BarChart2, Download, FileSpreadsheet, TrendingUp } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { BreadcrumbItem } from '@/types';

    interface VentaPeriodo {
        periodo: string;
        total_ventas: number;
        count_facturas: number;
    }

    interface TopProducto {
        producto_id: number;
        nombre: string;
        codigo: string;
        total_cantidad: number;
        total_ventas: number;
    }

    interface Filtros {
        desde: string;
        hasta: string;
        agrupacion: string;
    }

    let {
        ventas_periodo,
        top_productos,
        total_ventas,
        count_facturas,
        promedio_venta,
        filtros,
    }: {
        ventas_periodo: VentaPeriodo[];
        top_productos: TopProducto[];
        total_ventas: number;
        count_facturas: number;
        promedio_venta: number;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'Ventas', href: '/reportes/ventas' },
    ];

    let desde      = $state(untrack(() => filtros.desde ?? ''));
    let hasta      = $state(untrack(() => filtros.hasta ?? ''));
    let agrupacion = $state(untrack(() => filtros.agrupacion ?? 'dia'));

    function aplicarFiltros() {
        router.get('/reportes/ventas', { desde, hasta, agrupacion }, { preserveState: false });
    }

    function exportarCsv() {
        const params = new URLSearchParams({ tipo: 'ventas', desde, hasta });
        window.location.href = `/reportes/exportar?${params.toString()}`;
    }

    const maxVentas = $derived(
        ventas_periodo.length > 0
            ? Math.max(...ventas_periodo.map((v) => v.total_ventas))
            : 1,
    );

    function pctDelTotal(monto: number): string {
        return total_ventas > 0 ? ((monto / total_ventas) * 100).toFixed(1) : '0.0';
    }

    function formatCop(value: number): string {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    }

    function labelAgrupacion(a: string): string {
        const map: Record<string, string> = { dia: 'Diario', semana: 'Semanal', mes: 'Mensual' };
        return map[a] ?? a;
    }

    function periodoLabel(p: string, agrup: string): string {
        if (agrup === 'mes') {
            const [y, m] = p.split('-');
            return new Date(Number(y), Number(m) - 1).toLocaleString('es-CO', {
                month: 'short',
                year: '2-digit',
            });
        }
        if (agrup === 'semana') return `S${p.slice(-2)}`;
        // dia
        const parts = p.split('-');
        return `${parts[2]}/${parts[1]}`;
    }
</script>

<AppHead title="Reporte de Ventas" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                    <BarChart2 class="size-6 text-blue-600" />
                    Reporte de Ventas
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Análisis de ventas por período con indicadores clave.
                </p>
            </div>
            <Button variant="outline" size="sm" onclick={exportarCsv}>
                <Download class="mr-2 size-4" />
                Exportar CSV
            </Button>
        </div>

        <!-- Filtros -->
        <Card>
            <CardContent class="pt-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-1.5">
                        <Label for="desde">Desde</Label>
                        <Input id="desde" type="date" bind:value={desde} class="w-40" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="hasta">Hasta</Label>
                        <Input id="hasta" type="date" bind:value={hasta} class="w-40" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label>Agrupación</Label>
                        <Select type="single" bind:value={agrupacion}>
                            <SelectTrigger class="w-36">
                                {labelAgrupacion(agrupacion)}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="dia">Diario</SelectItem>
                                <SelectItem value="semana">Semanal</SelectItem>
                                <SelectItem value="mes">Mensual</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <Button onclick={aplicarFiltros}>Generar</Button>
                </div>
            </CardContent>
        </Card>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Total Ventas
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-blue-600">{formatCop(total_ventas)}</p>
                    <p class="text-xs text-muted-foreground">
                        {filtros.desde} — {filtros.hasta}
                    </p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Facturas Emitidas
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{count_facturas}</p>
                    <p class="text-xs text-muted-foreground">documentos válidos</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        Promedio por Factura
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-emerald-600">{formatCop(promedio_venta)}</p>
                    <p class="text-xs text-muted-foreground">ticket promedio</p>
                </CardContent>
            </Card>
        </div>

        <!-- Gráfica de barras HTML/CSS -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <TrendingUp class="size-4" />
                    Ventas por período — {labelAgrupacion(agrupacion)}
                </CardTitle>
            </CardHeader>
            <CardContent>
                {#if ventas_periodo.length === 0}
                    <p class="py-8 text-center text-muted-foreground">
                        No hay datos de ventas para el período seleccionado.
                    </p>
                {:else}
                    <div class="overflow-x-auto">
                        <div
                            class="flex min-w-0 items-end gap-1 pb-1"
                            style="min-height: 180px;"
                        >
                            {#each ventas_periodo as v (v.periodo)}
                                {@const pct = maxVentas > 0 ? (v.total_ventas / maxVentas) * 100 : 0}
                                <div
                                    class="group flex min-w-0 flex-1 flex-col items-center gap-1"
                                    title="{periodoLabel(v.periodo, agrupacion)}: {formatCop(v.total_ventas)} ({v.count_facturas} fact.)"
                                >
                                    <span
                                        class="hidden whitespace-nowrap rounded bg-foreground px-1.5 py-0.5 text-xs text-background opacity-0 transition-opacity group-hover:opacity-100"
                                    >
                                        {formatCop(v.total_ventas)}
                                    </span>
                                    <div
                                        class="w-full rounded-t bg-blue-500 transition-all group-hover:bg-blue-600"
                                        style="height: {Math.max(pct * 1.5, 2)}px;"
                                    ></div>
                                    <span
                                        class="w-full overflow-hidden text-ellipsis whitespace-nowrap text-center text-[10px] text-muted-foreground"
                                    >
                                        {periodoLabel(v.periodo, agrupacion)}
                                    </span>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </CardContent>
        </Card>

        <!-- Top Productos -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <FileSpreadsheet class="size-4" />
                    Top Productos por Ventas
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                {#if top_productos.length === 0}
                    <p class="px-6 py-8 text-center text-muted-foreground">
                        Sin datos para mostrar.
                    </p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/40">
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">#</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Producto</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Código</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Cant. Vendida</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total Ventas</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">% del Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each top_productos as p, i (p.producto_id)}
                                    <tr class="border-b last:border-0 hover:bg-muted/30">
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {i + 1}
                                        </td>
                                        <td class="px-4 py-3 font-medium">{p.nombre}</td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {p.codigo ?? '—'}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums">
                                            {new Intl.NumberFormat('es-CO').format(p.total_cantidad)}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums font-medium">
                                            {formatCop(p.total_ventas)}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <div class="h-2 w-20 overflow-hidden rounded-full bg-muted">
                                                    <div
                                                        class="h-full rounded-full bg-blue-500"
                                                        style="width: {pctDelTotal(p.total_ventas)}%"
                                                    ></div>
                                                </div>
                                                <span class="w-12 text-right tabular-nums text-muted-foreground">
                                                    {pctDelTotal(p.total_ventas)}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                {/if}
            </CardContent>
        </Card>
    </div>
</AppLayout>
