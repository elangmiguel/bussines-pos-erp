<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Download, FileSpreadsheet } from 'lucide-svelte';
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
    import type { BreadcrumbItem } from '@/types';

    interface FilaCompra {
        fecha: string;
        tipo: 'gasto' | 'orden_compra';
        doc_numero: string;
        nit_proveedor: string;
        nombre_proveedor: string;
        subtotal: number;
        iva_descontable: number;
        total: number;
    }

    interface Totales {
        total_compras: number;
        iva_descontable: number;
        total: number;
    }

    interface Filtros {
        desde: string;
        hasta: string;
    }

    let {
        filas = [],
        desde: desdeInit = '',
        hasta: hastaInit = '',
        totales,
        filtros,
    }: {
        filas: FilaCompra[];
        desde: string;
        hasta: string;
        totales: Totales;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'DIAN', href: '/reportes/dian/libro-compras' },
        { title: 'Libro de Compras', href: '/reportes/dian/libro-compras' },
    ];

    let desde = $state(untrack(() => filtros?.desde ?? desdeInit ?? ''));
    let hasta = $state(untrack(() => filtros?.hasta ?? hastaInit ?? ''));
    let errorFechas = $state('');

    function generar() {
        if (!desde || !hasta) {
            errorFechas = 'Las fechas desde y hasta son requeridas.';
            return;
        }
        if (desde > hasta) {
            errorFechas = 'La fecha inicial no puede ser mayor a la fecha final.';
            return;
        }
        errorFechas = '';
        router.get('/reportes/dian/libro-compras', { desde, hasta }, { preserveState: false });
    }

    function exportarCsv() {
        if (!desde || !hasta) return;
        const params = new URLSearchParams({ tipo: 'libro_compras', desde, hasta });
        window.location.href = `/reportes/exportar?${params.toString()}`;
    }

    function formatCop(value: number): string {
        if (!value && value !== 0) return '—';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    }

    function tipoBadgeVariant(tipo: string): 'default' | 'secondary' | 'destructive' | 'outline' {
        return tipo === 'gasto' ? 'secondary' : 'outline';
    }

    function tipoLabel(tipo: string): string {
        return tipo === 'gasto' ? 'Gasto' : 'Orden de Compra';
    }

    const mostrandoDatos = $derived(filas.length > 0);
</script>

<AppHead title="Libro de Compras — DIAN" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                    <FileSpreadsheet class="size-6 text-orange-600" />
                    Libro de Compras
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Gastos y órdenes de compra con IVA descontable para declaración tributaria.
                </p>
            </div>
            {#if mostrandoDatos}
                <Button variant="outline" size="sm" onclick={exportarCsv}>
                    <Download class="mr-2 size-4" />
                    Exportar CSV
                </Button>
            {/if}
        </div>

        <!-- Selector de fechas -->
        <Card>
            <CardContent class="pt-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-1.5">
                        <Label for="desde">Desde <span class="text-destructive">*</span></Label>
                        <Input id="desde" type="date" bind:value={desde} class="w-40" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="hasta">Hasta <span class="text-destructive">*</span></Label>
                        <Input id="hasta" type="date" bind:value={hasta} class="w-40" />
                    </div>
                    <Button onclick={generar}>Generar reporte</Button>
                </div>
                {#if errorFechas}
                    <p class="mt-2 text-sm text-destructive">{errorFechas}</p>
                {/if}
            </CardContent>
        </Card>

        {#if mostrandoDatos}
            <!-- Tarjetas de totales -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Total Compras (sin IVA)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xl font-bold tabular-nums">{formatCop(totales.total_compras)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            IVA Descontable
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xl font-bold text-emerald-600 tabular-nums">
                            {formatCop(totales.iva_descontable)}
                        </p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">
                            Total con IVA
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-xl font-bold text-orange-600 tabular-nums">
                            {formatCop(totales.total)}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Leyenda de tipos -->
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
                <span>Tipos:</span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-amber-400"></span>
                    Gasto
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded-sm bg-blue-500"></span>
                    Orden de Compra
                </span>
            </div>

            <!-- Tabla -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">
                        {filas.length} registros — {desde} al {hasta}
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[800px] text-sm">
                            <thead>
                                <tr class="border-b bg-muted/40">
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Fecha</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Tipo</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground"># Documento</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">NIT Proveedor</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Proveedor</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Subtotal</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">IVA Descontable</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each filas as fila, i (i)}
                                    <tr class="border-b last:border-0 hover:bg-muted/20">
                                        <td class="px-4 py-2 tabular-nums text-muted-foreground">{fila.fecha}</td>
                                        <td class="px-4 py-2">
                                            {#if fila.tipo === 'gasto'}
                                                <Badge
                                                    variant="secondary"
                                                    class="bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300"
                                                >
                                                    Gasto
                                                </Badge>
                                            {:else}
                                                <Badge
                                                    variant="outline"
                                                    class="border-blue-300 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-950/30 dark:text-blue-300"
                                                >
                                                    Orden Compra
                                                </Badge>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-2 font-mono">{fila.doc_numero}</td>
                                        <td class="px-4 py-2 tabular-nums">{fila.nit_proveedor}</td>
                                        <td class="max-w-[200px] truncate px-4 py-2" title={fila.nombre_proveedor}>
                                            {fila.nombre_proveedor}
                                        </td>
                                        <td class="px-4 py-2 text-right tabular-nums">{formatCop(fila.subtotal)}</td>
                                        <td class="px-4 py-2 text-right tabular-nums text-emerald-700 dark:text-emerald-400">
                                            {formatCop(fila.iva_descontable)}
                                        </td>
                                        <td class="px-4 py-2 text-right tabular-nums font-medium">
                                            {formatCop(fila.total)}
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 bg-muted/50 font-bold">
                                    <td colspan="5" class="px-4 py-2.5 text-sm">TOTALES</td>
                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                        {formatCop(totales.total_compras)}
                                    </td>
                                    <td class="px-4 py-2.5 text-right tabular-nums text-emerald-700 dark:text-emerald-400">
                                        {formatCop(totales.iva_descontable)}
                                    </td>
                                    <td class="px-4 py-2.5 text-right tabular-nums">
                                        {formatCop(totales.total)}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>
        {:else if desde && hasta}
            <div class="rounded-lg border border-dashed p-12 text-center text-muted-foreground">
                No se encontraron compras o gastos para el período {desde} — {hasta}.
            </div>
        {:else}
            <div class="rounded-lg border border-dashed p-12 text-center text-muted-foreground">
                Selecciona un rango de fechas y haz clic en <strong>Generar reporte</strong>.
            </div>
        {/if}
    </div>
</AppLayout>
