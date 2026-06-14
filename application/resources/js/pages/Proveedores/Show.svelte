<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { Truck, Package, ShoppingCart, Edit, Trash2, Plus, CheckCircle } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    interface Persona {
        tipo_identificacion: string;
        numero_identificacion: string;
        nombres: string;
        apellidos: string;
        email: string | null;
        telefono: string | null;
        celular: string | null;
        direccion: string | null;
        ciudad: string | null;
    }

    interface Empresa {
        razon_social: string;
        nit: string;
        digito_verificacion: string;
        regimen_tributario: string;
        email: string | null;
        telefono: string | null;
        direccion: string | null;
        ciudad: string | null;
    }

    interface ProductoPivot {
        id: number;
        codigo: string;
        nombre: string;
        pivot: {
            precio_compra: number;
            tiempo_entrega: number | null;
            es_principal: boolean;
        };
    }

    interface OrdenResumen {
        id: number;
        fecha: string;
        total: number;
        estado: string;
        detalles_count: number;
    }

    interface Proveedor {
        id: number;
        tipo: 'natural' | 'juridico';
        condiciones_pago: string | null;
        plazo_dias: number;
        activo: boolean;
        persona: Persona | null;
        empresa: Empresa | null;
    }

    let {
        proveedor,
        ordenes,
        productos,
    }: {
        proveedor: Proveedor;
        ordenes: OrdenResumen[];
        productos: ProductoPivot[];
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Proveedores', href: '/proveedores' },
        { title: getNombre(), href: '#' },
    ];

    const cop = new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    });

    function getNombre(): string {
        if (proveedor.tipo === 'natural' && proveedor.persona) {
            return `${proveedor.persona.nombres} ${proveedor.persona.apellidos}`.trim();
        }
        return proveedor.empresa?.razon_social ?? '—';
    }

    function getIdentificacion(): string {
        if (proveedor.tipo === 'natural' && proveedor.persona) {
            return `${proveedor.persona.tipo_identificacion}: ${proveedor.persona.numero_identificacion}`;
        }
        if (proveedor.empresa) {
            return `NIT: ${proveedor.empresa.nit}-${proveedor.empresa.digito_verificacion}`;
        }
        return '—';
    }

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

    function confirmDelete() {
        if (confirm(`¿Está seguro de eliminar el proveedor "${getNombre()}"?`)) {
            router.delete(`/proveedores/${proveedor.id}`);
        }
    }
</script>

<AppHead title={getNombre()} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Truck class="h-7 w-7 text-primary" />
                <div>
                    <h1 class="text-2xl font-bold">{getNombre()}</h1>
                    <p class="text-sm text-muted-foreground">{getIdentificacion()}</p>
                </div>
                {#if proveedor.activo}
                    <Badge class="bg-green-100 text-green-800 border-green-200">Activo</Badge>
                {:else}
                    <Badge variant="destructive">Inactivo</Badge>
                {/if}
            </div>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    href={`/compras/ordenes/create?proveedor_id=${proveedor.id}`}
                >
                    <ShoppingCart class="mr-2 h-4 w-4" />
                    Nueva Orden de Compra
                </Button>
                <Button variant="outline" href={`/proveedores/${proveedor.id}/edit`}>
                    <Edit class="mr-2 h-4 w-4" />
                    Editar
                </Button>
                <Button
                    variant="outline"
                    class="text-destructive hover:text-destructive"
                    onclick={confirmDelete}
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    Eliminar
                </Button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Proveedor info -->
            <div class="lg:col-span-1 flex flex-col gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Información</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 text-sm">
                        {#if proveedor.tipo === 'natural' && proveedor.persona}
                            <div>
                                <span class="text-muted-foreground">Email:</span>
                                <span class="ml-2">{proveedor.persona.email ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Teléfono:</span>
                                <span class="ml-2">{proveedor.persona.telefono ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Celular:</span>
                                <span class="ml-2">{proveedor.persona.celular ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Dirección:</span>
                                <span class="ml-2">{proveedor.persona.direccion ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Ciudad:</span>
                                <span class="ml-2">{proveedor.persona.ciudad ?? '—'}</span>
                            </div>
                        {:else if proveedor.empresa}
                            <div>
                                <span class="text-muted-foreground">Régimen:</span>
                                <span class="ml-2">{proveedor.empresa.regimen_tributario === 'responsable_iva' ? 'Responsable de IVA' : 'No responsable de IVA'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Email:</span>
                                <span class="ml-2">{proveedor.empresa.email ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Teléfono:</span>
                                <span class="ml-2">{proveedor.empresa.telefono ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Dirección:</span>
                                <span class="ml-2">{proveedor.empresa.direccion ?? '—'}</span>
                            </div>
                            <div>
                                <span class="text-muted-foreground">Ciudad:</span>
                                <span class="ml-2">{proveedor.empresa.ciudad ?? '—'}</span>
                            </div>
                        {/if}
                        <Separator />
                        <div>
                            <span class="text-muted-foreground">Plazo de pago:</span>
                            <span class="ml-2 font-medium">{proveedor.plazo_dias} días</span>
                        </div>
                        {#if proveedor.condiciones_pago}
                            <div>
                                <p class="text-muted-foreground mb-1">Condiciones:</p>
                                <p class="text-xs bg-muted rounded p-2">{proveedor.condiciones_pago}</p>
                            </div>
                        {/if}
                    </CardContent>
                </Card>
            </div>

            <!-- Right column -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                <!-- Productos asociados -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <Package class="h-4 w-4" />
                                Productos Asociados
                            </CardTitle>
                            <span class="text-sm text-muted-foreground">{productos.length} productos</span>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        {#if productos.length > 0}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50 border-y">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium">Código</th>
                                            <th class="px-4 py-2 text-left font-medium">Nombre</th>
                                            <th class="px-4 py-2 text-right font-medium">Precio Compra</th>
                                            <th class="px-4 py-2 text-center font-medium">Entrega</th>
                                            <th class="px-4 py-2 text-center font-medium">Principal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        {#each productos as prod (prod.id)}
                                            <tr class="hover:bg-muted/30">
                                                <td class="px-4 py-2 font-mono text-xs text-muted-foreground">{prod.codigo}</td>
                                                <td class="px-4 py-2">{prod.nombre}</td>
                                                <td class="px-4 py-2 text-right font-medium">{cop.format(prod.pivot.precio_compra)}</td>
                                                <td class="px-4 py-2 text-center text-muted-foreground">
                                                    {prod.pivot.tiempo_entrega != null ? `${prod.pivot.tiempo_entrega} días` : '—'}
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    {#if prod.pivot.es_principal}
                                                        <CheckCircle class="h-4 w-4 text-green-600 mx-auto" />
                                                    {/if}
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        {:else}
                            <p class="px-4 py-8 text-center text-sm text-muted-foreground">
                                Este proveedor no tiene productos asociados.
                            </p>
                        {/if}
                    </CardContent>
                </Card>

                <!-- Últimas órdenes -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle class="flex items-center gap-2 text-base">
                                <ShoppingCart class="h-4 w-4" />
                                Últimas Órdenes de Compra
                            </CardTitle>
                            <Button variant="outline" size="sm" href={`/compras/ordenes/create?proveedor_id=${proveedor.id}`}>
                                <Plus class="mr-1 h-3 w-3" />
                                Nueva Orden
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        {#if ordenes.length > 0}
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-muted/50 border-y">
                                        <tr>
                                            <th class="px-4 py-2 text-left font-medium"># OC</th>
                                            <th class="px-4 py-2 text-left font-medium">Fecha</th>
                                            <th class="px-4 py-2 text-center font-medium">Ítems</th>
                                            <th class="px-4 py-2 text-right font-medium">Total</th>
                                            <th class="px-4 py-2 text-center font-medium">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y">
                                        {#each ordenes as orden (orden.id)}
                                            <tr class="hover:bg-muted/30 cursor-pointer" onclick={() => router.visit(`/compras/ordenes/${orden.id}`)}>
                                                <td class="px-4 py-2 font-mono text-xs text-muted-foreground">OC-{String(orden.id).padStart(6, '0')}</td>
                                                <td class="px-4 py-2">{new Date(orden.fecha).toLocaleDateString('es-CO')}</td>
                                                <td class="px-4 py-2 text-center">{orden.detalles_count}</td>
                                                <td class="px-4 py-2 text-right font-medium">{cop.format(orden.total)}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <Badge class={estadoColors[orden.estado] ?? ''}>
                                                        {estadoLabels[orden.estado] ?? orden.estado}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        {/each}
                                    </tbody>
                                </table>
                            </div>
                        {:else}
                            <p class="px-4 py-8 text-center text-sm text-muted-foreground">
                                No hay órdenes de compra para este proveedor.
                            </p>
                        {/if}
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</AppLayout>
