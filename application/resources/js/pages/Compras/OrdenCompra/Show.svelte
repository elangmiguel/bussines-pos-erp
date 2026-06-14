<script lang="ts">
    import { useForm, router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { ShoppingCart, Truck, Package, CheckCircle, Clock, Edit, Trash2 } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    interface TarifaIva { porcentaje: number }
    interface UnidadMedida { abreviatura: string }
    interface Producto {
        id: number;
        codigo: string;
        nombre: string;
        unidad_medida: UnidadMedida | null;
        tarifa_iva: TarifaIva | null;
    }

    interface DetalleOrden {
        id: number;
        producto_id: number;
        cantidad: number;
        precio_unitario: number;
        subtotal: number;
        producto: Producto;
    }

    interface DetalleRecepcion {
        id: number;
        producto_id: number;
        cantidad_esperada: number;
        cantidad_recibida: number;
        precio_unitario: number;
        novedad: string | null;
        producto: Producto;
    }

    interface RecepcionMercancia {
        id: number;
        fecha: string;
        estado: string;
        observaciones: string | null;
        user: { name: string } | null;
        detalles: DetalleRecepcion[];
    }

    interface Proveedor {
        id: number;
        tipo: string;
        persona: { nombres: string; apellidos: string } | null;
        empresa: { razon_social: string; nit: string; digito_verificacion: string } | null;
    }

    interface Orden {
        id: number;
        fecha: string;
        fecha_esperada: string | null;
        estado: string;
        subtotal: number;
        iva: number;
        total: number;
        observaciones: string | null;
        proveedor: Proveedor;
        detalles: DetalleOrden[];
        recepciones: RecepcionMercancia[];
    }

    let { orden }: { orden: Orden } = $props();

    const breadcrumbs = $derived([
        { title: 'Órdenes de Compra', href: '/compras/ordenes' },
        { title: `OC-${String(orden.id).padStart(6, '0')}`, href: '#' },
    ]);

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

    const recepcionEstadoColors: Record<string, string> = {
        completa:    'bg-green-100 text-green-700 border-green-200',
        parcial:     'bg-yellow-100 text-yellow-700 border-yellow-200',
        con_novedad: 'bg-orange-100 text-orange-700 border-orange-200',
    };

    const recepcionEstadoLabels: Record<string, string> = {
        completa:    'Completa',
        parcial:     'Parcial',
        con_novedad: 'Con novedad',
    };

    function getProveedorNombre(): string {
        if (orden.proveedor.tipo === 'natural' && orden.proveedor.persona) {
            return `${orden.proveedor.persona.nombres} ${orden.proveedor.persona.apellidos}`.trim();
        }
        return orden.proveedor.empresa?.razon_social ?? '—';
    }

    // Estado transition forms
    const enviarForm = useForm({ estado: 'enviada' });
    const cancelarForm = useForm({ estado: 'cancelada' });

    function enviarOrden(e: Event) {
        e.preventDefault();
        if (confirm('¿Confirma enviar esta orden al proveedor?')) {
            $enviarForm.patch(`/compras/ordenes/${orden.id}/estado`);
        }
    }

    function cancelarOrden(e: Event) {
        e.preventDefault();
        if (confirm('¿Está seguro de cancelar esta orden? Esta acción no se puede deshacer.')) {
            $cancelarForm.patch(`/compras/ordenes/${orden.id}/estado`);
        }
    }

    function eliminarOrden() {
        if (confirm('¿Está seguro de eliminar esta orden de compra?')) {
            router.delete(`/compras/ordenes/${orden.id}`);
        }
    }

    // Recepcion form — one row per detalle
    interface RecepcionItem {
        detalle_id: number;
        cantidad_recibida: number | string;
        novedad: string;
    }

    const today = new Date().toISOString().split('T')[0];

    const recepcionForm = useForm(untrack(() => ({
        orden_id: orden.id,
        fecha: today,
        observaciones: '',
        items: orden.detalles.map((d): RecepcionItem => ({
            detalle_id: d.id,
            cantidad_recibida: d.cantidad,
            novedad: '',
        })),
    })));

    function submitRecepcion(e: Event) {
        e.preventDefault();
        $recepcionForm.post('/compras/recepciones');
    }

    const canReceive = $derived(
        orden.estado === 'enviada' || orden.estado === 'recibida_parcial'
    );
</script>

<AppHead title={`OC-${String(orden.id).padStart(6, '0')}`} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <ShoppingCart class="h-7 w-7 text-primary" />
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold">OC-{String(orden.id).padStart(6, '0')}</h1>
                        <Badge class={estadoColors[orden.estado] ?? ''}>
                            {estadoLabels[orden.estado] ?? orden.estado}
                        </Badge>
                    </div>
                    <p class="text-sm text-muted-foreground flex items-center gap-1">
                        <Truck class="h-3.5 w-3.5" />
                        {getProveedorNombre()}
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                {#if orden.estado === 'borrador'}
                    <form onsubmit={enviarOrden}>
                        <Button type="submit" disabled={$enviarForm.processing}>
                            Marcar como Enviada
                        </Button>
                    </form>
                    <Button
                        variant="outline"
                        class="text-destructive hover:text-destructive"
                        onclick={eliminarOrden}
                    >
                        <Trash2 class="mr-2 h-4 w-4" />
                        Eliminar
                    </Button>
                {:else if orden.estado === 'enviada'}
                    <form onsubmit={cancelarOrden}>
                        <Button type="submit" variant="outline" class="text-destructive hover:text-destructive" disabled={$cancelarForm.processing}>
                            Cancelar Orden
                        </Button>
                    </form>
                {/if}
            </div>
        </div>

        <!-- Orden summary card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Card>
                <CardContent class="pt-4">
                    <div class="flex flex-col gap-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground flex items-center gap-1">
                                <Clock class="h-3.5 w-3.5" /> Fecha orden
                            </span>
                            <span>{new Date(orden.fecha).toLocaleDateString('es-CO')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground flex items-center gap-1">
                                <Clock class="h-3.5 w-3.5" /> Fecha esperada
                            </span>
                            <span>
                                {orden.fecha_esperada ? new Date(orden.fecha_esperada).toLocaleDateString('es-CO') : '—'}
                            </span>
                        </div>
                        {#if orden.observaciones}
                            <Separator class="my-1" />
                            <p class="text-xs text-muted-foreground bg-muted rounded p-2">{orden.observaciones}</p>
                        {/if}
                    </div>
                </CardContent>
            </Card>

            <Card class="md:col-span-2">
                <CardContent class="pt-4">
                    <div class="flex flex-col gap-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{cop.format(orden.subtotal)}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">IVA</span>
                            <span>{cop.format(orden.iva)}</span>
                        </div>
                        <Separator class="my-1" />
                        <div class="flex justify-between text-base font-bold">
                            <span>Total</span>
                            <span>{cop.format(orden.total)}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Detalles -->
        <Card>
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <Package class="h-4 w-4" />
                    Productos de la Orden
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-muted/50 border-y">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Producto</th>
                                <th class="px-4 py-2 text-center font-medium">Unidad</th>
                                <th class="px-4 py-2 text-right font-medium">Cantidad</th>
                                <th class="px-4 py-2 text-right font-medium">Precio Unitario</th>
                                <th class="px-4 py-2 text-right font-medium">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each orden.detalles as detalle (detalle.id)}
                                <tr class="hover:bg-muted/30">
                                    <td class="px-4 py-2">
                                        <div class="font-medium">{detalle.producto.nombre}</div>
                                        <div class="text-xs text-muted-foreground font-mono">{detalle.producto.codigo}</div>
                                    </td>
                                    <td class="px-4 py-2 text-center text-muted-foreground">
                                        {detalle.producto.unidad_medida?.abreviatura ?? '—'}
                                    </td>
                                    <td class="px-4 py-2 text-right">{Number(detalle.cantidad).toLocaleString('es-CO')}</td>
                                    <td class="px-4 py-2 text-right">{cop.format(detalle.precio_unitario)}</td>
                                    <td class="px-4 py-2 text-right font-medium">{cop.format(detalle.subtotal)}</td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Recepciones history -->
        {#if orden.recepciones.length > 0}
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <CheckCircle class="h-4 w-4" />
                        Recepciones Registradas
                    </CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    {#each orden.recepciones as recepcion (recepcion.id)}
                        <div class="border rounded-lg overflow-hidden">
                            <div class="bg-muted/50 px-4 py-2 flex items-center justify-between text-sm">
                                <div class="flex items-center gap-3">
                                    <span class="font-medium">Recepción #{recepcion.id}</span>
                                    <Badge class={recepcionEstadoColors[recepcion.estado] ?? ''}>
                                        {recepcionEstadoLabels[recepcion.estado] ?? recepcion.estado}
                                    </Badge>
                                </div>
                                <div class="flex items-center gap-3 text-muted-foreground">
                                    <span>{new Date(recepcion.fecha).toLocaleDateString('es-CO')}</span>
                                    {#if recepcion.user}
                                        <span>· {recepcion.user.name}</span>
                                    {/if}
                                </div>
                            </div>
                            {#if recepcion.observaciones}
                                <div class="px-4 py-2 text-xs text-muted-foreground border-b">
                                    {recepcion.observaciones}
                                </div>
                            {/if}
                            <table class="w-full text-sm">
                                <thead class="border-b bg-background">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium">Producto</th>
                                        <th class="px-4 py-2 text-right font-medium">Esperado</th>
                                        <th class="px-4 py-2 text-right font-medium">Recibido</th>
                                        <th class="px-4 py-2 text-left font-medium">Novedad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    {#each recepcion.detalles as dr (dr.id)}
                                        <tr class="hover:bg-muted/20">
                                            <td class="px-4 py-2">{dr.producto.nombre}</td>
                                            <td class="px-4 py-2 text-right">{Number(dr.cantidad_esperada).toLocaleString('es-CO')}</td>
                                            <td class="px-4 py-2 text-right">{Number(dr.cantidad_recibida).toLocaleString('es-CO')}</td>
                                            <td class="px-4 py-2 text-sm text-muted-foreground">{dr.novedad ?? '—'}</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    {/each}
                </CardContent>
            </Card>
        {/if}

        <!-- Recibir Mercancía form -->
        {#if canReceive}
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <CheckCircle class="h-4 w-4 text-green-600" />
                        Registrar Recepción de Mercancía
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form onsubmit={submitRecepcion} class="flex flex-col gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="rec_fecha">Fecha de recepción</Label>
                                <Input
                                    id="rec_fecha"
                                    type="date"
                                    bind:value={$recepcionForm.fecha}
                                />
                                <InputError message={$recepcionForm.errors.fecha} />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="rec_obs">Observaciones</Label>
                            <textarea
                                id="rec_obs"
                                bind:value={$recepcionForm.observaciones}
                                rows={2}
                                placeholder="Observaciones sobre la recepción..."
                                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 resize-none"
                            ></textarea>
                        </div>

                        <div class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50 border-b">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium">Producto</th>
                                        <th class="px-4 py-2 text-right font-medium">Cantidad ordenada</th>
                                        <th class="px-4 py-2 text-right font-medium w-36">Cantidad recibida</th>
                                        <th class="px-4 py-2 text-left font-medium">Novedad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    {#each orden.detalles as detalle, idx (detalle.id)}
                                        <tr class="hover:bg-muted/20">
                                            <td class="px-4 py-2">
                                                <div class="font-medium">{detalle.producto.nombre}</div>
                                                <div class="text-xs text-muted-foreground font-mono">{detalle.producto.codigo}</div>
                                            </td>
                                            <td class="px-4 py-2 text-right text-muted-foreground">
                                                {Number(detalle.cantidad).toLocaleString('es-CO')}
                                                {detalle.producto.unidad_medida?.abreviatura ?? ''}
                                            </td>
                                            <td class="px-4 py-2">
                                                <Input
                                                    type="number"
                                                    min={0}
                                                    step={0.001}
                                                    bind:value={$recepcionForm.items[idx].cantidad_recibida}
                                                    class="h-8 text-right text-xs"
                                                />
                                                <InputError message={$recepcionForm.errors[`items.${idx}.cantidad_recibida`]} />
                                            </td>
                                            <td class="px-4 py-2">
                                                <Input
                                                    bind:value={$recepcionForm.items[idx].novedad}
                                                    placeholder="Ej. Empaque dañado, cantidad incorrecta..."
                                                    class="h-8 text-xs"
                                                />
                                                <InputError message={$recepcionForm.errors[`items.${idx}.novedad`]} />
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>

                        {#if $recepcionForm.errors.items}
                            <p class="text-sm text-destructive">{$recepcionForm.errors.items}</p>
                        {/if}

                        <div class="flex justify-end">
                            <Button type="submit" disabled={$recepcionForm.processing}>
                                {$recepcionForm.processing ? 'Registrando...' : 'Registrar Recepción'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        {/if}
    </div>
</AppLayout>
