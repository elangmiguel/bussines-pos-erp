<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import Calendar from 'lucide-svelte/icons/calendar';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import Lock from 'lucide-svelte/icons/lock';
    import Plus from 'lucide-svelte/icons/plus';
    import Unlock from 'lucide-svelte/icons/unlock';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { formatCOP, formatFechaHora } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface CajaRef {
        id: number;
        nombre: string;
    }

    interface Turno {
        id: number;
        caja: CajaRef;
        cajero_nombre: string;
        apertura: string;
        cierre: string | null;
        estado: 'abierto' | 'cerrado';
        monto_apertura: number | string;
        monto_cierre: number | string | null;
        diferencia: number | string | null;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedTurnos {
        data: Turno[];
        links: PaginationLink[];
        meta: {
            current_page: number;
            last_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
    }

    interface Filtros {
        caja_id: string;
        estado: string;
        fecha_desde: string;
        fecha_hasta: string;
    }

    let {
        turnos,
        cajas,
        filtros,
    }: {
        turnos: PaginatedTurnos;
        cajas: Array<{ id: number; nombre: string }>;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Caja', href: '/caja' },
        { title: 'Turnos', href: '/caja/turnos' },
    ];

    let cajaId = $state(untrack(() => filtros.caja_id ?? ''));
    let estado = $state(untrack(() => filtros.estado ?? ''));
    let fechaDesde = $state(untrack(() => filtros.fecha_desde ?? ''));
    let fechaHasta = $state(untrack(() => filtros.fecha_hasta ?? ''));

    function applyFilters() {
        const params: Record<string, string> = {};
        if (cajaId) params.caja_id = cajaId;
        if (estado) params.estado = estado;
        if (fechaDesde) params.fecha_desde = fechaDesde;
        if (fechaHasta) params.fecha_hasta = fechaHasta;
        router.get('/caja/turnos', params, { preserveState: true });
    }

    function clearFilters() {
        cajaId = '';
        estado = '';
        fechaDesde = '';
        fechaHasta = '';
        router.get('/caja/turnos', {}, { preserveState: true });
    }

    function numVal(v: number | string | null | undefined): number {
        if (v === null || v === undefined) return 0;
        return Number(v);
    }

    function diferenciaColor(diferencia: number | string | null): string {
        const val = numVal(diferencia);
        if (val < 0) return 'text-destructive font-semibold';
        if (val > 0) return 'text-green-600 font-semibold';
        return 'text-muted-foreground';
    }

    function cajaNombre(id: string): string {
        return cajas.find(c => String(c.id) === id)?.nombre ?? 'Todas las cajas';
    }

    function estadoLabel(e: string): string {
        const map: Record<string, string> = { abierto: 'Abierto', cerrado: 'Cerrado', '': 'Todos' };
        return map[e] ?? e;
    }
</script>

<AppHead title="Turnos de Caja" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Turnos de Caja</h1>
                <p class="text-sm text-muted-foreground">
                    Historial de aperturas y cierres de turno
                </p>
            </div>
            <Button href="/caja/turnos/abrir">
                <Plus class="mr-2 size-4" />
                Abrir Turno
            </Button>
        </div>

        <!-- Filtros -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Filtros</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-1.5">
                        <Label>Caja</Label>
                        <Select value={cajaId} onValueChange={(v) => { cajaId = v; applyFilters(); }}>
                            <SelectTrigger class="w-full">
                                {cajaId ? cajaNombre(cajaId) : 'Todas las cajas'}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Todas las cajas</SelectItem>
                                {#each cajas as caja (caja.id)}
                                    <SelectItem value={String(caja.id)}>{caja.nombre}</SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5">
                        <Label>Estado</Label>
                        <Select value={estado} onValueChange={(v) => { estado = v; applyFilters(); }}>
                            <SelectTrigger class="w-full">
                                {estadoLabel(estado)}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Todos</SelectItem>
                                <SelectItem value="abierto">Abierto</SelectItem>
                                <SelectItem value="cerrado">Cerrado</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="space-y-1.5">
                        <Label>Fecha desde</Label>
                        <div class="relative">
                            <Calendar class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                            <Input
                                type="date"
                                class="pl-8"
                                bind:value={fechaDesde}
                                onchange={applyFilters}
                            />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label>Fecha hasta</Label>
                        <div class="relative">
                            <Calendar class="absolute left-2.5 top-2.5 size-4 text-muted-foreground" />
                            <Input
                                type="date"
                                class="pl-8"
                                bind:value={fechaHasta}
                                onchange={applyFilters}
                            />
                        </div>
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <Button variant="outline" size="sm" onclick={clearFilters}>
                        Limpiar filtros
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Tabla -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">#</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Caja</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cajero</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Apertura</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Cierre</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Monto Apertura</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Monto Cierre</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Diferencia</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each turnos.data as turno (turno.id)}
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        #{turno.id}
                                    </td>
                                    <td class="px-4 py-3 font-medium">{turno.caja.nombre}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{turno.cajero_nombre}</td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {formatFechaHora(turno.apertura)}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-muted-foreground">
                                        {turno.cierre ? formatFechaHora(turno.cierre) : '—'}
                                    </td>
                                    <td class="px-4 py-3">
                                        {#if turno.estado === 'abierto'}
                                            <Badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200">
                                                <Unlock class="mr-1 size-3" />
                                                Abierto
                                            </Badge>
                                        {:else}
                                            <Badge variant="secondary">
                                                <Lock class="mr-1 size-3" />
                                                Cerrado
                                            </Badge>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        {formatCOP(numVal(turno.monto_apertura))}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        {turno.monto_cierre !== null ? formatCOP(numVal(turno.monto_cierre)) : '—'}
                                    </td>
                                    <td class="px-4 py-3 text-right {diferenciaColor(turno.diferencia)}">
                                        {turno.diferencia !== null ? formatCOP(numVal(turno.diferencia)) : '—'}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/caja/turnos/${turno.id}`}
                                                title="Ver detalle"
                                            >
                                                <ChevronRight class="size-4" />
                                            </Button>
                                            {#if turno.estado === 'abierto'}
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    href={`/caja/turnos/${turno.id}/cerrar`}
                                                    title="Cerrar turno"
                                                >
                                                    <Lock class="mr-1 size-3" />
                                                    Cerrar
                                                </Button>
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="10" class="px-4 py-12 text-center text-muted-foreground">
                                        No hay turnos registrados con los filtros aplicados.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                {#if turnos.meta && turnos.meta.last_page > 1}
                    <div class="flex items-center justify-between border-t px-4 py-3">
                        <p class="text-sm text-muted-foreground">
                            {#if turnos.meta.from && turnos.meta.to}
                                Mostrando {turnos.meta.from} – {turnos.meta.to} de {turnos.meta.total} turnos
                            {:else}
                                {turnos.meta.total} turnos
                            {/if}
                        </p>
                        <div class="flex gap-1">
                            {#each turnos.links as link, i (i)}
                                <Button
                                    variant={link.active ? 'default' : 'outline'}
                                    size="sm"
                                    disabled={!link.url}
                                    onclick={() => link.url && router.visit(link.url)}
                                >
                                    <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                                    {@html link.label}
                                </Button>
                            {/each}
                        </div>
                    </div>
                {/if}
            </CardContent>
        </Card>
    </div>
</AppLayout>
