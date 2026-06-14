<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import {
        DollarSign,
        Edit,
        FileText,
        Plus,
        Receipt,
        Search,
        Trash2,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { formatCOP, formatFecha } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface Categoria { nombre: string }
    interface Proveedor { nombre: string }
    interface MedioPago { nombre: string }
    interface User { name: string }

    interface Gasto {
        id: number;
        categoria: Categoria;
        descripcion: string;
        monto: number;
        iva: number;
        fecha: string;
        proveedor: Proveedor | null;
        medioPago: MedioPago | null;
        user: User;
        numero_documento: string | null;
        comprobante: string | null;
        total: number;
    }

    interface PaginatedGastos {
        data: Gasto[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    }

    interface Filtros {
        search?: string;
        categoria_id?: string;
        medio_pago_id?: string;
        fecha_desde?: string;
        fecha_hasta?: string;
    }

    interface Stats {
        total_mes: number;
        total_iva_mes: number;
        count_mes: number;
    }

    interface SelectOption { id: number; nombre: string }

    let {
        gastos,
        filtros = {},
        stats,
        categorias,
        medios_pago,
    }: {
        gastos: PaginatedGastos;
        filtros: Filtros;
        stats: Stats;
        categorias: SelectOption[];
        medios_pago: SelectOption[];
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Gastos', href: '/gastos' },
    ];

    let search       = $state(untrack(() => filtros.search ?? ''));
    let categoriaId  = $state(untrack(() => filtros.categoria_id ?? ''));
    let medioPagoId  = $state(untrack(() => filtros.medio_pago_id ?? ''));
    let fechaDesde   = $state(untrack(() => filtros.fecha_desde ?? ''));
    let fechaHasta   = $state(untrack(() => filtros.fecha_hasta ?? ''));
    let deleteDialog = $state<{ open: boolean; gasto: Gasto | null }>({ open: false, gasto: null });

    function applyFilters() {
        router.get('/gastos', {
            search:        search || undefined,
            categoria_id:  categoriaId || undefined,
            medio_pago_id: medioPagoId || undefined,
            fecha_desde:   fechaDesde || undefined,
            fecha_hasta:   fechaHasta || undefined,
        }, { preserveState: true, replace: true });
    }

    function clearFilters() {
        search      = '';
        categoriaId = '';
        medioPagoId = '';
        fechaDesde  = '';
        fechaHasta  = '';
        router.get('/gastos', {}, { preserveState: true, replace: true });
    }

    function openDelete(gasto: Gasto) {
        deleteDialog = { open: true, gasto };
    }

    function closeDelete() {
        deleteDialog = { open: false, gasto: null };
    }

    function confirmDelete() {
        if (deleteDialog.gasto) {
            router.delete(`/gastos/${deleteDialog.gasto.id}`, {
                onSuccess: () => closeDelete(),
            });
        }
    }
</script>

<AppHead title="Gastos" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Receipt class="h-6 w-6 text-primary" />
                <h1 class="text-2xl font-bold">Gastos</h1>
            </div>
            <Button href="/gastos/create">
                <Plus class="mr-2 h-4 w-4" />
                Registrar Gasto
            </Button>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                        <DollarSign class="h-4 w-4" />
                        Gastos del Mes
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{formatCOP(stats.total_mes)}</p>
                    <p class="text-xs text-muted-foreground mt-1">Subtotal sin IVA</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                        <FileText class="h-4 w-4" />
                        IVA del Mes
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{formatCOP(stats.total_iva_mes)}</p>
                    <p class="text-xs text-muted-foreground mt-1">IVA acumulado del mes</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                        <Receipt class="h-4 w-4" />
                        Número de Gastos
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{stats.count_mes}</p>
                    <p class="text-xs text-muted-foreground mt-1">Registros en el mes actual</p>
                </CardContent>
            </Card>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                    class="pl-9"
                    placeholder="Buscar por descripción o N° documento..."
                    bind:value={search}
                    onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && applyFilters()}
                />
            </div>

            <select
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={categoriaId}
                onchange={applyFilters}
            >
                <option value="">Todas las categorías</option>
                {#each categorias as cat (cat.id)}
                    <option value={String(cat.id)}>{cat.nombre}</option>
                {/each}
            </select>

            <select
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={medioPagoId}
                onchange={applyFilters}
            >
                <option value="">Todos los medios de pago</option>
                {#each medios_pago as mp (mp.id)}
                    <option value={String(mp.id)}>{mp.nombre}</option>
                {/each}
            </select>

            <div class="flex items-center gap-2">
                <Input
                    type="date"
                    class="h-9 w-auto"
                    bind:value={fechaDesde}
                    onchange={applyFilters}
                    placeholder="Desde"
                />
                <span class="text-muted-foreground text-sm">—</span>
                <Input
                    type="date"
                    class="h-9 w-auto"
                    bind:value={fechaHasta}
                    onchange={applyFilters}
                    placeholder="Hasta"
                />
            </div>

            <Button onclick={applyFilters}>Buscar</Button>
            <Button variant="ghost" onclick={clearFilters}>Limpiar</Button>
        </div>

        <!-- Table -->
        <div class="rounded-lg border shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Fecha</th>
                        <th class="px-4 py-3 text-left font-medium">Descripción</th>
                        <th class="px-4 py-3 text-left font-medium">Categoría</th>
                        <th class="px-4 py-3 text-right font-medium">Monto</th>
                        <th class="px-4 py-3 text-right font-medium">IVA</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-left font-medium">Proveedor</th>
                        <th class="px-4 py-3 text-left font-medium">Medio Pago</th>
                        <th class="px-4 py-3 text-left font-medium">N° Doc</th>
                        <th class="px-4 py-3 text-center font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    {#each gastos.data as gasto (gasto.id)}
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {formatFecha(gasto.fecha)}
                            </td>
                            <td class="px-4 py-3 max-w-[200px]">
                                <span class="line-clamp-2" title={gasto.descripcion}>
                                    {gasto.descripcion}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                {gasto.categoria?.nombre ?? '—'}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-mono">
                                {formatCOP(Number(gasto.monto))}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-mono text-muted-foreground">
                                {formatCOP(Number(gasto.iva))}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap font-mono font-semibold">
                                {formatCOP(Number(gasto.total))}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {gasto.proveedor?.nombre ?? '—'}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-muted-foreground">
                                {gasto.medioPago?.nombre ?? '—'}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-muted-foreground font-mono text-xs">
                                {gasto.numero_documento ?? '—'}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        href={`/gastos/${gasto.id}/edit`}
                                    >
                                        <Edit class="h-4 w-4" />
                                        <span class="sr-only">Editar</span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onclick={() => openDelete(gasto)}
                                        class="text-destructive hover:text-destructive"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span class="sr-only">Eliminar</span>
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    {:else}
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-muted-foreground">
                                No se encontraron gastos con los filtros aplicados.
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if gastos.meta.last_page > 1}
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    Mostrando {gastos.meta.from} a {gastos.meta.to} de {gastos.meta.total} gastos
                </span>
                <div class="flex gap-1">
                    {#each gastos.links as link, i (i)}
                        {#if link.url}
                            <Button
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                onclick={() => router.get(link.url!)}
                            >
                                {@html link.label}
                            </Button>
                        {:else}
                            <Button variant="outline" size="sm" disabled>
                                {@html link.label}
                            </Button>
                        {/if}
                    {/each}
                </div>
            </div>
        {/if}
    </div>
</AppLayout>

<!-- Delete Confirmation Dialog -->
{#if deleteDialog.open}
    <div
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
        role="dialog"
        aria-modal="true"
        aria-labelledby="delete-dialog-title"
    >
        <div class="bg-background rounded-lg border shadow-lg p-6 w-full max-w-md mx-4">
            <h2 id="delete-dialog-title" class="text-lg font-semibold mb-2">Eliminar gasto</h2>
            <p class="text-muted-foreground text-sm mb-6">
                ¿Está seguro de que desea eliminar el gasto
                <span class="font-medium text-foreground">"{deleteDialog.gasto?.descripcion}"</span>?
                Esta acción no se puede deshacer.
            </p>
            <div class="flex gap-3 justify-end">
                <Button variant="outline" onclick={closeDelete} type="button">Cancelar</Button>
                <Button variant="destructive" onclick={confirmDelete} type="button">
                    Eliminar
                </Button>
            </div>
        </div>
    </div>
{/if}
