<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import FolderOpen from 'lucide-svelte/icons/folder-open';
    import Pencil from 'lucide-svelte/icons/pencil';
    import Plus from 'lucide-svelte/icons/plus';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import type { BreadcrumbItem } from '@/types';

    interface ChildCategoria {
        id: number;
        nombre: string;
        activo: boolean;
        productos_count: number;
    }

    interface Categoria {
        id: number;
        nombre: string;
        descripcion: string | null;
        activo: boolean;
        parent_id: number | null;
        productos_count: number;
        children: ChildCategoria[];
    }

    let {
        categorias,
    }: {
        categorias: Categoria[];
        todasCategorias: Array<{ id: number; nombre: string }>;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventario', href: '/inventario/productos' },
        { title: 'Categorías', href: '/inventario/categorias' },
    ];

    let expandedRows = $state<Record<number, boolean>>({});

    function toggleRow(id: number) {
        expandedRows[id] = !expandedRows[id];
    }

    function confirmarEliminar(id: number, nombre: string) {
        if (confirm(`¿Desactivar la categoría "${nombre}"? Los productos asociados mantendrán su categoría.`)) {
            router.delete(`/inventario/categorias/${id}`, {
                preserveScroll: true,
            });
        }
    }

    const rootCategorias = $derived(categorias.filter(c => c.parent_id === null));
</script>

<AppHead title="Categorías de Inventario" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Categorías</h1>
                <p class="text-sm text-muted-foreground">
                    Organización jerárquica de productos por categoría
                </p>
            </div>
            <Button href="/inventario/categorias/create">
                <Plus class="mr-2 size-4" />
                Nueva Categoría
            </Button>
        </div>

        <!-- Tabla -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nombre</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Descripción</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Subcategorías</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Productos</th>
                                <th class="px-4 py-3 text-center font-medium text-muted-foreground">Activo</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each rootCategorias as cat (cat.id)}
                                <!-- Fila principal -->
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            {#if cat.children.length > 0}
                                                <button
                                                    type="button"
                                                    onclick={() => toggleRow(cat.id)}
                                                    class="flex size-5 items-center justify-center rounded text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
                                                >
                                                    {#if expandedRows[cat.id]}
                                                        <ChevronDown class="size-4" />
                                                    {:else}
                                                        <ChevronRight class="size-4" />
                                                    {/if}
                                                </button>
                                            {:else}
                                                <FolderOpen class="size-4 shrink-0 text-muted-foreground" />
                                            {/if}
                                            <span class="font-medium">{cat.nombre}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {cat.descripcion ?? '—'}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {#if cat.children.length > 0}
                                            <Badge variant="outline">{cat.children.length}</Badge>
                                        {:else}
                                            <span class="text-muted-foreground">—</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <Badge variant="secondary">{cat.productos_count}</Badge>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        {#if cat.activo}
                                            <Badge class="bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100">
                                                Activo
                                            </Badge>
                                        {:else}
                                            <Badge variant="secondary">Inactivo</Badge>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/inventario/categorias/${cat.id}`}
                                                title="Ver categoría"
                                            >
                                                <FolderOpen class="size-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/inventario/categorias/${cat.id}/edit`}
                                                title="Editar"
                                            >
                                                <Pencil class="size-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                                title="Desactivar"
                                                onclick={() => confirmarEliminar(cat.id, cat.nombre)}
                                            >
                                                <Trash2 class="size-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Filas de subcategorías expandibles -->
                                {#if expandedRows[cat.id] && cat.children.length > 0}
                                    {#each cat.children as child (child.id)}
                                        <tr class="bg-muted/20 hover:bg-muted/40 transition-colors">
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center gap-2 pl-7">
                                                    <FolderOpen class="size-3.5 shrink-0 text-muted-foreground" />
                                                    <span class="text-muted-foreground">{child.nombre}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-2.5 text-muted-foreground text-xs">—</td>
                                            <td class="px-4 py-2.5 text-center text-muted-foreground">—</td>
                                            <td class="px-4 py-2.5 text-center">
                                                <Badge variant="secondary" class="text-xs">{child.productos_count}</Badge>
                                            </td>
                                            <td class="px-4 py-2.5 text-center">
                                                {#if child.activo}
                                                    <Badge class="bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100 text-xs">
                                                        Activo
                                                    </Badge>
                                                {:else}
                                                    <Badge variant="secondary" class="text-xs">Inactivo</Badge>
                                                {/if}
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center justify-end gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        href={`/inventario/categorias/${child.id}`}
                                                        title="Ver"
                                                    >
                                                        <FolderOpen class="size-3.5" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        href={`/inventario/categorias/${child.id}/edit`}
                                                        title="Editar"
                                                    >
                                                        <Pencil class="size-3.5" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="text-destructive hover:text-destructive hover:bg-destructive/10"
                                                        title="Desactivar"
                                                        onclick={() => confirmarEliminar(child.id, child.nombre)}
                                                    >
                                                        <Trash2 class="size-3.5" />
                                                    </Button>
                                                </div>
                                            </td>
                                        </tr>
                                    {/each}
                                {/if}
                            {:else}
                                <tr>
                                    <td colspan="6" class="px-4 py-12 text-center text-muted-foreground">
                                        No hay categorías registradas.
                                        <Button variant="link" href="/inventario/categorias/create">
                                            Crear la primera categoría
                                        </Button>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</AppLayout>
