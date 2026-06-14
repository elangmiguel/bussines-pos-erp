<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { AlertTriangle, Edit, Package, Plus, Search, Trash2 } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { BreadcrumbItem } from '@/types';

    interface Categoria {
        id: number;
        nombre: string;
    }

    interface UnidadMedida {
        id: number;
        nombre: string;
        abreviatura: string;
    }

    interface TarifaIva {
        id: number;
        nombre: string;
        porcentaje: string;
    }

    interface Producto {
        id: number;
        codigo: string | null;
        codigo_barras: string | null;
        nombre: string;
        categoria: Categoria | null;
        unidad_medida: UnidadMedida | null;
        tarifa_iva: TarifaIva | null;
        precio_venta: string;
        stock_actual: string;
        stock_minimo: string;
        activo: boolean;
    }

    interface PaginationLink {
        url: string | null;
        label: string;
        active: boolean;
    }

    interface PaginatedProductos {
        data: Producto[];
        links: PaginationLink[];
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
    }

    interface Filtros {
        search: string;
        categoria_id: string;
        bajo_stock: boolean;
        activo: string;
    }

    let {
        productos,
        categorias,
        filtros,
    }: {
        productos: PaginatedProductos;
        categorias: Categoria[];
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventario', href: '/inventario/productos' },
        { title: 'Productos', href: '/inventario/productos' },
    ];

    let searchValue = $state(untrack(() => filtros.search ?? ''));
    let categoriaId = $state(untrack(() => filtros.categoria_id ?? ''));
    let bajoStock   = $state(untrack(() => filtros.bajo_stock ?? false));
    let activoFilter = $state(untrack(() => filtros.activo ?? ''));

    let deleteDialogOpen = $state(false);
    let productoToDelete = $state<Producto | null>(null);

    let debounceTimer: ReturnType<typeof setTimeout>;

    function formatCOP(value: string | number): string {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(Number(value));
    }

    function applyFilters() {
        const params: Record<string, string | boolean> = {};
        if (searchValue) params.search = searchValue;
        if (categoriaId) params.categoria_id = categoriaId;
        if (bajoStock) params.bajo_stock = true;
        if (activoFilter !== '') params.activo = activoFilter;

        router.get('/inventario/productos', params, { preserveState: true, replace: true });
    }

    function onSearchInput() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => applyFilters(), 400);
    }

    function onCategoriaChange(val: string) {
        categoriaId = val;
        applyFilters();
    }

    function onActivoChange(val: string) {
        activoFilter = val;
        applyFilters();
    }

    function toggleBajoStock() {
        bajoStock = !bajoStock;
        applyFilters();
    }

    function stockVariant(producto: Producto): 'default' | 'secondary' | 'destructive' | 'outline' {
        const actual  = parseFloat(producto.stock_actual);
        const minimo  = parseFloat(producto.stock_minimo);
        if (actual < minimo) return 'destructive';
        if (actual === minimo) return 'secondary';
        return 'default';
    }

    function openDeleteDialog(producto: Producto) {
        productoToDelete = producto;
        deleteDialogOpen = true;
    }

    function closeDeleteDialog() {
        deleteDialogOpen = false;
        productoToDelete = null;
    }

    function confirmDelete() {
        if (!productoToDelete) return;
        router.delete(`/inventario/productos/${productoToDelete.id}`, {
            onFinish: () => closeDeleteDialog(),
        });
    }

    function goToPage(url: string | null) {
        if (url) router.get(url, {}, { preserveState: true });
    }
</script>

<AppHead title="Productos - Inventario" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <Package class="size-6 text-primary" />
                <h1 class="text-2xl font-bold">Productos</h1>
                <Badge variant="outline" class="ml-2">{productos.meta.total} registros</Badge>
            </div>
            <Button href="/inventario/productos/create">
                <Plus class="mr-2 size-4" />
                Nuevo Producto
            </Button>
        </div>

        <!-- Filtros -->
        <div class="flex flex-wrap gap-3">
            <div class="relative min-w-64 flex-1">
                <Search class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    type="search"
                    placeholder="Buscar por nombre, código o código de barras..."
                    class="pl-9"
                    value={searchValue}
                    oninput={(e) => { searchValue = (e.target as HTMLInputElement).value; onSearchInput(); }}
                />
            </div>

            <Select value={categoriaId} onValueChange={onCategoriaChange}>
                <SelectTrigger class="w-48">
                    {categoriaId
                        ? (categorias.find(c => String(c.id) === categoriaId)?.nombre ?? 'Categoría')
                        : 'Todas las categorías'}
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="">Todas las categorías</SelectItem>
                    {#each categorias as cat (cat.id)}
                        <SelectItem value={String(cat.id)}>{cat.nombre}</SelectItem>
                    {/each}
                </SelectContent>
            </Select>

            <Select value={activoFilter} onValueChange={onActivoChange}>
                <SelectTrigger class="w-36">
                    {activoFilter === 'true' ? 'Activos' : activoFilter === 'false' ? 'Inactivos' : 'Todos'}
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="">Todos</SelectItem>
                    <SelectItem value="true">Activos</SelectItem>
                    <SelectItem value="false">Inactivos</SelectItem>
                </SelectContent>
            </Select>

            <Button
                variant={bajoStock ? 'destructive' : 'outline'}
                onclick={toggleBajoStock}
                class="gap-2"
            >
                <AlertTriangle class="size-4" />
                Bajo stock
            </Button>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-xl border bg-card shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b bg-muted/40">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Código</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nombre</th>
                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Categoría</th>
                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Precio Venta</th>
                            <th class="px-4 py-3 text-center font-medium text-muted-foreground">Stock</th>
                            <th class="px-4 py-3 text-center font-medium text-muted-foreground">IVA</th>
                            <th class="px-4 py-3 text-center font-medium text-muted-foreground">Estado</th>
                            <th class="px-4 py-3 text-center font-medium text-muted-foreground">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        {#each productos.data as producto (producto.id)}
                            {@const actual = parseFloat(producto.stock_actual)}
                            {@const minimo = parseFloat(producto.stock_minimo)}
                            {@const variant = stockVariant(producto)}
                            <tr class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                    {producto.codigo ?? '—'}
                                </td>
                                <td class="px-4 py-3 font-medium">{producto.nombre}</td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {producto.categoria?.nombre ?? '—'}
                                </td>
                                <td class="px-4 py-3 text-right font-medium">
                                    {formatCOP(producto.precio_venta)}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        {#if actual < minimo}
                                            <AlertTriangle class="size-3.5 text-destructive" />
                                        {/if}
                                        <Badge {variant}>
                                            {actual} / {minimo}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center text-muted-foreground">
                                    {producto.tarifa_iva?.nombre ?? '—'}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <Badge variant={producto.activo ? 'default' : 'secondary'}>
                                        {producto.activo ? 'Activo' : 'Inactivo'}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-center gap-1">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            href={`/inventario/productos/${producto.id}`}
                                            class="h-8 w-8 p-0"
                                            title="Ver detalle"
                                        >
                                            <Package class="size-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            href={`/inventario/productos/${producto.id}/edit`}
                                            class="h-8 w-8 p-0"
                                            title="Editar"
                                        >
                                            <Edit class="size-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                            onclick={() => openDeleteDialog(producto)}
                                            title="Eliminar"
                                        >
                                            <Trash2 class="size-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-muted-foreground">
                                    No se encontraron productos con los filtros aplicados.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            {#if productos.meta.last_page > 1}
                <div class="flex items-center justify-between border-t px-4 py-3">
                    <p class="text-sm text-muted-foreground">
                        Mostrando {productos.meta.from ?? 0} - {productos.meta.to ?? 0} de {productos.meta.total} resultados
                    </p>
                    <div class="flex gap-1">
                        {#each productos.links as link, i (i)}
                            <Button
                                variant={link.active ? 'default' : 'outline'}
                                size="sm"
                                disabled={!link.url}
                                onclick={() => goToPage(link.url)}
                                class="min-w-9"
                            >
                                {@html link.label}
                            </Button>
                        {/each}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</AppLayout>

<!-- Diálogo de confirmación de eliminación -->
<Dialog bind:open={deleteDialogOpen}>
    <DialogContent>
        <DialogTitle>Eliminar producto</DialogTitle>
        <DialogDescription>
            ¿Estás seguro de que deseas eliminar el producto
            <strong>{productoToDelete?.nombre}</strong>? Esta acción puede deshacerse
            restaurando el registro.
        </DialogDescription>
        <DialogFooter>
            <Button variant="outline" onclick={closeDeleteDialog}>Cancelar</Button>
            <Button variant="destructive" onclick={confirmDelete}>Eliminar</Button>
        </DialogFooter>
    </DialogContent>
</Dialog>
