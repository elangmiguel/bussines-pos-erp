<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import {
        Search,
        FileText,
        Download,
        Printer,
        XCircle,
        CheckCircle,
        Clock,
        AlertTriangle,
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
    } from '@/components/ui/card';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { BreadcrumbItem } from '@/types';

    interface PersonaRef {
        nombre: string;
        apellido?: string | null;
    }

    interface EmpresaRef {
        razon_social: string;
    }

    interface ClienteRef {
        id: number;
        tipo: 'natural' | 'juridico';
        nombre: string;
        identificacion: string;
        persona?: PersonaRef | null;
        empresa?: EmpresaRef | null;
    }

    interface UserRef {
        id: number;
        name: string;
    }

    interface Factura {
        id: number;
        numero_completo: string;
        fecha: string;
        tipo_pago: 'contado' | 'credito';
        total: number;
        estado: 'borrador' | 'emitida' | 'anulada';
        estado_dian: 'pendiente' | 'aceptada' | 'rechazada';
        cliente: ClienteRef | null;
        user: UserRef | null;
    }

    interface PaginatedFacturas {
        data: Factura[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: {
            current_page: number;
            last_page: number;
            total: number;
            from: number;
            to: number;
        };
    }

    interface Filtros {
        search: string;
        estado: string;
        estado_dian: string;
        tipo_pago: string;
        fecha_desde: string;
        fecha_hasta: string;
    }

    interface Stats {
        total_dia: number;
        count_dia: number;
        total_mes: number;
        count_mes: number;
    }

    let {
        facturas,
        filtros,
        stats,
    }: {
        facturas: PaginatedFacturas;
        filtros: Filtros;
        stats: Stats;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Facturación', href: '/facturacion' },
    ];

    let search      = $state(untrack(() => filtros.search ?? ''));
    let estado      = $state(untrack(() => filtros.estado ?? ''));
    let estadoDian  = $state(untrack(() => filtros.estado_dian ?? ''));
    let tipoPago    = $state(untrack(() => filtros.tipo_pago ?? ''));
    let fechaDesde  = $state(untrack(() => filtros.fecha_desde ?? ''));
    let fechaHasta  = $state(untrack(() => filtros.fecha_hasta ?? ''));
    let anularId    = $state<number | null>(null);
    let anularNumero = $state('');

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

    function applyFilters() {
        const params: Record<string, string> = {};
        if (search)     params.search      = search;
        if (estado)     params.estado      = estado;
        if (estadoDian) params.estado_dian = estadoDian;
        if (tipoPago)   params.tipo_pago   = tipoPago;
        if (fechaDesde) params.fecha_desde = fechaDesde;
        if (fechaHasta) params.fecha_hasta = fechaHasta;
        router.get('/facturacion', params, { preserveState: true, replace: true });
    }

    function clearFilters() {
        search      = '';
        estado      = '';
        estadoDian  = '';
        tipoPago    = '';
        fechaDesde  = '';
        fechaHasta  = '';
        router.get('/facturacion', {}, { preserveState: true, replace: true });
    }

    function handleSearchKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter') applyFilters();
    }

    function confirmAnular(factura: Factura) {
        anularId     = factura.id;
        anularNumero = factura.numero_completo;
    }

    function doAnular() {
        if (!anularId) return;
        router.patch(`/facturacion/${anularId}/anular`, {}, {
            onSuccess: () => { anularId = null; anularNumero = ''; },
        });
    }

    function cancelAnular() {
        anularId     = null;
        anularNumero = '';
    }

    function estadoBadgeClass(estado: string): string {
        if (estado === 'emitida')  return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        if (estado === 'anulada')  return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        return 'bg-gray-100 text-gray-700 dark:bg-gray-700/40 dark:text-gray-300';
    }

    function estadoLabel(e: string): string {
        if (e === 'emitida')  return 'Emitida';
        if (e === 'anulada')  return 'Anulada';
        if (e === 'borrador') return 'Borrador';
        return e;
    }

    function dianBadgeClass(ed: string): string {
        if (ed === 'aceptada')   return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        if (ed === 'rechazada')  return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    }

    function dianLabel(ed: string): string {
        if (ed === 'aceptada')  return 'Aceptada';
        if (ed === 'rechazada') return 'Rechazada';
        return 'Pendiente';
    }
</script>

<AppHead title="Facturación" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">

        <!-- KPI Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <Card class="border-green-400/40 bg-green-400/5">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-green-700 dark:text-green-400">Ventas hoy</CardTitle>
                    <FileText class="size-4 text-green-600 dark:text-green-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-400">{formatCOP(stats.total_dia)}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{stats.count_dia} factura{stats.count_dia !== 1 ? 's' : ''}</p>
                </CardContent>
            </Card>

            <Card class="border-blue-400/40 bg-blue-400/5">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-blue-700 dark:text-blue-400">Facturas hoy</CardTitle>
                    <CheckCircle class="size-4 text-blue-600 dark:text-blue-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">{stats.count_dia}</p>
                    <p class="mt-1 text-xs text-muted-foreground">documentos emitidos</p>
                </CardContent>
            </Card>

            <Card class="border-violet-400/40 bg-violet-400/5">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-violet-700 dark:text-violet-400">Ventas del mes</CardTitle>
                    <Clock class="size-4 text-violet-600 dark:text-violet-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-violet-700 dark:text-violet-400">{formatCOP(stats.total_mes)}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{stats.count_mes} factura{stats.count_mes !== 1 ? 's' : ''}</p>
                </CardContent>
            </Card>

            <Card class="border-orange-400/40 bg-orange-400/5">
                <CardHeader class="flex flex-row items-center justify-between pb-2">
                    <CardTitle class="text-sm font-medium text-orange-700 dark:text-orange-400">Facturas del mes</CardTitle>
                    <AlertTriangle class="size-4 text-orange-600 dark:text-orange-400" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold text-orange-700 dark:text-orange-400">{stats.count_mes}</p>
                    <p class="mt-1 text-xs text-muted-foreground">documentos este mes</p>
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
                            placeholder="Número, cliente..."
                            bind:value={search}
                            onkeydown={handleSearchKeydown}
                        />
                    </div>

                    <Select bind:value={estado} onValueChange={applyFilters}>
                        <SelectTrigger class="w-36">
                            {estado ? estadoLabel(estado) : 'Estado'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todos</SelectItem>
                            <SelectItem value="emitida">Emitida</SelectItem>
                            <SelectItem value="borrador">Borrador</SelectItem>
                            <SelectItem value="anulada">Anulada</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select bind:value={estadoDian} onValueChange={applyFilters}>
                        <SelectTrigger class="w-36">
                            {estadoDian ? dianLabel(estadoDian) : 'DIAN'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todos</SelectItem>
                            <SelectItem value="pendiente">Pendiente</SelectItem>
                            <SelectItem value="aceptada">Aceptada</SelectItem>
                            <SelectItem value="rechazada">Rechazada</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select bind:value={tipoPago} onValueChange={applyFilters}>
                        <SelectTrigger class="w-36">
                            {tipoPago === 'contado' ? 'Contado' : tipoPago === 'credito' ? 'Crédito' : 'Tipo pago'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todos</SelectItem>
                            <SelectItem value="contado">Contado</SelectItem>
                            <SelectItem value="credito">Crédito</SelectItem>
                        </SelectContent>
                    </Select>

                    <Input
                        type="date"
                        class="w-40"
                        placeholder="Desde"
                        bind:value={fechaDesde}
                        onchange={applyFilters}
                    />
                    <Input
                        type="date"
                        class="w-40"
                        placeholder="Hasta"
                        bind:value={fechaHasta}
                        onchange={applyFilters}
                    />

                    <Button onclick={applyFilters}>
                        <Search class="mr-2 size-4" />
                        Buscar
                    </Button>
                    <Button variant="outline" onclick={clearFilters}>Limpiar</Button>
                </div>
            </CardContent>
        </Card>

        <!-- Tabla de facturas -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Número</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cliente</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo Pago</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">DIAN</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each facturas.data as factura (factura.id)}
                                <tr class="transition-colors hover:bg-muted/30">
                                    <td class="px-4 py-3">
                                        <a
                                            href={`/facturacion/${factura.id}`}
                                            class="font-mono font-medium text-primary hover:underline"
                                        >
                                            {factura.numero_completo}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {formatDate(factura.fecha)}
                                    </td>
                                    <td class="px-4 py-3">
                                        {#if factura.cliente}
                                            <p class="font-medium">{factura.cliente.nombre}</p>
                                            <p class="font-mono text-xs text-muted-foreground">{factura.cliente.identificacion}</p>
                                        {:else}
                                            <span class="text-muted-foreground">Consumidor Final</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        {formatCOP(factura.total)}
                                    </td>
                                    <td class="px-4 py-3 capitalize text-muted-foreground">
                                        {factura.tipo_pago}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${estadoBadgeClass(factura.estado)}`}>
                                            {estadoLabel(factura.estado)}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class={`inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ${dianBadgeClass(factura.estado_dian)}`}>
                                            {dianLabel(factura.estado_dian)}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/facturacion/${factura.id}`}
                                                title="Ver detalle"
                                            >
                                                <FileText class="size-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/facturacion/${factura.id}/pdf`}
                                                title="Descargar PDF"
                                            >
                                                <Printer class="size-4" />
                                            </Button>
                                            {#if factura.estado_dian !== 'pendiente' || factura.xml_dian}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/facturacion/${factura.id}/xml`}
                                                    title="Descargar XML"
                                                >
                                                    <Download class="size-4" />
                                                </Button>
                                            {/if}
                                            {#if factura.estado === 'emitida'}
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="text-destructive hover:text-destructive"
                                                    title="Anular factura"
                                                    onclick={() => confirmAnular(factura)}
                                                >
                                                    <XCircle class="size-4" />
                                                </Button>
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-muted-foreground">
                                        <FileText class="mx-auto mb-2 size-8 opacity-30" />
                                        No hay facturas con los filtros aplicados.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                {#if facturas.meta && facturas.meta.last_page > 1}
                    <div class="flex items-center justify-between border-t px-4 py-3">
                        <p class="text-sm text-muted-foreground">
                            Mostrando {facturas.meta.from} – {facturas.meta.to} de {facturas.meta.total} facturas
                        </p>
                        <div class="flex gap-1">
                            {#each facturas.links as link, i (i)}
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

<!-- Modal de confirmación de anulación -->
{#if anularId !== null}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-sm rounded-lg bg-background p-6 shadow-xl">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-destructive/10">
                    <AlertTriangle class="size-5 text-destructive" />
                </div>
                <div>
                    <h3 class="font-semibold">Anular factura</h3>
                    <p class="text-sm text-muted-foreground">{anularNumero}</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-muted-foreground">
                Esta acción revertirá el stock de los productos y no se puede deshacer.
                ¿Desea continuar?
            </p>
            <div class="flex justify-end gap-2">
                <Button variant="outline" onclick={cancelAnular}>Cancelar</Button>
                <Button variant="destructive" onclick={doAnular}>Anular factura</Button>
            </div>
        </div>
    </div>
{/if}
