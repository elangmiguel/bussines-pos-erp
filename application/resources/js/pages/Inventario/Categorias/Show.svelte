<script lang="ts">
    import FolderOpen from 'lucide-svelte/icons/folder-open';
    import Package from 'lucide-svelte/icons/package';
    import Pencil from 'lucide-svelte/icons/pencil';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import { formatCOP } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface ParentRef {
        id: number;
        nombre: string;
    }

    interface ChildCategoria {
        id: number;
        nombre: string;
        activo: boolean;
        productos_count: number;
    }

    interface ProductoRef {
        id: number;
        codigo: string | null;
        nombre: string;
        stock_actual: number | string;
        precio_venta: number | string;
        activo: boolean;
    }

    interface Categoria {
        id: number;
        nombre: string;
        descripcion: string | null;
        activo: boolean;
        parent: ParentRef | null;
        children: ChildCategoria[];
        productos: ProductoRef[];
    }

    let {
        categoria,
    }: {
        categoria: Categoria;
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Inventario', href: '/inventario/productos' },
        { title: 'Categorías', href: '/inventario/categorias' },
        { title: categoria.nombre, href: `/inventario/categorias/${categoria.id}` },
    ]);

    const STOCK_MINIMO_UI = 5;

    function numVal(v: number | string | null | undefined): number {
        if (v === null || v === undefined) return 0;
        return Number(v);
    }

    function stockColor(stock: number | string): string {
        const val = numVal(stock);
        if (val <= 0) return 'text-destructive font-semibold';
        if (val <= STOCK_MINIMO_UI) return 'text-orange-600 dark:text-orange-400 font-semibold';
        return 'text-foreground';
    }
</script>

<AppHead title="Categoría — {categoria.nombre}" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-muted">
                    <FolderOpen class="size-5 text-muted-foreground" />
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold">{categoria.nombre}</h1>
                        {#if categoria.activo}
                            <Badge class="bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100">
                                Activo
                            </Badge>
                        {:else}
                            <Badge variant="secondary">Inactivo</Badge>
                        {/if}
                    </div>
                    {#if categoria.descripcion}
                        <p class="text-sm text-muted-foreground mt-0.5">{categoria.descripcion}</p>
                    {/if}
                    {#if categoria.parent}
                        <p class="mt-1 text-sm text-muted-foreground">
                            Categoría padre:
                            <a
                                href={`/inventario/categorias/${categoria.parent.id}`}
                                class="font-medium text-foreground hover:underline"
                            >
                                {categoria.parent.nombre}
                            </a>
                        </p>
                    {/if}
                </div>
            </div>
            <Button href={`/inventario/categorias/${categoria.id}/edit`}>
                <Pencil class="mr-2 size-4" />
                Editar
            </Button>
        </div>

        <!-- Subcategorías -->
        {#if categoria.children.length > 0}
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">
                        Subcategorías
                        <Badge variant="outline" class="ml-2">{categoria.children.length}</Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Nombre</th>
                                    <th class="px-4 py-2.5 text-center font-medium text-muted-foreground">Productos</th>
                                    <th class="px-4 py-2.5 text-center font-medium text-muted-foreground">Estado</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                {#each categoria.children as child (child.id)}
                                    <tr class="hover:bg-muted/30 transition-colors">
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <FolderOpen class="size-4 shrink-0 text-muted-foreground" />
                                                <span class="font-medium">{child.nombre}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            <Badge variant="secondary">{child.productos_count}</Badge>
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            {#if child.activo}
                                                <Badge class="bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100">
                                                    Activo
                                                </Badge>
                                            {:else}
                                                <Badge variant="secondary">Inactivo</Badge>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/inventario/categorias/${child.id}`}
                                            >
                                                Ver
                                            </Button>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        {/if}

        <Separator />

        <!-- Productos -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">
                    <div class="flex items-center gap-2">
                        <Package class="size-4" />
                        Productos en esta categoría
                        <Badge variant="outline" class="ml-1">{categoria.productos.length}</Badge>
                    </div>
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                {#if categoria.productos.length === 0}
                    <p class="px-4 py-10 text-center text-sm text-muted-foreground">
                        No hay productos asignados a esta categoría.
                    </p>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="border-b bg-muted/50">
                                <tr>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Código</th>
                                    <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Nombre</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Stock</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Precio venta</th>
                                    <th class="px-4 py-2.5 text-center font-medium text-muted-foreground">Estado</th>
                                    <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                {#each categoria.productos as producto (producto.id)}
                                    <tr class="hover:bg-muted/30 transition-colors">
                                        <td class="px-4 py-2.5 font-mono text-xs text-muted-foreground">
                                            {producto.codigo ?? '—'}
                                        </td>
                                        <td class="px-4 py-2.5 font-medium">{producto.nombre}</td>
                                        <td class="px-4 py-2.5 text-right {stockColor(producto.stock_actual)}">
                                            {numVal(producto.stock_actual)}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-medium">
                                            {formatCOP(numVal(producto.precio_venta))}
                                        </td>
                                        <td class="px-4 py-2.5 text-center">
                                            {#if producto.activo}
                                                <Badge class="bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100">
                                                    Activo
                                                </Badge>
                                            {:else}
                                                <Badge variant="secondary">Inactivo</Badge>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-2.5 text-right">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/inventario/productos/${producto.id}`}
                                            >
                                                Ver
                                            </Button>
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
