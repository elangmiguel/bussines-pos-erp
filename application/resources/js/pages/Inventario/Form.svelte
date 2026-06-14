<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
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

    interface ProductoData {
        id: number;
        codigo: string | null;
        codigo_barras: string | null;
        nombre: string;
        descripcion: string | null;
        categoria_id: number | null;
        unidad_medida_id: number;
        tarifa_iva_id: number;
        precio_compra: string;
        precio_venta: string;
        precio_mayorista: string | null;
        stock_minimo: string;
        stock_maximo: string | null;
        activo: boolean;
        tarifa_iva: TarifaIva | null;
    }

    let {
        producto,
        categorias,
        tarifas,
        unidades,
    }: {
        producto: ProductoData | null;
        categorias: Categoria[];
        tarifas: TarifaIva[];
        unidades: UnidadMedida[];
        errors?: Record<string, string>;
    } = $props();

    const isEditing = $derived(producto !== null);

    const breadcrumbs = $derived([
        { title: 'Inventario', href: '/inventario/productos' },
        { title: 'Productos', href: '/inventario/productos' },
        { title: isEditing ? 'Editar producto' : 'Nuevo producto', href: '#' },
    ]);

    const form = useForm(untrack(() => ({
        codigo:           producto?.codigo           ?? '',
        codigo_barras:    producto?.codigo_barras    ?? '',
        nombre:           producto?.nombre           ?? '',
        descripcion:      producto?.descripcion      ?? '',
        categoria_id:     producto?.categoria_id     ? String(producto.categoria_id) : '',
        unidad_medida_id: producto?.unidad_medida_id ? String(producto.unidad_medida_id) : '',
        tarifa_iva_id:    producto?.tarifa_iva_id    ? String(producto.tarifa_iva_id) : '',
        precio_compra:    producto?.precio_compra    ?? '',
        precio_venta:     producto?.precio_venta     ?? '',
        precio_mayorista: producto?.precio_mayorista ?? '',
        stock_minimo:     producto?.stock_minimo     ?? '0',
        stock_maximo:     producto?.stock_maximo     ?? '',
        activo:           producto?.activo           ?? true,
    })));

    function formatCOP(value: string | number): string {
        const num = Number(value);
        if (isNaN(num)) return '—';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(num);
    }

    const precioConIva = $derived.by(() => {
        const tarifa = tarifas.find(t => String(t.id) === $form.tarifa_iva_id);
        const porcentaje = tarifa ? parseFloat(tarifa.porcentaje) : 0;
        const base = parseFloat($form.precio_venta as string);
        if (isNaN(base)) return null;
        return base * (1 + porcentaje / 100);
    });

    function handleSubmit() {
        if (isEditing && producto) {
            $form.put(`/inventario/productos/${producto.id}`, {
                preserveScroll: true,
            });
        } else {
            $form.post('/inventario/productos', {
                preserveScroll: true,
            });
        }
    }
</script>

<AppHead title={isEditing ? 'Editar Producto' : 'Nuevo Producto'} />

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-4xl p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">
                {isEditing ? 'Editar Producto' : 'Nuevo Producto'}
            </h1>
            <p class="text-sm text-muted-foreground">
                {isEditing ? 'Modifica los datos del producto.' : 'Completa los datos para registrar un nuevo producto.'}
            </p>
        </div>

        <form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="space-y-6">
            <!-- Información básica -->
            <Card>
                <CardHeader>
                    <CardTitle>Información básica</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="codigo">Código</Label>
                            <Input
                                id="codigo"
                                placeholder="Ej: PROD-001"
                                bind:value={$form.codigo}
                            />
                            <InputError message={$form.errors.codigo} />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="codigo_barras">Código de barras</Label>
                            <Input
                                id="codigo_barras"
                                placeholder="Ej: 7702001234567"
                                bind:value={$form.codigo_barras}
                            />
                            <InputError message={$form.errors.codigo_barras} />
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <Label for="nombre">Nombre <span class="text-destructive">*</span></Label>
                        <Input
                            id="nombre"
                            placeholder="Nombre del producto"
                            bind:value={$form.nombre}
                            required
                        />
                        <InputError message={$form.errors.nombre} />
                    </div>

                    <div class="space-y-1.5">
                        <Label for="descripcion">Descripción</Label>
                        <textarea
                            id="descripcion"
                            rows={3}
                            placeholder="Descripción detallada del producto..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:border-ring disabled:cursor-not-allowed disabled:opacity-50"
                            bind:value={$form.descripcion}
                        ></textarea>
                        <InputError message={$form.errors.descripcion} />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="categoria_id">Categoría</Label>
                            <Select
                                value={$form.categoria_id}
                                onValueChange={(val) => ($form.categoria_id = val)}
                            >
                                <SelectTrigger class="w-full">
                                    {$form.categoria_id
                                        ? (categorias.find(c => String(c.id) === $form.categoria_id)?.nombre ?? 'Seleccionar...')
                                        : 'Sin categoría'}
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">Sin categoría</SelectItem>
                                    {#each categorias as cat (cat.id)}
                                        <SelectItem value={String(cat.id)}>{cat.nombre}</SelectItem>
                                    {/each}
                                </SelectContent>
                            </Select>
                            <InputError message={$form.errors.categoria_id} />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="unidad_medida_id">Unidad de medida <span class="text-destructive">*</span></Label>
                            <Select
                                value={$form.unidad_medida_id}
                                onValueChange={(val) => ($form.unidad_medida_id = val)}
                            >
                                <SelectTrigger class="w-full">
                                    {$form.unidad_medida_id
                                        ? (unidades.find(u => String(u.id) === $form.unidad_medida_id)?.nombre ?? 'Seleccionar...')
                                        : 'Seleccionar...'}
                                </SelectTrigger>
                                <SelectContent>
                                    {#each unidades as u (u.id)}
                                        <SelectItem value={String(u.id)}>
                                            {u.nombre} ({u.abreviatura})
                                        </SelectItem>
                                    {/each}
                                </SelectContent>
                            </Select>
                            <InputError message={$form.errors.unidad_medida_id} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Precios e IVA -->
            <Card>
                <CardHeader>
                    <CardTitle>Precios e IVA</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-1.5">
                        <Label for="tarifa_iva_id">Tarifa de IVA <span class="text-destructive">*</span></Label>
                        <Select
                            value={$form.tarifa_iva_id}
                            onValueChange={(val) => ($form.tarifa_iva_id = val)}
                        >
                            <SelectTrigger class="w-full sm:w-72">
                                {$form.tarifa_iva_id
                                    ? (tarifas.find(t => String(t.id) === $form.tarifa_iva_id)?.nombre ?? 'Seleccionar...')
                                    : 'Seleccionar...'}
                            </SelectTrigger>
                            <SelectContent>
                                {#each tarifas as t (t.id)}
                                    <SelectItem value={String(t.id)}>
                                        {t.nombre} ({t.porcentaje}%)
                                    </SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                        <InputError message={$form.errors.tarifa_iva_id} />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="space-y-1.5">
                            <Label for="precio_compra">Precio de compra (COP) <span class="text-destructive">*</span></Label>
                            <Input
                                id="precio_compra"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="0"
                                bind:value={$form.precio_compra}
                            />
                            <InputError message={$form.errors.precio_compra} />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="precio_venta">Precio de venta (COP) <span class="text-destructive">*</span></Label>
                            <Input
                                id="precio_venta"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="0"
                                bind:value={$form.precio_venta}
                            />
                            <InputError message={$form.errors.precio_venta} />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="precio_mayorista">Precio mayorista (COP)</Label>
                            <Input
                                id="precio_mayorista"
                                type="number"
                                min="0"
                                step="1"
                                placeholder="0"
                                bind:value={$form.precio_mayorista}
                            />
                            <InputError message={$form.errors.precio_mayorista} />
                        </div>
                    </div>

                    {#if precioConIva !== null}
                        <div class="rounded-lg border border-dashed bg-muted/30 px-4 py-3">
                            <p class="text-sm text-muted-foreground">
                                Precio de venta con IVA:
                                <span class="ml-2 font-semibold text-foreground">{formatCOP(precioConIva)}</span>
                            </p>
                        </div>
                    {/if}
                </CardContent>
            </Card>

            <!-- Stock -->
            <Card>
                <CardHeader>
                    <CardTitle>Control de stock</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label for="stock_minimo">Stock mínimo <span class="text-destructive">*</span></Label>
                            <Input
                                id="stock_minimo"
                                type="number"
                                min="0"
                                step="0.001"
                                placeholder="0"
                                bind:value={$form.stock_minimo}
                            />
                            <InputError message={$form.errors.stock_minimo} />
                        </div>

                        <div class="space-y-1.5">
                            <Label for="stock_maximo">Stock máximo</Label>
                            <Input
                                id="stock_maximo"
                                type="number"
                                min="0"
                                step="0.001"
                                placeholder="Sin límite"
                                bind:value={$form.stock_maximo}
                            />
                            <InputError message={$form.errors.stock_maximo} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Estado -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="activo"
                            checked={$form.activo}
                            onCheckedChange={(val) => ($form.activo = val === true)}
                        />
                        <div>
                            <Label for="activo" class="cursor-pointer font-medium">Producto activo</Label>
                            <p class="text-xs text-muted-foreground">
                                Los productos inactivos no aparecen en ventas ni en búsquedas.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Separator />

            <!-- Botones -->
            <div class="flex items-center justify-end gap-3">
                <Button variant="outline" href="/inventario/productos" type="button">
                    Cancelar
                </Button>
                <Button type="submit" disabled={$form.processing}>
                    {$form.processing
                        ? 'Guardando...'
                        : isEditing
                            ? 'Actualizar producto'
                            : 'Crear producto'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
