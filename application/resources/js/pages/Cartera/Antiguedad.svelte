<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronUp from 'lucide-svelte/icons/chevron-up';
    import Clock from 'lucide-svelte/icons/clock';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { formatCOP, formatFecha } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface CuentaItem {
        id: number;
        cliente: string;
        factura_id: number | null;
        saldo: number | string;
        fecha_vencimiento: string;
        dias_vencida: number;
        estado: string;
    }

    interface Bucket {
        label: string;
        cuentas: CuentaItem[];
        total: number | string;
    }

    interface Buckets {
        '0_30': Bucket;
        '31_60': Bucket;
        '61_90': Bucket;
        'mas90': Bucket;
    }

    let {
        buckets,
        total_general,
    }: {
        buckets: Buckets;
        total_general: number | string;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Cartera', href: '/cartera' },
        { title: 'Antigüedad', href: '/cartera/antiguedad' },
    ];

    const bucketOrder = ['0_30', '31_60', '61_90', 'mas90'] as const;

    const bucketStyles = {
        '0_30':  {
            card:  'border-green-200 dark:border-green-800',
            icon:  'text-green-600 dark:text-green-400',
            total: 'text-green-700 dark:text-green-400',
            bar:   'bg-green-500',
            badge: 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100',
        },
        '31_60': {
            card:  'border-yellow-200 dark:border-yellow-800',
            icon:  'text-yellow-600 dark:text-yellow-400',
            total: 'text-yellow-700 dark:text-yellow-400',
            bar:   'bg-yellow-500',
            badge: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-100',
        },
        '61_90': {
            card:  'border-orange-200 dark:border-orange-800',
            icon:  'text-orange-600 dark:text-orange-400',
            total: 'text-orange-700 dark:text-orange-400',
            bar:   'bg-orange-500',
            badge: 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900 dark:text-orange-100',
        },
        'mas90': {
            card:  'border-red-200 dark:border-red-800',
            icon:  'text-red-600 dark:text-red-400',
            total: 'text-red-700 dark:text-red-400',
            bar:   'bg-red-500',
            badge: 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-100',
        },
    };

    let expanded = $state<Record<string, boolean>>({
        '0_30': true,
        '31_60': false,
        '61_90': false,
        'mas90': false,
    });

    function numVal(v: number | string | null | undefined): number {
        if (v === null || v === undefined) return 0;
        return Number(v);
    }

    const totalGeneral = $derived(numVal(total_general));

    function pct(bucket: Bucket): number {
        if (totalGeneral <= 0) return 0;
        return Math.round((numVal(bucket.total) / totalGeneral) * 100);
    }

    function estadoBadgeClass(estado: string): string {
        const map: Record<string, string> = {
            pendiente: 'bg-yellow-100 text-yellow-800 border-yellow-200',
            parcial:   'bg-blue-100 text-blue-800 border-blue-200',
            vencida:   'bg-red-100 text-red-800 border-red-200',
            pagada:    'bg-green-100 text-green-800 border-green-200',
        };
        return map[estado] ?? '';
    }

    function estadoLabel(estado: string): string {
        const map: Record<string, string> = {
            pendiente: 'Pendiente', parcial: 'Parcial', vencida: 'Vencida', pagada: 'Pagada',
        };
        return map[estado] ?? estado;
    }
</script>

<AppHead title="Antigüedad de Cartera" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Antigüedad de Cartera</h1>
                <p class="text-sm text-muted-foreground">
                    Análisis de cuentas por cobrar agrupadas por días de vencimiento
                </p>
            </div>
            <Button variant="outline" href="/cartera">
                Volver a Cartera
            </Button>
        </div>

        <!-- Resumen total -->
        <Card>
            <CardContent class="pt-4 pb-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <Clock class="size-5 text-muted-foreground" />
                        <span class="text-sm text-muted-foreground">Total cartera vencida</span>
                    </div>
                    <span class="text-2xl font-bold text-destructive">{formatCOP(totalGeneral)}</span>
                </div>
            </CardContent>
        </Card>

        <!-- Tarjetas resumen por bucket -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {#each bucketOrder as key (key)}
                {@const bucket = buckets[key]}
                {@const style = bucketStyles[key]}
                {@const porcentaje = pct(bucket)}
                <Card class={style.card}>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium text-muted-foreground">{bucket.label}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <p class="text-2xl font-bold {style.total}">
                            {formatCOP(numVal(bucket.total))}
                        </p>
                        <p class="text-xs text-muted-foreground">
                            {bucket.cuentas.length} {bucket.cuentas.length === 1 ? 'cuenta' : 'cuentas'}
                        </p>
                        <!-- Barra de proporción -->
                        <div class="h-2 w-full rounded-full bg-muted">
                            <div
                                class="h-2 rounded-full transition-all {style.bar}"
                                style="width: {porcentaje}%"
                            ></div>
                        </div>
                        <p class="text-xs text-muted-foreground">{porcentaje}% del total</p>
                    </CardContent>
                </Card>
            {/each}
        </div>

        <!-- Detalle por bucket -->
        {#each bucketOrder as key (key)}
            {@const bucket = buckets[key]}
            {@const style = bucketStyles[key]}
            {#if bucket.cuentas.length > 0}
                <Card>
                    <CardHeader class="pb-0">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-base {style.total}">
                                {bucket.label}
                                <span class="ml-2 text-sm font-normal text-muted-foreground">
                                    ({bucket.cuentas.length} cuentas · {formatCOP(numVal(bucket.total))})
                                </span>
                            </CardTitle>
                            <Button
                                variant="ghost"
                                size="sm"
                                onclick={() => (expanded[key] = !expanded[key])}
                            >
                                {#if expanded[key]}
                                    <ChevronUp class="size-4" />
                                {:else}
                                    <ChevronDown class="size-4" />
                                {/if}
                            </Button>
                        </div>
                    </CardHeader>

                    {#if expanded[key]}
                        <CardContent class="pt-3 p-0">
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b bg-muted/50">
                                        <tr>
                                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Cliente</th>
                                            <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Saldo</th>
                                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Vencimiento</th>
                                            <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Días vencida</th>
                                            <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Estado</th>
                                            <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        {#each bucket.cuentas as cuenta (cuenta.id)}
                                            <tr class="hover:bg-muted/30 transition-colors">
                                                <td class="px-4 py-2.5 font-medium">{cuenta.cliente}</td>
                                                <td class="px-4 py-2.5 text-right font-semibold text-destructive">
                                                    {formatCOP(numVal(cuenta.saldo))}
                                                </td>
                                                <td class="px-4 py-2.5 text-xs text-muted-foreground">
                                                    {formatFecha(cuenta.fecha_vencimiento)}
                                                </td>
                                                <td class="px-4 py-2.5 text-right">
                                                    {#if cuenta.dias_vencida > 0}
                                                        <span class="font-medium {style.total}">{cuenta.dias_vencida}d</span>
                                                    {:else}
                                                        <span class="text-muted-foreground">—</span>
                                                    {/if}
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <Badge class={estadoBadgeClass(cuenta.estado)}>
                                                        {estadoLabel(cuenta.estado)}
                                                    </Badge>
                                                </td>
                                                <td class="px-4 py-2.5 text-right">
                                                    {#if cuenta.factura_id}
                                                        <Button variant="ghost" size="sm" href={`/facturacion/${cuenta.factura_id}`}>
                                                            Ver detalle
                                                        </Button>
                                                    {:else}
                                                        <span class="text-xs text-muted-foreground">—</span>
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        </CardContent>
                    {/if}
                </Card>
            {/if}
        {/each}
    </div>
</AppLayout>
