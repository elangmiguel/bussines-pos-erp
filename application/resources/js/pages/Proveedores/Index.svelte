<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Search, Plus, Edit, Trash2, Truck } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import type { BreadcrumbItem } from '@/types';

    interface Persona {
        tipo_identificacion: string;
        numero_identificacion: string;
        nombres: string;
        apellidos: string;
        ciudad: string | null;
    }

    interface Empresa {
        razon_social: string;
        nit: string;
        digito_verificacion: string;
        ciudad: string | null;
    }

    interface Proveedor {
        id: number;
        tipo: 'natural' | 'juridico';
        plazo_dias: number;
        activo: boolean;
        persona: Persona | null;
        empresa: Empresa | null;
    }

    interface PaginatedProveedores {
        data: Proveedor[];
        links: { url: string | null; label: string; active: boolean }[];
        meta: { current_page: number; last_page: number; total: number; from: number; to: number };
    }

    interface Filtros {
        search?: string;
        tipo?: string;
        activo?: string;
    }

    let {
        proveedores,
        filtros = {},
    }: {
        proveedores: PaginatedProveedores;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Proveedores', href: '/proveedores' },
    ];

    let search = $state(untrack(() => filtros.search ?? ''));
    let tipo = $state(untrack(() => filtros.tipo ?? ''));
    let activo = $state(untrack(() => filtros.activo ?? ''));

    function applyFilters() {
        router.get('/proveedores', {
            search: search || undefined,
            tipo: tipo || undefined,
            activo: activo || undefined,
        }, { preserveScroll: true, replace: true });
    }

    function clearFilters() {
        search = '';
        tipo = '';
        activo = '';
        router.get('/proveedores', {}, { preserveScroll: true, replace: true });
    }

    function getNombre(p: Proveedor): string {
        if (p.tipo === 'natural' && p.persona) {
            return `${p.persona.nombres} ${p.persona.apellidos}`.trim();
        }
        return p.empresa?.razon_social ?? '—';
    }

    function getIdentificacion(p: Proveedor): string {
        if (p.tipo === 'natural' && p.persona) {
            return `${p.persona.tipo_identificacion}: ${p.persona.numero_identificacion}`;
        }
        if (p.empresa) {
            return `NIT: ${p.empresa.nit}-${p.empresa.digito_verificacion}`;
        }
        return '—';
    }

    function getCiudad(p: Proveedor): string {
        return (p.tipo === 'natural' ? p.persona?.ciudad : p.empresa?.ciudad) ?? '—';
    }

    function confirmDelete(proveedor: Proveedor) {
        if (confirm(`¿Está seguro de eliminar el proveedor "${getNombre(proveedor)}"?`)) {
            router.delete(`/proveedores/${proveedor.id}`);
        }
    }
</script>

<AppHead title="Proveedores" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Truck class="h-6 w-6 text-primary" />
                <h1 class="text-2xl font-bold">Proveedores</h1>
            </div>
            <Button href="/proveedores/create">
                <Plus class="mr-2 h-4 w-4" />
                Nuevo Proveedor
            </Button>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[200px]">
                <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                    class="pl-9"
                    placeholder="Buscar por nombre, NIT o identificación..."
                    bind:value={search}
                    onkeydown={(e: KeyboardEvent) => e.key === 'Enter' && applyFilters()}
                />
            </div>

            <select
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={tipo}
                onchange={applyFilters}
            >
                <option value="">Todos los tipos</option>
                <option value="natural">Persona Natural</option>
                <option value="juridico">Persona Jurídica</option>
            </select>

            <select
                class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                bind:value={activo}
                onchange={applyFilters}
            >
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
            </select>

            <Button variant="outline" onclick={applyFilters}>Filtrar</Button>
            <Button variant="ghost" onclick={clearFilters}>Limpiar</Button>
        </div>

        <!-- Table -->
        <div class="rounded-lg border shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium">Identificación</th>
                        <th class="px-4 py-3 text-left font-medium">Nombre / Razón Social</th>
                        <th class="px-4 py-3 text-left font-medium">Tipo</th>
                        <th class="px-4 py-3 text-left font-medium">Ciudad</th>
                        <th class="px-4 py-3 text-center font-medium">Plazo días</th>
                        <th class="px-4 py-3 text-center font-medium">Estado</th>
                        <th class="px-4 py-3 text-center font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    {#each proveedores.data as proveedor (proveedor.id)}
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="px-4 py-3 text-muted-foreground font-mono text-xs">
                                {getIdentificacion(proveedor)}
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {getNombre(proveedor)}
                            </td>
                            <td class="px-4 py-3">
                                {#if proveedor.tipo === 'natural'}
                                    <Badge variant="secondary">Natural</Badge>
                                {:else}
                                    <Badge variant="outline">Jurídico</Badge>
                                {/if}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {getCiudad(proveedor)}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {proveedor.plazo_dias} días
                            </td>
                            <td class="px-4 py-3 text-center">
                                {#if proveedor.activo}
                                    <Badge class="bg-green-100 text-green-800 border-green-200">Activo</Badge>
                                {:else}
                                    <Badge variant="destructive">Inactivo</Badge>
                                {/if}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        href={`/proveedores/${proveedor.id}`}
                                    >
                                        <Truck class="h-4 w-4" />
                                        <span class="sr-only">Ver</span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        href={`/proveedores/${proveedor.id}/edit`}
                                    >
                                        <Edit class="h-4 w-4" />
                                        <span class="sr-only">Editar</span>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onclick={() => confirmDelete(proveedor)}
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
                            <td colspan="7" class="px-4 py-12 text-center text-muted-foreground">
                                No se encontraron proveedores.
                            </td>
                        </tr>
                    {/each}
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        {#if proveedores.meta.last_page > 1}
            <div class="flex items-center justify-between text-sm text-muted-foreground">
                <span>
                    Mostrando {proveedores.meta.from} a {proveedores.meta.to} de {proveedores.meta.total} proveedores
                </span>
                <div class="flex gap-1">
                    {#each proveedores.links as link, i (i)}
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
