<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { Clock, DollarSign, Lock, Unlock, ArrowLeft } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    // --- Types ---
    interface Persona { nombres: string; apellidos: string | null; }
    interface User { persona: Persona | null; }
    interface Colaborador { user: User | null; }
    interface Cajero { id: number; colaborador: Colaborador | null; }

    interface Caja { id: number; nombre: string; ubicacion: string | null; }

    interface Turno {
        id: number;
        estado: 'abierto' | 'cerrado';
        saldo_inicial: string;
        saldo_final: string | null;
        apertura: string;
        cierre: string | null;
        observaciones: string | null;
        caja: Caja;
        cajero: Cajero | null;
    }

    interface Cliente { persona: Persona | null; nombre?: string; }
    interface MedioPago { nombre: string; }
    interface PagoFactura { medio_pago: MedioPago | null; }
    interface Factura {
        id: number;
        numero?: string;
        total: string | number;
        created_at: string;
        cliente: Cliente | null;
        pago_factura: PagoFactura | null;
    }

    interface PaginationLink { url: string | null; label: string; active: boolean; }
    interface PaginatedFacturas {
        data: Factura[];
        links: PaginationLink[];
        meta: {
            current_page: number;
            last_page: number;
            total: number;
            from: number | null;
            to: number | null;
        };
    }

    interface PorMedioPago { medio_pago: string; total: string | number; }
    interface Resumen {
        total_ventas: string | number;
        por_medio_pago: PorMedioPago[];
    }

    let {
        turno,
        facturas,
        resumen,
    }: {
        turno: Turno;
        facturas: PaginatedFacturas;
        resumen: Resumen;
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Caja', href: '/caja' },
        { title: 'Turnos', href: '/caja/turnos' },
        { title: `Turno #${turno.id}`, href: `/caja/turnos/${turno.id}` },
    ]);

    function formatCOP(value: string | number | null | undefined): string {
        if (value === null || value === undefined) return '$0';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(Number(value));
    }

    function formatDateTime(dt: string): string {
        return new Date(dt).toLocaleString('es-CO', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });
    }

    function formatTime(dt: string): string {
        return new Date(dt).toLocaleTimeString('es-CO', {
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function cajeroNombre(): string {
        if (!turno.cajero?.colaborador?.user?.persona) return '—';
        const p = turno.cajero.colaborador.user.persona;
        return `${p.nombres} ${p.apellidos ?? ''}`.trim();
    }

    function clienteNombre(factura: Factura): string {
        if (factura.cliente?.nombre) return factura.cliente.nombre;
        if (factura.cliente?.persona) {
            const p = factura.cliente.persona;
            return `${p.nombres} ${p.apellidos ?? ''}`.trim();
        }
        return 'Consumidor final';
    }

    function goToPage(url: string | null) {
        if (url) router.get(url, {}, { preserveState: true });
    }
</script>

<AppHead title="Turno #{turno.id} — {turno.caja.nombre}" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">

        <!-- Header -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-3">
                <Button variant="ghost" size="sm" href="/caja/turnos">
                    <ArrowLeft class="size-4" />
                </Button>
                <div>
                    <h1 class="text-2xl font-bold">Turno #{turno.id}</h1>
                    <p class="text-sm text-muted-foreground">{turno.caja.nombre}{turno.caja.ubicacion ? ` — ${turno.caja.ubicacion}` : ''}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                {#if turno.estado === 'abierto'}
                    <Badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200">
                        <Unlock class="mr-1 size-3" />
                        Abierto
                    </Badge>
                    <Button variant="destructive" size="sm" href={`/caja/turnos/${turno.id}/cerrar`}>
                        <Lock class="mr-1 size-4" />
                        Cerrar Turno
                    </Button>
                {:else}
                    <Badge variant="secondary">
                        <Lock class="mr-1 size-3" />
                        Cerrado
                    </Badge>
                {/if}
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Columna izquierda: Info del turno + resumen -->
            <div class="space-y-4 lg:col-span-1">

                <!-- Datos del turno -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-semibold">Información del Turno</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2.5 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Cajero</span>
                            <span class="font-medium text-right">{cajeroNombre()}</span>
                        </div>
                        <Separator />
                        <div class="flex justify-between items-center">
                            <span class="text-muted-foreground">Apertura</span>
                            <span class="flex items-center gap-1 text-right">
                                <Clock class="size-3 text-muted-foreground" />
                                {formatDateTime(turno.apertura)}
                            </span>
                        </div>
                        {#if turno.cierre}
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground">Cierre</span>
                                <span class="flex items-center gap-1 text-right">
                                    <Clock class="size-3 text-muted-foreground" />
                                    {formatDateTime(turno.cierre)}
                                </span>
                            </div>
                        {/if}
                        <Separator />
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Saldo inicial</span>
                            <span class="font-medium">{formatCOP(turno.saldo_inicial)}</span>
                        </div>
                        {#if turno.saldo_final !== null}
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Saldo final</span>
                                <span class="font-medium">{formatCOP(turno.saldo_final)}</span>
                            </div>
                        {/if}
                        {#if turno.observaciones}
                            <Separator />
                            <div>
                                <p class="text-muted-foreground mb-1">Observaciones</p>
                                <p class="text-xs">{turno.observaciones}</p>
                            </div>
                        {/if}
                    </CardContent>
                </Card>

                <!-- Resumen de ventas -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-sm font-semibold">Resumen de Ventas</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-muted-foreground">Total ventas</span>
                            <span class="text-xl font-bold text-green-700 dark:text-green-400">
                                {formatCOP(resumen.total_ventas)}
                            </span>
                        </div>

                        {#if resumen.por_medio_pago.length > 0}
                            <Separator />
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">Por medio de pago</p>
                            {#each resumen.por_medio_pago as item (item.medio_pago)}
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-foreground">{item.medio_pago}</span>
                                    <span class="font-medium">{formatCOP(item.total)}</span>
                                </div>
                            {/each}
                        {/if}
                    </CardContent>
                </Card>
            </div>

            <!-- Columna derecha: Facturas -->
            <div class="lg:col-span-2">
                <Card>
                    <CardHeader class="pb-3">
                        <div class="flex items-center justify-between">
                            <CardTitle class="text-sm font-semibold">Facturas del Turno</CardTitle>
                            <Badge variant="outline">{facturas.meta.total} facturas</Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Número</th>
                                        <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Cliente</th>
                                        <th class="px-4 py-2.5 text-right font-medium text-muted-foreground">Total</th>
                                        <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Medio de Pago</th>
                                        <th class="px-4 py-2.5 text-left font-medium text-muted-foreground">Hora</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    {#each facturas.data as factura (factura.id)}
                                        <tr class="hover:bg-muted/30 transition-colors">
                                            <td class="px-4 py-2.5 font-mono text-xs text-muted-foreground">
                                                {factura.numero ?? `#${factura.id}`}
                                            </td>
                                            <td class="px-4 py-2.5">{clienteNombre(factura)}</td>
                                            <td class="px-4 py-2.5 text-right font-medium">
                                                {formatCOP(factura.total)}
                                            </td>
                                            <td class="px-4 py-2.5 text-muted-foreground">
                                                {factura.pago_factura?.medio_pago?.nombre ?? '—'}
                                            </td>
                                            <td class="px-4 py-2.5 text-muted-foreground text-xs">
                                                {formatTime(factura.created_at)}
                                            </td>
                                        </tr>
                                    {:else}
                                        <tr>
                                            <td colspan="5" class="px-4 py-10 text-center text-muted-foreground">
                                                No hay facturas registradas en este turno.
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginación -->
                        {#if facturas.meta.last_page > 1}
                            <div class="flex items-center justify-between border-t px-4 py-3">
                                <p class="text-xs text-muted-foreground">
                                    {facturas.meta.from ?? 0}–{facturas.meta.to ?? 0} de {facturas.meta.total}
                                </p>
                                <div class="flex gap-1">
                                    {#each facturas.links as link, i (i)}
                                        <Button
                                            variant={link.active ? 'default' : 'outline'}
                                            size="sm"
                                            disabled={!link.url}
                                            onclick={() => goToPage(link.url)}
                                            class="min-w-8 text-xs"
                                        >
                                            {@html link.label}
                                        </Button>
                                    {/each}
                                </div>
                            </div>
                        {/if}
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</AppLayout>
