<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import {
        Search,
        AlertCircle,
        Clock,
        CreditCard,
        CheckCircle,
        User,
        Building2,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        CardDescription,
    } from '@/components/ui/card';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { BreadcrumbItem } from '@/types';

    interface ClienteRef {
        id: number;
        tipo: 'natural' | 'juridico';
        nombre: string;
        identificacion: string;
    }

    interface FacturaRef {
        id: number;
        numero: string;
        total: number;
    }

    interface CuentaPorCobrar {
        id: number;
        cliente_id: number;
        factura_id: number | null;
        monto_total: number;
        monto_pagado: number;
        saldo: number;
        fecha_vencimiento: string;
        estado: 'pendiente' | 'parcial' | 'vencida';
        cliente: ClienteRef | null;
        factura: FacturaRef | null;
    }

    interface PaginatedCuentas {
        data: CuentaPorCobrar[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: {
            current_page: number;
            last_page: number;
            total: number;
            from: number;
            to: number;
        };
    }

    interface Stats {
        total_cartera: number;
        vencida: number;
        por_vencer: number;
    }

    let {
        cuentas,
        stats,
    }: {
        cuentas: PaginatedCuentas;
        stats: Stats;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Cartera', href: '/cartera' },
    ];

    let search = $state('');
    let estadoFiltro = $state('');
    let soloVencidas = $state(false);

    const formatCOP = (value: number) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(value);

    const formatDate = (date: string) =>
        new Intl.DateTimeFormat('es-CO', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        }).format(new Date(date));

    function getDiasVencida(fecha: string): number {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const venc = new Date(fecha);
        venc.setHours(0, 0, 0, 0);
        const diff = today.getTime() - venc.getTime();
        return Math.max(0, Math.floor(diff / (1000 * 60 * 60 * 24)));
    }

    function isVencida(fecha: string): boolean {
        return new Date(fecha) < new Date();
    }

    function applyFilters() {
        const params: Record<string, string> = {};
        if (search) params.search = search;
        if (estadoFiltro) params.estado = estadoFiltro;
        if (soloVencidas) params.vencida = '1';
        router.get('/cartera', params, { preserveState: true, replace: true });
    }

    function clearFilters() {
        search = '';
        estadoFiltro = '';
        soloVencidas = false;
        router.get('/cartera', {}, { preserveState: true, replace: true });
    }

    function handleSearchKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter') applyFilters();
    }

    function estadoBadgeVariant(estado: string): 'default' | 'secondary' | 'destructive' | 'outline' {
        if (estado === 'vencida') return 'destructive';
        if (estado === 'parcial') return 'secondary';
        return 'outline';
    }

    function estadoLabel(estado: string): string {
        const labels: Record<string, string> = {
            pendiente: 'Pendiente',
            parcial: 'Parcial',
            vencida: 'Vencida',
        };
        return labels[estado] ?? estado;
    }
</script>

<AppHead title="Cartera" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Cartera</h1>
                <p class="text-sm text-muted-foreground">
                    Gestión de cuentas por cobrar y seguimiento de pagos
                </p>
            </div>
            <Button variant="outline" href="/cartera/antiguedad">
                <Clock class="mr-2 size-4" />
                Análisis de Antigüedad
            </Button>
        </div>

        <!-- KPI Cards -->
        <div class="grid gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">Total Cartera</CardTitle>
                    <CreditCard class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{formatCOP(stats.total_cartera)}</p>
                    <p class="text-xs text-muted-foreground mt-1">Saldo total pendiente de recaudo</p>
                </CardContent>
            </Card>

            <Card class="border-destructive/40 bg-destructive/5">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-destructive">Cartera Vencida</CardTitle>
                    <AlertCircle class="size-4 text-destructive" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-destructive">{formatCOP(stats.vencida)}</p>
                    <p class="text-xs text-muted-foreground mt-1">Supera la fecha de vencimiento</p>
                </CardContent>
            </Card>

            <Card class="border-yellow-400/40 bg-yellow-400/5">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-yellow-700 dark:text-yellow-400">Por Vencer</CardTitle>
                    <Clock class="size-4 text-yellow-600 dark:text-yellow-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">{formatCOP(stats.por_vencer)}</p>
                    <p class="text-xs text-muted-foreground mt-1">Pendiente dentro del plazo</p>
                </CardContent>
            </Card>
        </div>

        <!-- Filtros -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Filtros</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap gap-3">
                    <div class="relative min-w-56 flex-1">
                        <Search class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                        <Input
                            class="pl-8"
                            placeholder="Buscar por nombre de cliente..."
                            bind:value={search}
                            onkeydown={handleSearchKeydown}
                        />
                    </div>

                    <Select bind:value={estadoFiltro} onValueChange={applyFilters}>
                        <SelectTrigger class="w-36">
                            {estadoFiltro ? estadoLabel(estadoFiltro) : 'Estado'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todos los estados</SelectItem>
                            <SelectItem value="pendiente">Pendiente</SelectItem>
                            <SelectItem value="parcial">Parcial</SelectItem>
                            <SelectItem value="vencida">Vencida</SelectItem>
                        </SelectContent>
                    </Select>

                    <label class="flex cursor-pointer items-center gap-2 rounded-md border px-3 py-2 text-sm hover:bg-muted/50 transition-colors">
                        <input
                            type="checkbox"
                            class="size-4 rounded"
                            bind:checked={soloVencidas}
                            onchange={applyFilters}
                        />
                        Solo vencidas
                    </label>

                    <Button onclick={applyFilters}>
                        <Search class="mr-2 size-4" />
                        Buscar
                    </Button>
                    <Button variant="outline" onclick={clearFilters}>Limpiar</Button>
                </div>
            </CardContent>
        </Card>

        <!-- Tabla de cartera -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cliente</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Factura</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Monto Total</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Pagado</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Saldo</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Vencimiento</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Días Vencida</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each cuentas.data as cuenta (cuenta.id)}
                                {@const diasVencida = getDiasVencida(cuenta.fecha_vencimiento)}
                                {@const vencida = isVencida(cuenta.fecha_vencimiento)}
                                <tr class={['hover:bg-muted/30 transition-colors', vencida ? 'bg-destructive/5' : ''].join(' ')}>
                                    <td class="px-4 py-3">
                                        {#if cuenta.cliente}
                                            <div class="flex items-center gap-2">
                                                {#if cuenta.cliente.tipo === 'natural'}
                                                    <User class="size-3.5 text-muted-foreground shrink-0" />
                                                {:else}
                                                    <Building2 class="size-3.5 text-muted-foreground shrink-0" />
                                                {/if}
                                                <div>
                                                    <a
                                                        href={`/clientes/${cuenta.cliente.id}`}
                                                        class="font-medium hover:underline"
                                                    >
                                                        {cuenta.cliente.nombre}
                                                    </a>
                                                    <p class="text-xs text-muted-foreground font-mono">
                                                        {cuenta.cliente.identificacion}
                                                    </p>
                                                </div>
                                            </div>
                                        {:else}
                                            <span class="text-muted-foreground">—</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        {cuenta.factura?.numero ?? (cuenta.factura_id ? `#${cuenta.factura_id}` : '—')}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">{formatCOP(cuenta.monto_total)}</td>
                                    <td class="px-4 py-3 text-right text-green-600">{formatCOP(cuenta.monto_pagado)}</td>
                                    <td class="px-4 py-3 text-right font-bold {vencida ? 'text-destructive' : ''}">
                                        {formatCOP(cuenta.saldo ?? cuenta.monto_total - cuenta.monto_pagado)}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-1.5">
                                            {#if vencida}
                                                <AlertCircle class="size-3.5 text-destructive shrink-0" />
                                            {:else}
                                                <Clock class="size-3.5 text-muted-foreground shrink-0" />
                                            {/if}
                                            <span class="{vencida ? 'text-destructive' : 'text-muted-foreground'} text-xs">
                                                {formatDate(cuenta.fecha_vencimiento)}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant={estadoBadgeVariant(cuenta.estado)}>
                                            {estadoLabel(cuenta.estado)}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        {#if diasVencida > 0}
                                            <span class="font-medium text-destructive">{diasVencida}d</span>
                                        {:else}
                                            <span class="text-muted-foreground">—</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-1">
                                            {#if cuenta.cliente}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/clientes/${cuenta.cliente.id}`}
                                                    title="Ver cliente"
                                                >
                                                    <User class="size-4" />
                                                </Button>
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="9" class="px-4 py-12 text-center text-muted-foreground">
                                        <CheckCircle class="mx-auto mb-2 size-8 opacity-30" />
                                        No hay cuentas por cobrar con los filtros aplicados.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                {#if cuentas.meta && cuentas.meta.last_page > 1}
                    <div class="flex items-center justify-between border-t px-4 py-3">
                        <p class="text-sm text-muted-foreground">
                            Mostrando {cuentas.meta.from} – {cuentas.meta.to} de {cuentas.meta.total} cuentas
                        </p>
                        <div class="flex gap-1">
                            {#each cuentas.links as link, i (i)}
                                {#if link.url}
                                    <Button
                                        variant={link.active ? 'default' : 'outline'}
                                        size="sm"
                                        onclick={() => router.visit(link.url!)}
                                    >
                                        <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                                        {@html link.label}
                                    </Button>
                                {:else}
                                    <Button variant="outline" size="sm" disabled>
                                        <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                                        {@html link.label}
                                    </Button>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {/if}
            </CardContent>
        </Card>
    </div>
</AppLayout>
