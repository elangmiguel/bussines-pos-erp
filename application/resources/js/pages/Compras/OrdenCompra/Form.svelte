<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Plus, Trash2, ShoppingCart } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    interface ProveedorOption {
        id: number;
        nombre: string;
        tipo: string;
    }

    interface TarifaIva {
        id: number;
        nombre: string;
        porcentaje: number;
    }

    interface UnidadMedida {
        id: number;
        nombre: string;
        abreviatura: string;
    }

    interface ProductoOption {
        id: number;
        codigo: string;
        nombre: string;
        precio_compra: number;
        unidad_medida: UnidadMedida | null;
        tarifa_iva: TarifaIva | null;
    }

    interface ItemForm {
        producto_id: number | '';
        cantidad: number | '';
        precio_unitario: number | '';
    }

    let {
        proveedores,
        productos,
        proveedor_id_init,
        errors = {},
    }: {
        proveedores: ProveedorOption[];
        productos: ProductoOption[];
        proveedor_id_init?: string | null;
        errors?: Record<string, string>;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Órdenes de Compra', href: '/compras/ordenes' },
        { title: 'Nueva Orden', href: '#' },
    ];

    const cop = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    });

    const today = new Date().toISOString().split('T')[0];

    const form = useForm(untrack(() => ({
        proveedor_id: proveedor_id_init ? Number(proveedor_id_init) : ('' as number | ''),
        fecha: today,
        fecha_esperada: '',
        observaciones: '',
        items: [{ producto_id: '' as number | '', cantidad: '' as number | '', precio_unitario: '' as number | '' }] as ItemForm[],
    })));

    // Computed totals
    const subtotalTotal = $derived(
        $form.items.reduce((acc, item) => {
            const qty = Number(item.cantidad) || 0;
            const price = Number(item.precio_unitario) || 0;
            return acc + qty * price;
        }, 0)
    );

    const ivaTotal = $derived(
        $form.items.reduce((acc, item) => {
            const qty = Number(item.cantidad) || 0;
            const price = Number(item.precio_unitario) || 0;
            const sub = qty * price;
            if (!item.producto_id) return acc;
            const prod = productos.find(p => p.id === Number(item.producto_id));
            const pct = prod?.tarifa_iva?.porcentaje ?? 0;
            return acc + sub * (Number(pct) / 100);
        }, 0)
    );

    const totalGeneral = $derived(subtotalTotal + ivaTotal);

    let productoSearch = $state<string[]>($form.items.map(() => ''));

    const filteredProductos = $derived(
        productoSearch.map((s) =>
            s
                ? productos.filter(
                      (p) =>
                          p.nombre.toLowerCase().includes(s.toLowerCase()) ||
                          p.codigo.toLowerCase().includes(s.toLowerCase())
                  )
                : productos
        )
    );

    function addItem() {
        $form.items = [...$form.items, { producto_id: '', cantidad: '', precio_unitario: '' }];
        productoSearch = [...productoSearch, ''];
    }

    function removeItem(idx: number) {
        $form.items = $form.items.filter((_, i) => i !== idx);
        productoSearch = productoSearch.filter((_, i) => i !== idx);
    }

    function onProductoChange(idx: number) {
        const pid = $form.items[idx].producto_id;
        if (!pid) return;
        const prod = productos.find(p => p.id === Number(pid));
        if (prod) {
            const items = [...$form.items];
            items[idx] = { ...items[idx], precio_unitario: Number(prod.precio_compra) };
            $form.items = items;
        }
    }

    function getItemSubtotal(item: ItemForm): number {
        return (Number(item.cantidad) || 0) * (Number(item.precio_unitario) || 0);
    }

    function submit(e: Event) {
        e.preventDefault();
        // Sanitize: cast producto_id to number, cast quantities and prices, and null out empty fecha_esperada
        $form.transform((data) => ({
            ...data,
            fecha_esperada: data.fecha_esperada || null,
            items: data.items.map((item) => ({
                producto_id: Number(item.producto_id),
                cantidad: Number(item.cantidad),
                precio_unitario: Number(item.precio_unitario),
            })),
        }));
        $form.post('/compras/ordenes');
    }
</script>

<AppHead title="Nueva Orden de Compra" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6 max-w-5xl mx-auto">
        <div class="flex items-center gap-2">
            <ShoppingCart class="h-6 w-6 text-primary" />
            <h1 class="text-2xl font-bold">Nueva Orden de Compra</h1>
        </div>

        <form onsubmit={submit} class="flex flex-col gap-6">
            <!-- Header data -->
            <Card>
                <CardHeader>
                    <CardTitle>Datos Generales</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="proveedor_id">Proveedor</Label>
                        <select
                            id="proveedor_id"
                            bind:value={$form.proveedor_id}
                            class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                            <option value="">-- Seleccione un proveedor --</option>
                            {#each proveedores as prov (prov.id)}
                                <option value={prov.id}>{prov.nombre}</option>
                            {/each}
                        </select>
                        <InputError message={$form.errors.proveedor_id} />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="fecha">Fecha de la orden</Label>
                            <Input
                                id="fecha"
                                type="date"
                                bind:value={$form.fecha}
                            />
                            <InputError message={$form.errors.fecha} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="fecha_esperada">Fecha esperada de recepción</Label>
                            <Input
                                id="fecha_esperada"
                                type="date"
                                bind:value={$form.fecha_esperada}
                            />
                            <InputError message={$form.errors.fecha_esperada} />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="observaciones">Observaciones</Label>
                        <textarea
                            id="observaciones"
                            bind:value={$form.observaciones}
                            rows={2}
                            placeholder="Observaciones o instrucciones especiales para el proveedor..."
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 resize-none"
                        ></textarea>
                        <InputError message={$form.errors.observaciones} />
                    </div>
                </CardContent>
            </Card>

            <!-- Items -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle>Productos</CardTitle>
                        <Button type="button" variant="outline" size="sm" onclick={addItem}>
                            <Plus class="mr-1 h-4 w-4" />
                            Agregar producto
                        </Button>
                    </div>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-muted/50 border-y">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium">Producto</th>
                                    <th class="px-4 py-2 text-center font-medium w-32">Cantidad</th>
                                    <th class="px-4 py-2 text-right font-medium w-40">Precio Unitario</th>
                                    <th class="px-4 py-2 text-right font-medium w-40">Subtotal</th>
                                    <th class="px-4 py-2 w-12"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                {#each $form.items as item, idx (idx)}
                                    <tr>
                                        <td class="px-4 py-2">
                                            <div class="flex flex-col gap-1">
                                                <Input
                                                    placeholder="Buscar producto..."
                                                    bind:value={productoSearch[idx]}
                                                    class="h-8 text-xs"
                                                />
                                                <select
                                                    bind:value={item.producto_id}
                                                    onchange={() => onProductoChange(idx)}
                                                    class="h-8 w-full rounded-md border border-input bg-background px-2 py-1 text-xs shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                                                >
                                                    <option value="">-- Seleccione --</option>
                                                    {#each filteredProductos[idx] as prod (prod.id)}
                                                        <option value={prod.id}>
                                                            [{prod.codigo}] {prod.nombre}
                                                            {prod.unidad_medida ? `(${prod.unidad_medida.abreviatura})` : ''}
                                                        </option>
                                                    {/each}
                                                </select>
                                            </div>
                                            <InputError message={$form.errors[`items.${idx}.producto_id`]} />
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                type="number"
                                                min={0.001}
                                                step={0.001}
                                                bind:value={item.cantidad}
                                                class="h-8 text-right text-xs w-full"
                                                placeholder="0"
                                            />
                                            <InputError message={$form.errors[`items.${idx}.cantidad`]} />
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                type="number"
                                                min={0}
                                                step={1}
                                                bind:value={item.precio_unitario}
                                                class="h-8 text-right text-xs w-full"
                                                placeholder="0"
                                            />
                                            <InputError message={$form.errors[`items.${idx}.precio_unitario`]} />
                                        </td>
                                        <td class="px-4 py-2 text-right font-medium text-xs">
                                            {cop.format(getItemSubtotal(item))}
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            {#if $form.items.length > 1}
                                                <Button
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onclick={() => removeItem(idx)}
                                                    class="h-7 w-7 p-0 text-destructive hover:text-destructive"
                                                >
                                                    <Trash2 class="h-3.5 w-3.5" />
                                                </Button>
                                            {/if}
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>

                    {#if $form.errors.items}
                        <p class="px-4 py-2 text-sm text-destructive">{$form.errors.items}</p>
                    {/if}

                    <!-- Totals -->
                    <div class="border-t px-4 py-4">
                        <div class="flex flex-col gap-1 items-end text-sm">
                            <div class="flex gap-8">
                                <span class="text-muted-foreground">Subtotal:</span>
                                <span class="font-medium w-36 text-right">{cop.format(subtotalTotal)}</span>
                            </div>
                            <div class="flex gap-8">
                                <span class="text-muted-foreground">IVA:</span>
                                <span class="font-medium w-36 text-right">{cop.format(ivaTotal)}</span>
                            </div>
                            <Separator class="my-1 w-60" />
                            <div class="flex gap-8 text-base font-bold">
                                <span>Total:</span>
                                <span class="w-36 text-right">{cop.format(totalGeneral)}</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="flex gap-3 justify-end">
                <Button variant="outline" href="/compras/ordenes" type="button">Cancelar</Button>
                <Button type="submit" disabled={$form.processing}>
                    {$form.processing ? 'Creando orden...' : 'Crear Orden de Compra'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
