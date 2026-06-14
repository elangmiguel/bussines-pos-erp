<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { ShoppingCart, Plus, Search } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import type { BreadcrumbItem } from '@/types';

    interface Proveedor {
        id: number;
        tipo: string;
        persona: { nombres: string; apellidos: string } | null;
        empresa: { razon_social: string } | null;
    }

    interface Orden {
        id: number;
        fecha: string;
        fecha_esperada: string | null;
        estado: string;
        subtotal: number;
        iva: number;
        total: number;
        detalles_count: number;
        proveedor: Proveedor;
    }

    interface PaginatedOrdenes {
        data: Orden[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    }

    interface Filtros {
        estado?: string;
        search?: string;
        fecha_desde?: string;
        fecha_hasta?: string;
    }

    let {
        ordenes,
        filtros = {},
    }: {
        ordenes: PaginatedOrdenes;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Órdenes de Compra', href: '/compras/ordenes' },
    ];

    let search = $state(untrack(() => filtros.search ?? ''));
    let estado = $state(untrack(() => filtros.estado ?? ''));
    let fechaDesde = $state(untrack(() => filtros.fecha_desde ?? ''));
    let fechaHasta = $state(untrack(() => filtros.fecha_hasta ?? ''));

    const cop = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    });

    const estadoColors: Record<string, string> = {
        borrador:         'bg-gray-100 text-gray-700 border-gray-200',
        enviada:          'bg-blue-100 text-blue-700 border-blue-200',
        recibida_parcial: 'bg-yellow-100 text-yellow-700 border-yellow-200',
        recibida:         'bg-green-100 text-green-700 border-green-200',
        cancelada:        'bg-red-100 text-red-700 border-red-200',
    };

    const estadoLabels: Record<string, string> = {
        borrador:         'Borrador',
        enviada:          'Enviada',
        recibida_parcial: 'Recibida parcial',
        recibida:         'Recibida',
        cancelada:        'Cancelada',
    };

    function getProveedorNombre(p: Proveedor): string {
        if (p.tipo === 'natural' && p.persona) {
            return `${p.persona.nombres} ${p.persona.apellidos}`.trim();
        }
        return p.empresa?.razon_social ?? '—';
    }

    function applyFilters() {
        router.get('/compras/ordenes', {
            search: search || undefined,
            estado: estado || undefined,
            fecha_desde: fechaDesde || undefined,
            fecha_hasta: fechaHasta || undefined,
        }, { preserveScroll: true, replace: true });
    }

    function clearFilters() {
        search = '';
        estado = '';
        fechaDesde = '';
        fechaHasta = '';
        router.get('/compras/ordenes', {}, { preserveScroll: true, replace: true });
    }
</script>

<AppHead title="Órdenes de Compra" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <ShoppingCart class="h-6 w-6 text-primary" />
                <h1 class="text-2xl font-bold">Órdenes de Compra</h1>
            </div>
            <Button href="/compras/ordenes/create">
                <Plus class="mr-2 h-4 w-4" />
                Nueva Orden
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                    class="pl-9"
                    placeholder="Buscar por proveedor..."
                    bind:value={search}
                    onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && applyFilters()}
                />
            </div>

            <select
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={estado}
                onchange={applyFilters}
            >
                <option value="">Todos los estados</option>
                <option value="borrador">Borrador</option>
                <option value="enviada">Enviada</option>
                <option value="recibida_parcial">Recibida parcial</option>
                <option value="recibida">Recibida</option>
                <option value="cancelada">Cancelada</option>
            </select>

            <Input
                type="date"
                bind:value={fechaDesde}
                class="w-auto"
                placeholder="Desde"
                onchange={applyFilters}
            />
            <Input
                type="date"
                bind:value={fechaHasta}
                class="w-auto"
                placeholder="Hasta"
                onchange={applyFilters}
            />

            <Button variant="outline" onclick={applyFilters}>Filtrar</Button>
            <Button variant="ghost" onclick={clearFilters}>Limpiar</Button>
        </div>

        <!-- Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium"># OC</th>
                        <th class="px-4 py-3 text-left font-medium">Proveedor</th>
                        <th class="px-4 py-3 text-center font-medium">Fecha</th>
                        <th class="px-4 py-3 text-center font-medium">Fecha Esperada</th>
                        <th class="px-4 py-3 text-center font-medium">Ítems</th>
                        <th class="px-4 py-3 text-right font-medium">Total</th>
                        <th class="px-4 py-3 text-center font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    {#each ordenes.data as orden (orden.id)}
                        <tr
                            class="hover:bg-muted/30 transition-colors cursor-pointer"
                            onclick={() => router.visit(`/compras/ordenes/${orden.id}`)}
                        >
                            <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                OC-{String(orden.id).padStart(6, '0')}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {getProveedorNombre(orden.proveedor)}
                            </td>
                            <td class="px-4 py-3 text-center text-muted-foreground">
                                {new Date(orden.fecha).toLocaleDateString('es-CO')}
                            </td>
                            <td class="px-4 py-3 text-center text-muted-foreground">
                                {orden.fecha_esperada ? new Date(orden.fecha_esperada).toLocaleDateString('es-CO') : '—'}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {orden.detalles_count}
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                {cop.format(orden.total)}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Badge class={estadoColors[orden.estado] ?? ''}>
                                    {estadoLabels[orden.estado] ?? orden.estado}
                                </Badge>
                            </td>
                        </tr>
                    {:else}
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                No se encontraron órdenes de compra.
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if ordenes.meta.last_page > 1}
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    Mostrando {ordenes.meta.from} a {ordenes.meta.to} de {ordenes.meta.total} órdenes
                </span>
                <div class="flex gap-1">
                    {#each ordenes.links as link, i (i)}
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
