<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import Download from 'lucide-svelte/icons/download';
    import FileText from 'lucide-svelte/icons/file-text';
    import Info from 'lucide-svelte/icons/info';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { formatCOP, formatFecha } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface FilaVenta {
        fecha: string;
        numero_completo: string;
        tipo_id_cliente: string;
        identificacion_cliente: string;
        nombre_cliente: string;
        base_iva_0: number;
        base_iva_5: number;
        iva_5: number;
        base_iva_19: number;
        iva_19: number;
        inc: number;
        descuento: number;
        total: number;
    }

    interface Totales {
        base_0: number;
        base_5: number;
        iva_5: number;
        base_19: number;
        iva_19: number;
        inc: number;
        descuento: number;
        total: number;
    }

    interface Datos {
        facturas: FilaVenta[];
        totales: Totales;
    }

    interface Filtros {
        desde: string;
        hasta: string;
    }

    let { datos, filtros }: { datos: Datos; filtros: Filtros } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'DIAN', href: '/reportes/dian/libro-ventas' },
        { title: 'Libro de Ventas', href: '/reportes/dian/libro-ventas' },
    ];

    let desde = $state(untrack(() => filtros?.desde ?? ''));
    let hasta = $state(untrack(() => filtros?.hasta ?? ''));
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
        router.get('/reportes/dian/libro-ventas', { desde, hasta }, { preserveState: false });
    }

    const exportUrl = $derived(
        `/reportes/dian/libro-ventas/exportar?desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}`,
    );

    const mostrandoDatos = $derived(datos.facturas.length > 0);
</script>

<AppHead title="Libro de Ventas — DIAN" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                    <FileText class="size-6 text-rose-600" />
                    Libro de Ventas
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Registro para declaración de IVA bimestral/cuatrimestral según régimen.
                </p>
            </div>
            {#if mostrandoDatos}
                <a href={exportUrl}>
                    <Button variant="outline" size="sm">
                        <Download class="mr-2 size-4" />
                        Exportar CSV
                    </Button>
                </a>
            {/if}
        </div>

        <!-- Nota DIAN -->
        <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950/30">
            <Info class="mt-0.5 size-4 shrink-0 text-amber-600" />
            <p class="text-sm text-amber-800 dark:text-amber-300">
                <strong>Reporte para declaración de IVA bimestral/cuatrimestral.</strong>
                Incluye todas las facturas con estado "emitida" en el período. Verifique con su contador la periodicidad aplicable.
            </p>
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
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
                <Card>
                    <CardHeader class="pb-1 pt-3">
                        <CardTitle class="text-xs font-medium text-muted-foreground">Base Gravable 19%</CardTitle>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <p class="text-base font-bold tabular-nums">{formatCOP(datos.totales.base_19)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-1 pt-3">
                        <CardTitle class="text-xs font-medium text-muted-foreground">IVA 19%</CardTitle>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <p class="text-base font-bold text-rose-600 tabular-nums">{formatCOP(datos.totales.iva_19)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-1 pt-3">
                        <CardTitle class="text-xs font-medium text-muted-foreground">Base Gravable 5%</CardTitle>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <p class="text-base font-bold tabular-nums">{formatCOP(datos.totales.base_5)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-1 pt-3">
                        <CardTitle class="text-xs font-medium text-muted-foreground">IVA 5%</CardTitle>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <p class="text-base font-bold text-orange-600 tabular-nums">{formatCOP(datos.totales.iva_5)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-1 pt-3">
                        <CardTitle class="text-xs font-medium text-muted-foreground">Base Excluida (0%)</CardTitle>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <p class="text-base font-bold tabular-nums">{formatCOP(datos.totales.base_0)}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-1 pt-3">
                        <CardTitle class="text-xs font-medium text-muted-foreground">Total Facturado</CardTitle>
                    </CardHeader>
                    <CardContent class="pb-3">
                        <p class="text-base font-bold text-blue-600 tabular-nums">{formatCOP(datos.totales.total)}</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabla completa -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-sm">
                        {datos.facturas.length} registros — {desde} al {hasta}
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1100px] text-xs">
                            <thead>
                                <tr class="border-b bg-muted/40">
                                    <th class="px-3 py-2.5 text-left font-medium text-muted-foreground">Fecha</th>
                                    <th class="px-3 py-2.5 text-left font-medium text-muted-foreground"># Factura</th>
                                    <th class="px-3 py-2.5 text-left font-medium text-muted-foreground">Tipo ID</th>
                                    <th class="px-3 py-2.5 text-left font-medium text-muted-foreground">Identificación</th>
                                    <th class="px-3 py-2.5 text-left font-medium text-muted-foreground">Cliente</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">Base Excl.</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">Base 5%</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">IVA 5%</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">Base 19%</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">IVA 19%</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">INC</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">Descuento</th>
                                    <th class="px-3 py-2.5 text-right font-medium text-muted-foreground">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each datos.facturas as fila, i (i)}
                                    <tr class="border-b last:border-0 hover:bg-muted/20">
                                        <td class="px-3 py-2 tabular-nums text-muted-foreground">{formatFecha(fila.fecha)}</td>
                                        <td class="px-3 py-2 font-mono font-medium">{fila.numero_completo}</td>
                                        <td class="px-3 py-2 text-muted-foreground">{fila.tipo_id_cliente}</td>
                                        <td class="px-3 py-2 tabular-nums">{fila.identificacion_cliente}</td>
                                        <td class="max-w-[160px] truncate px-3 py-2" title={fila.nombre_cliente}>
                                            {fila.nombre_cliente}
                                        </td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.base_iva_0)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.base_iva_5)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.iva_5)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.base_iva_19)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.iva_19)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.inc)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums">{formatCOP(fila.descuento)}</td>
                                        <td class="px-3 py-2 text-right tabular-nums font-medium">{formatCOP(fila.total)}</td>
                                    </tr>
                                {/each}
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 bg-muted/50 font-bold">
                                    <td colspan="5" class="px-3 py-2.5 text-sm">TOTALES</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.base_0)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.base_5)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.iva_5)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.base_19)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.iva_19)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.inc)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.descuento)}</td>
                                    <td class="px-3 py-2.5 text-right tabular-nums">{formatCOP(datos.totales.total)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </CardContent>
            </Card>
        {:else if desde && hasta}
            <div class="rounded-lg border border-dashed p-12 text-center text-muted-foreground">
                No se encontraron facturas emitidas para el período {desde} — {hasta}.
            </div>
        {:else}
            <div class="rounded-lg border border-dashed p-12 text-center text-muted-foreground">
                Selecciona un rango de fechas y haz clic en <strong>Generar reporte</strong>.
            </div>
        {/if}
    </div>
</AppLayout>
