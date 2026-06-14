<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { formatCOP } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import Download from 'lucide-svelte/icons/download';
    import BarChart2 from 'lucide-svelte/icons/bar-chart-2';
    import DollarSign from 'lucide-svelte/icons/dollar-sign';
    import Percent from 'lucide-svelte/icons/percent';
    import PackageSearch from 'lucide-svelte/icons/package-search';

    interface ProductoRentabilidad {
        producto_id: number;
        nombre: string;
        codigo: string;
        categoria: string;
        cantidad_vendida: number;
        ingresos_total: number;
        costo_total: number;
        utilidad_bruta: number;
        margen_pct: number;
    }

    interface Filtros {
        desde: string;
        hasta: string;
    }

    let {
        productos_rentabilidad,
        total_ingresos,
        total_costos,
        utilidad_bruta,
        margen_promedio,
        filtros,
    }: {
        productos_rentabilidad: ProductoRentabilidad[];
        total_ingresos: number;
        total_costos: number;
        utilidad_bruta: number;
        margen_promedio: number;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'Rentabilidad', href: '/reportes/rentabilidad' },
    ];

    let desde = $state(untrack(() => filtros.desde ?? ''));
    let hasta = $state(untrack(() => filtros.hasta ?? ''));

    function aplicarFiltros() {
        router.get('/reportes/rentabilidad', { desde, hasta }, { preserveState: true });
    }

    function margenClass(pct: number): string {
        if (pct >= 20) return 'text-emerald-600 font-semibold';
        if (pct >= 10) return 'text-yellow-600 font-semibold';
        return 'text-red-600 font-semibold';
    }

    function margenBarClass(pct: number): string {
        if (pct >= 20) return 'bg-emerald-500';
        if (pct >= 10) return 'bg-yellow-400';
        return 'bg-red-500';
    }

    function utilidadClass(value: number): string {
        if (value > 0) return 'text-emerald-600 font-bold';
        if (value < 0) return 'text-red-600 font-bold';
        return 'text-muted-foreground font-bold';
    }

    const totalCantidad = $derived(
        productos_rentabilidad.reduce((s, p) => s + p.cantidad_vendida, 0),
    );

    function exportarCsv() {
        const headers = [
            'Código',
            'Producto',
            'Categoría',
            'Cant. Vendida',
            'Ingresos',
            'Costos',
            'Utilidad',
            'Margen %',
        ];
        const rows = productos_rentabilidad.map((p) => [
            p.codigo ?? '',
            p.nombre,
            p.categoria ?? '',
            p.cantidad_vendida,
            p.ingresos_total,
            p.costo_total,
            p.utilidad_bruta,
            p.margen_pct.toFixed(2),
        ]);
        const totalsRow = [
            '',
            'TOTALES',
            '',
            totalCantidad,
            total_ingresos,
            total_costos,
            utilidad_bruta,
            margen_promedio.toFixed(2),
        ];

        const csvContent = [headers, ...rows, totalsRow]
            .map((row) =>
                row
                    .map((cell) =>
                        typeof cell === 'string' && cell.includes(',')
                            ? `"${cell}"`
                            : String(cell),
                    )
                    .join(','),
            )
            .join('\n');

        const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `rentabilidad_${filtros.desde ?? 'inicio'}_${filtros.hasta ?? 'hoy'}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }
</script>

<AppHead title="Reporte de Rentabilidad" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                    <BarChart2 class="size-6 text-blue-600" />
                    Rentabilidad por Producto
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Análisis de márgenes y utilidad bruta por producto vendido.
                </p>
            </div>
            <Button variant="outline" size="sm" onclick={exportarCsv} disabled={productos_rentabilidad.length === 0}>
                <Download class="mr-2 size-4" />
                Exportar CSV
            </Button>
        </div>

        <!-- Filtros de fecha -->
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
                    <Button onclick={aplicarFiltros}>Aplicar</Button>
                </div>
            </CardContent>
        </Card>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <TrendingUp class="size-4" />
                        Total Ingresos
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-emerald-600">{formatCOP(total_ingresos)}</p>
                    <p class="text-xs text-muted-foreground">ventas netas del período</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <TrendingDown class="size-4" />
                        Total Costos
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-red-600">{formatCOP(total_costos)}</p>
                    <p class="text-xs text-muted-foreground">costo de ventas</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <DollarSign class="size-4" />
                        Utilidad Bruta
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold {utilidadClass(utilidad_bruta)}">{formatCOP(utilidad_bruta)}</p>
                    <p class="text-xs text-muted-foreground">ingresos − costos</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <Percent class="size-4" />
                        Margen Promedio
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold {margenClass(margen_promedio)}">{margen_promedio.toFixed(1)}%</p>
                    <p class="text-xs text-muted-foreground">promedio ponderado</p>
                </CardContent>
            </Card>
        </div>

        <!-- Tabla de rentabilidad -->
        <Card>
            <CardHeader>
                <CardTitle class="text-base">Rentabilidad por Producto</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                {#if productos_rentabilidad.length === 0}
                    <div class="flex flex-col items-center justify-center gap-2 py-16 text-muted-foreground">
                        <PackageSearch class="size-10 opacity-40" />
                        <p class="text-sm">No hay datos de rentabilidad para el período seleccionado.</p>
                    </div>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/40">
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">#</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Código</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Producto</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Categoría</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Cant. Vendida</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Ingresos</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Costos</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Utilidad</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground" style="min-width: 140px;">Margen %</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each productos_rentabilidad as p, i (p.producto_id)}
                                    <tr class="border-b last:border-0 hover:bg-muted/30 transition-colors">
                                        <td class="px-4 py-3 text-muted-foreground">{i + 1}</td>
                                        <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{p.codigo ?? '—'}</td>
                                        <td class="px-4 py-3 font-medium">{p.nombre}</td>
                                        <td class="px-4 py-3 text-muted-foreground">{p.categoria ?? '—'}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">
                                            {new Intl.NumberFormat('es-CO').format(p.cantidad_vendida)}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums text-emerald-600">
                                            {formatCOP(p.ingresos_total)}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums text-red-500">
                                            {formatCOP(p.costo_total)}
                                        </td>
                                        <td class="px-4 py-3 text-right tabular-nums {utilidadClass(p.utilidad_bruta)}">
                                            {formatCOP(p.utilidad_bruta)}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-2 w-20 overflow-hidden rounded-full bg-muted">
                                                    <div
                                                        class="h-full rounded-full {margenBarClass(p.margen_pct)} transition-all"
                                                        style="width: {Math.min(Math.max(p.margen_pct, 0), 100)}%;"
                                                    ></div>
                                                </div>
                                                <span class="w-12 tabular-nums {margenClass(p.margen_pct)}">
                                                    {p.margen_pct.toFixed(1)}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-border bg-muted/60">
                                    <td colspan="4" class="px-4 py-3 font-bold">Totales</td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold">
                                        {new Intl.NumberFormat('es-CO').format(totalCantidad)}
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold text-emerald-600">
                                        {formatCOP(total_ingresos)}
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums font-bold text-red-500">
                                        {formatCOP(total_costos)}
                                    </td>
                                    <td class="px-4 py-3 text-right tabular-nums {utilidadClass(utilidad_bruta)}">
                                        {formatCOP(utilidad_bruta)}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="{margenClass(margen_promedio)}">
                                            {margen_promedio.toFixed(1)}%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                {/if}
            </CardContent>
        </Card>
    </div>
</AppLayout>
