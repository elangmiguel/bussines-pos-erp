<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { formatCOP } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';
    import Wallet from 'lucide-svelte/icons/wallet';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import Clock from 'lucide-svelte/icons/clock';
    import TrendingUp from 'lucide-svelte/icons/trending-up';

    interface BucketData {
        count: number;
        total: number;
    }

    interface Antiguedad {
        buckets: {
            '0-30': BucketData;
            '31-60': BucketData;
            '61-90': BucketData;
            '>90': BucketData;
        };
        lista: CarteraItem[];
    }

    interface CarteraItem {
        id: number;
        cliente_id: number;
        nombre_cliente: string;
        factura_numero: string;
        monto_total: number;
        monto_pagado: number;
        saldo: number;
        fecha_vencimiento: string;
        dias_vencidos: number;
        estado: string;
        bucket: string;
    }

    let {
        antiguedad,
        total_cartera,
        total_vencida,
    }: {
        antiguedad: Antiguedad;
        total_cartera: number;
        total_vencida: number;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'Cartera por Antigüedad', href: '/reportes/cartera' },
    ];

    const buckets = $derived(antiguedad.buckets);
    const lista = $derived(antiguedad.lista);

    const totalMas30 = $derived(
        (buckets['31-60']?.total ?? 0) +
        (buckets['61-90']?.total ?? 0) +
        (buckets['>90']?.total ?? 0),
    );

    const countMas30 = $derived(
        (buckets['31-60']?.count ?? 0) +
        (buckets['61-90']?.count ?? 0) +
        (buckets['>90']?.count ?? 0),
    );

    const maxBucketTotal = $derived(
        Math.max(
            buckets['0-30']?.total ?? 0,
            buckets['31-60']?.total ?? 0,
            buckets['61-90']?.total ?? 0,
            buckets['>90']?.total ?? 0,
            1,
        ),
    );

    const bucketConfig = [
        {
            key: '0-30' as const,
            label: '0–30 días',
            barClass: 'bg-emerald-500',
            textClass: 'text-emerald-600',
        },
        {
            key: '31-60' as const,
            label: '31–60 días',
            barClass: 'bg-yellow-400',
            textClass: 'text-yellow-600',
        },
        {
            key: '61-90' as const,
            label: '61–90 días',
            barClass: 'bg-orange-500',
            textClass: 'text-orange-600',
        },
        {
            key: '>90' as const,
            label: '+90 días',
            barClass: 'bg-red-500',
            textClass: 'text-red-600',
        },
    ];

    function rowClass(bucket: string): string {
        switch (bucket) {
            case '31-60': return 'bg-yellow-50 dark:bg-yellow-950/20';
            case '61-90': return 'bg-orange-50 dark:bg-orange-950/20';
            case '>90':   return 'bg-red-50 dark:bg-red-950/20';
            default:      return '';
        }
    }

    function diasClass(dias: number): string {
        if (dias <= 30)  return 'text-emerald-600 font-medium';
        if (dias <= 60)  return 'text-yellow-600 font-medium';
        if (dias <= 90)  return 'text-orange-600 font-medium';
        return 'text-red-600 font-bold';
    }

    function estadoBadgeVariant(estado: string): 'default' | 'secondary' | 'destructive' | 'outline' {
        switch (estado) {
            case 'pagada':   return 'default';
            case 'parcial':  return 'secondary';
            default:         return 'outline';
        }
    }

    function estadoLabel(estado: string): string {
        switch (estado) {
            case 'pagada':   return 'Pagada';
            case 'parcial':  return 'Parcial';
            case 'pendiente': return 'Pendiente';
            default:         return estado;
        }
    }

    function estadoBadgeClass(estado: string): string {
        switch (estado) {
            case 'pagada':   return 'bg-emerald-100 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400';
            case 'parcial':  return 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400';
            default:         return 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-950/40 dark:text-yellow-400';
        }
    }

    function formatFecha(str: string): string {
        if (!str) return '—';
        const d = new Date(str);
        return new Intl.DateTimeFormat('es-CO', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(d);
    }
</script>

<AppHead title="Cartera por Antigüedad" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <Wallet class="size-6 text-blue-600" />
                Cartera por Antigüedad
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Análisis de cuentas por cobrar clasificadas por tiempo de vencimiento.
            </p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <Wallet class="size-4" />
                        Total Cartera Pendiente
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-blue-600">{formatCOP(total_cartera)}</p>
                    <p class="text-xs text-muted-foreground">{lista.length} cuenta(s)</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <AlertTriangle class="size-4" />
                        Cartera Vencida
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold {total_vencida > 0 ? 'text-red-600' : 'text-emerald-600'}">
                        {formatCOP(total_vencida)}
                    </p>
                    <p class="text-xs text-muted-foreground">saldo vencido total</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <Clock class="size-4" />
                        0–30 días
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-emerald-600">{formatCOP(buckets['0-30']?.total ?? 0)}</p>
                    <p class="text-xs text-muted-foreground">{buckets['0-30']?.count ?? 0} cuenta(s)</p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                        <TrendingUp class="size-4" />
                        &gt; 30 días
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-orange-600">{formatCOP(totalMas30)}</p>
                    <p class="text-xs text-muted-foreground">{countMas30} cuenta(s)</p>
                </CardContent>
            </Card>
        </div>

        <!-- Aging Buckets — barras horizontales -->
        <Card>
            <CardHeader>
                <CardTitle class="text-base">Distribución por Antigüedad</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-col gap-4">
                    {#each bucketConfig as cfg (cfg.key)}
                        {@const data = buckets[cfg.key]}
                        {@const pct = maxBucketTotal > 0 ? ((data?.total ?? 0) / maxBucketTotal) * 100 : 0}
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-medium {cfg.textClass}">{cfg.label}</span>
                                <span class="tabular-nums text-muted-foreground">
                                    {data?.count ?? 0} cuenta(s) — <span class="font-semibold {cfg.textClass}">{formatCOP(data?.total ?? 0)}</span>
                                </span>
                            </div>
                            <div class="h-6 w-full overflow-hidden rounded-md bg-muted">
                                <div
                                    class="h-full rounded-md {cfg.barClass} transition-all duration-500"
                                    style="width: {pct}%;"
                                ></div>
                            </div>
                        </div>
                    {/each}
                </div>
            </CardContent>
        </Card>

        <!-- Tabla de cuentas -->
        <Card>
            <CardHeader>
                <CardTitle class="text-base">Detalle de Cuentas por Cobrar</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                {#if lista.length === 0}
                    <div class="flex flex-col items-center justify-center gap-2 py-16 text-muted-foreground">
                        <Wallet class="size-10 opacity-40" />
                        <p class="text-sm">No hay cuentas por cobrar pendientes.</p>
                    </div>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/40">
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cliente</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Factura</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Monto Total</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Pagado</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Saldo</th>
                                    <th class="px-4 py-3 text-left font-medium text-muted-foreground">Vencimiento</th>
                                    <th class="px-4 py-3 text-right font-medium text-muted-foreground">Días Vencidos</th>
                                    <th class="px-4 py-3 text-center font-medium text-muted-foreground">Estado</th>
                                    <th class="px-4 py-3 text-center font-medium text-muted-foreground">Bucket</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each lista as item (item.id)}
                                    <tr class="border-b last:border-0 {rowClass(item.bucket)} hover:brightness-95 transition-colors">
                                        <td class="px-4 py-3 font-medium">{item.nombre_cliente}</td>
                                        <td class="px-4 py-3 font-mono text-xs text-muted-foreground">{item.factura_numero}</td>
                                        <td class="px-4 py-3 text-right tabular-nums">{formatCOP(item.monto_total)}</td>
                                        <td class="px-4 py-3 text-right tabular-nums text-emerald-600">{formatCOP(item.monto_pagado)}</td>
                                        <td class="px-4 py-3 text-right tabular-nums font-semibold">{formatCOP(item.saldo)}</td>
                                        <td class="px-4 py-3 tabular-nums text-muted-foreground">{formatFecha(item.fecha_vencimiento)}</td>
                                        <td class="px-4 py-3 text-right tabular-nums {diasClass(item.dias_vencidos)}">
                                            {item.dias_vencidos}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {estadoBadgeClass(item.estado)}">
                                                {estadoLabel(item.estado)}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium {rowClass(item.bucket) ? '' : 'bg-muted text-muted-foreground'}">
                                                {item.bucket === '>90' ? '+90 días' : item.bucket + ' días'}
                                            </span>
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
