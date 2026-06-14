<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { AlertTriangle, Edit, Package } from 'lucide-svelte';
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

    interface TarifaIva {
        id: number;
        nombre: string;
        porcentaje: string;
    }

    interface UnidadMedida {
        id: number;
        nombre: string;
        abreviatura: string;
    }

    interface Categoria {
        id: number;
        nombre: string;
    }

    interface User {
        id: number;
        name: string;
    }

    interface Movimiento {
        id: number;
        tipo: string;
        cantidad: string;
        stock_anterior: string;
        stock_nuevo: string;
        costo_unitario: string | null;
        motivo: string | null;
        referencia_tipo: string | null;
        referencia_id: number | null;
        created_at: string;
        user: User | null;
    }

    interface Proveedor {
        id: number;
        nombre?: string;
        razon_social?: string;
        pivot?: {
            precio_compra: string | null;
            es_principal: boolean;
        };
    }

    interface ProductoFull {
        id: number;
        codigo: string | null;
        codigo_barras: string | null;
        nombre: string;
        descripcion: string | null;
        precio_compra: string;
        precio_venta: string;
        precio_mayorista: string | null;
        stock_actual: string;
        stock_minimo: string;
        stock_maximo: string | null;
        activo: boolean;
        categoria: Categoria | null;
        unidad_medida: UnidadMedida | null;
        tarifa_iva: TarifaIva | null;
        created_at: string;
        updated_at: string;
    }

    let {
        producto,
        movimientos,
        proveedores,
    }: {
        producto: ProductoFull;
        movimientos: Movimiento[];
        proveedores: Proveedor[];
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Inventario', href: '/inventario/productos' },
        { title: 'Productos', href: '/inventario/productos' },
        { title: producto.nombre, href: `/inventario/productos/${producto.id}` },
    ]);

    const ajusteForm = useForm(untrack(() => ({
        producto_id:    String(producto.id),
        nueva_cantidad: producto.stock_actual ?? '0',
        motivo:         '',
    })));

    function formatCOP(value: string | number | null): string {
        if (value === null || value === undefined) return '—';
        const num = Number(value);
        if (isNaN(num)) return '—';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(num);
    }

    function formatDate(dateStr: string): string {
        return new Intl.DateTimeFormat('es-CO', {
            dateStyle: 'short',
            timeStyle: 'short',
        }).format(new Date(dateStr));
    }

    const stockActual = $derived(parseFloat(producto.stock_actual));
    const stockMinimo = $derived(parseFloat(producto.stock_minimo));

    const stockStatus = $derived<'ok' | 'warning' | 'danger'>(() => {
        if (stockActual < stockMinimo) return 'danger';
        if (stockActual === stockMinimo) return 'warning';
        return 'ok';
    });

    const stockVariant = $derived<'default' | 'secondary' | 'destructive'>(() => {
        if (stockStatus === 'danger') return 'destructive';
        if (stockStatus === 'warning') return 'secondary';
        return 'default';
    });

    const tipoLabels: Record<string, string> = {
        entrada_compra:    'Entrada compra',
        salida_venta:      'Salida venta',
        ajuste_positivo:   'Ajuste positivo',
        ajuste_negativo:   'Ajuste negativo',
        devolucion_venta:  'Devolución venta',
        devolucion_compra: 'Devolución compra',
        traslado:          'Traslado',
    };

    function tipoVariant(tipo: string): 'default' | 'secondary' | 'destructive' | 'outline' {
        switch (tipo) {
            case 'entrada_compra':
            case 'ajuste_positivo':
            case 'devolucion_venta':
                return 'default';
            case 'salida_venta':
            case 'devolucion_compra':
                return 'secondary';
            case 'ajuste_negativo':
                return 'destructive';
            default:
                return 'outline';
        }
    }

    function submitAjuste() {
        $ajusteForm.post('/inventario/ajustes', {
            preserveScroll: true,
            onSuccess: () => {
                $ajusteForm.reset('motivo');
            },
        });
    }

    function proveedorNombre(p: Proveedor): string {
        return p.razon_social ?? p.nombre ?? `Proveedor #${p.id}`;
    }
</script>

<AppHead title={producto.nombre} />

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <!-- Encabezado -->
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="rounded-xl bg-primary/10 p-3">
                    <Package class="size-6 text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold">{producto.nombre}</h1>
                    {#if producto.codigo}
                        <p class="font-mono text-sm text-muted-foreground">{producto.codigo}</p>
                    {/if}
                </div>
            </div>
            <div class="flex gap-2">
                <Badge variant={producto.activo ? 'default' : 'secondary'}>
                    {producto.activo ? 'Activo' : 'Inactivo'}
                </Badge>
                <Button variant="outline" href={`/inventario/productos/${producto.id}/edit`}>
                    <Edit class="mr-2 size-4" />
                    Editar
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Columna izquierda: Datos del producto -->
            <div class="space-y-6 lg:col-span-2">
                <!-- Información general -->
                <Card>
                    <CardHeader>
                        <CardTitle>Información del producto</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <dl class="grid grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div>
                                <dt class="text-muted-foreground">Código</dt>
                                <dd class="mt-0.5 font-mono font-medium">{producto.codigo ?? '—'}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Código de barras</dt>
                                <dd class="mt-0.5 font-mono font-medium">{producto.codigo_barras ?? '—'}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-muted-foreground">Descripción</dt>
                                <dd class="mt-0.5">{producto.descripcion ?? '—'}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Categoría</dt>
                                <dd class="mt-0.5">{producto.categoria?.nombre ?? '—'}</dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Unidad de medida</dt>
                                <dd class="mt-0.5">
                                    {producto.unidad_medida
                                        ? `${producto.unidad_medida.nombre} (${producto.unidad_medida.abreviatura})`
                                        : '—'}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-muted-foreground">Tarifa IVA</dt>
                                <dd class="mt-0.5">
                                    {producto.tarifa_iva
                                        ? `${producto.tarifa_iva.nombre} (${producto.tarifa_iva.porcentaje}%)`
                                        : '—'}
                                </dd>
                            </div>
                        </dl>
                    </CardContent>
                </Card>

                <!-- Precios -->
                <Card>
                    <CardHeader>
                        <CardTitle>Precios</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-3 gap-4 text-sm">
                            <div class="rounded-lg border bg-muted/20 p-3 text-center">
                                <p class="text-muted-foreground">Compra</p>
                                <p class="mt-1 text-lg font-bold">{formatCOP(producto.precio_compra)}</p>
                            </div>
                            <div class="rounded-lg border border-primary/30 bg-primary/5 p-3 text-center">
                                <p class="text-muted-foreground">Venta</p>
                                <p class="mt-1 text-lg font-bold text-primary">{formatCOP(producto.precio_venta)}</p>
                            </div>
                            <div class="rounded-lg border bg-muted/20 p-3 text-center">
                                <p class="text-muted-foreground">Mayorista</p>
                                <p class="mt-1 text-lg font-bold">
                                    {producto.precio_mayorista ? formatCOP(producto.precio_mayorista) : '—'}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Movimientos de inventario -->
                <Card>
                    <CardHeader>
                        <CardTitle>Últimos movimientos de inventario</CardTitle>
                    </CardHeader>
                    <CardContent class="p-0">
                        {#if movimientos.length === 0}
                            <p class="px-6 py-8 text-center text-sm text-muted-foreground">
                                No hay movimientos registrados para este producto.
                            </p>
                        {:else}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="border-b bg-muted/40">
                                        <tr>
                                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Fecha</th>
                                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo</th>
                                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Cantidad</th>
                                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Stock ant.</th>
                                            <th class="px-4 py-3 text-right font-medium text-muted-foreground">Stock nuevo</th>
                                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Motivo</th>
                                            <th class="px-4 py-3 text-left font-medium text-muted-foreground">Usuario</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        {#each movimientos as mov (mov.id)}
                                            <tr class="hover:bg-muted/30 transition-colors">
                                                <td class="px-4 py-2.5 text-xs text-muted-foreground">
                                                    {formatDate(mov.created_at)}
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <Badge variant={tipoVariant(mov.tipo)} class="text-xs">
                                                        {tipoLabels[mov.tipo] ?? mov.tipo}
                                                    </Badge>
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono">
                                                    {parseFloat(mov.cantidad).toLocaleString('es-CO')}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono text-muted-foreground">
                                                    {parseFloat(mov.stock_anterior).toLocaleString('es-CO')}
                                                </td>
                                                <td class="px-4 py-2.5 text-right font-mono font-medium">
                                                    {parseFloat(mov.stock_nuevo).toLocaleString('es-CO')}
                                                </td>
                                                <td class="px-4 py-2.5 text-xs text-muted-foreground">
                                                    {mov.motivo ?? '—'}
                                                </td>
                                                <td class="px-4 py-2.5 text-xs text-muted-foreground">
                                                    {mov.user?.name ?? '—'}
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

            <!-- Columna derecha: Stock y ajuste -->
            <div class="space-y-6">
                <!-- Stock actual -->
                <Card>
                    <CardHeader>
                        <CardTitle>Stock</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="text-center">
                            <div class="flex items-center justify-center gap-2">
                                {#if stockStatus === 'danger'}
                                    <AlertTriangle class="size-5 text-destructive" />
                                {/if}
                                <span class="text-4xl font-bold">
                                    {stockActual.toLocaleString('es-CO')}
                                </span>
                            </div>
                            {#if producto.unidad_medida}
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {producto.unidad_medida.abreviatura}
                                </p>
                            {/if}
                            <div class="mt-2">
                                <Badge variant={stockVariant}>
                                    {#if stockStatus === 'danger'}
                                        Bajo stock
                                    {:else if stockStatus === 'warning'}
                                        En límite mínimo
                                    {:else}
                                        Stock normal
                                    {/if}
                                </Badge>
                            </div>
                        </div>

                        <Separator />

                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted-foreground">Stock mínimo</dt>
                                <dd class="font-medium">{parseFloat(producto.stock_minimo).toLocaleString('es-CO')}</dd>
                            </div>
                            {#if producto.stock_maximo}
                                <div class="flex justify-between">
                                    <dt class="text-muted-foreground">Stock máximo</dt>
                                    <dd class="font-medium">{parseFloat(producto.stock_maximo).toLocaleString('es-CO')}</dd>
                                </div>
                            {/if}
                        </dl>
                    </CardContent>
                </Card>

                <!-- Ajuste de stock -->
                <Card>
                    <CardHeader>
                        <CardTitle>Ajuste de stock</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onsubmit={(e) => { e.preventDefault(); submitAjuste(); }}
                            class="space-y-4"
                        >
                            <input type="hidden" name="producto_id" value={producto.id} />

                            <div class="space-y-1.5">
                                <Label for="nueva_cantidad">Nueva cantidad</Label>
                                <Input
                                    id="nueva_cantidad"
                                    type="number"
                                    min="0"
                                    step="0.001"
                                    placeholder="0"
                                    bind:value={$ajusteForm.nueva_cantidad}
                                />
                                <InputError message={$ajusteForm.errors.nueva_cantidad} />
                            </div>

                            <div class="space-y-1.5">
                                <Label for="motivo">Motivo del ajuste</Label>
                                <textarea
                                    id="motivo"
                                    rows={3}
                                    placeholder="Describe el motivo del ajuste..."
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:border-ring disabled:cursor-not-allowed disabled:opacity-50"
                                    bind:value={$ajusteForm.motivo}
                                ></textarea>
                                <InputError message={$ajusteForm.errors.motivo} />
                            </div>

                            <Button
                                type="submit"
                                class="w-full"
                                disabled={$ajusteForm.processing}
                            >
                                {$ajusteForm.processing ? 'Aplicando ajuste...' : 'Aplicar ajuste'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Proveedores -->
                {#if proveedores.length > 0}
                    <Card>
                        <CardHeader>
                            <CardTitle>Proveedores</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            {#each proveedores as prov (prov.id)}
                                <div class="flex items-center justify-between rounded-lg border px-3 py-2 text-sm">
                                    <span class="font-medium">{proveedorNombre(prov)}</span>
                                    {#if prov.pivot?.es_principal}
                                        <Badge variant="secondary" class="text-xs">Principal</Badge>
                                    {/if}
                                </div>
                            {/each}
                        </CardContent>
                    </Card>
                {/if}
            </div>
        </div>
    </div>
</AppLayout>
