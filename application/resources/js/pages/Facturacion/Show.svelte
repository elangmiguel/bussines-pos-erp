<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import {
        FileText,
        Download,
        Printer,
        XCircle,
        CheckCircle,
        Clock,
        RotateCcw,
        AlertTriangle,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        CardDescription,
    } from '@/components/ui/card';
    import type { BreadcrumbItem } from '@/types';

    // ---------- types ----------
    interface Persona {
        nombre: string;
        apellido?: string | null;
        numero_identificacion?: string | null;
        email?: string | null;
        telefono?: string | null;
        direccion?: string | null;
    }

    interface EmpresaData {
        razon_social: string;
        nit: string;
        digito_verificacion?: string | null;
        email?: string | null;
        telefono?: string | null;
        direccion?: string | null;
        ciudad?: string | null;
        regimen_tributario?: string | null;
    }

    interface Cliente {
        id: number;
        tipo: 'natural' | 'juridico';
        nombre: string;
        identificacion: string;
        persona?: Persona | null;
        empresa?: EmpresaData | null;
    }

    interface TarifaIva {
        id: number;
        porcentaje: number;
        nombre: string;
    }

    interface Producto {
        id: number;
        nombre: string;
        referencia?: string | null;
    }

    interface DetalleFactura {
        id: number;
        descripcion?: string | null;
        cantidad: number;
        precio_unitario: number;
        descuento_pct: number;
        subtotal: number;
        iva: number;
        total: number;
        producto?: Producto | null;
        tarifa_iva?: TarifaIva | null;
    }

    interface MedioPago {
        id: number;
        nombre: string;
    }

    interface PagoFactura {
        id: number;
        monto: number;
        referencia?: string | null;
        medio_pago?: MedioPago | null;
    }

    interface NotaCredito {
        id: number;
        numero_completo: string;
        fecha: string;
        motivo: string;
        descripcion: string;
        subtotal: number;
        iva: number;
        total: number;
        estado: string;
        estado_dian: string;
        user?: { name: string } | null;
    }

    interface NotaDebito {
        id: number;
        numero_completo: string;
        fecha: string;
        motivo: string;
        subtotal: number;
        iva: number;
        total: number;
        estado: string;
        estado_dian: string;
        user?: { name: string } | null;
    }

    interface CuentaPorCobrar {
        id: number;
        monto_total: number;
        monto_pagado: number;
        saldo: number;
        fecha_vencimiento: string;
        estado: 'pendiente' | 'parcial' | 'pagada' | 'vencida';
    }

    interface Resolucion {
        numero_resolucion: string;
        prefijo: string;
        fecha_inicio: string;
        fecha_fin: string;
    }

    interface Factura {
        id: number;
        numero_completo: string;
        fecha: string;
        fecha_vencimiento?: string | null;
        tipo_pago: 'contado' | 'credito';
        subtotal: number;
        descuento_global: number;
        base_iva_0: number;
        base_iva_5: number;
        base_iva_19: number;
        iva_5: number;
        iva_19: number;
        inc: number;
        total: number;
        estado: 'borrador' | 'emitida' | 'anulada';
        estado_dian: 'pendiente' | 'aceptada' | 'rechazada';
        cufe?: string | null;
        qr_data?: string | null;
        xml_dian?: string | null;
        observaciones?: string | null;
        cliente?: Cliente | null;
        user?: { name: string } | null;
        resolucion?: Resolucion | null;
        detalles: DetalleFactura[];
        pagos: PagoFactura[];
        notas_credito: NotaCredito[];
        notas_debito: NotaDebito[];
        cuenta_por_cobrar?: CuentaPorCobrar | null;
    }

    interface ConfiguracionData {
        logo?: string | null;
        empresa?: EmpresaData & { nit_completo?: string } | null;
    }

    let {
        factura,
        configuracion,
    }: {
        factura: Factura;
        configuracion: ConfiguracionData | null;
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Facturación', href: '/facturacion' },
        { title: factura.numero_completo, href: '#' },
    ]);

    let confirmandoAnular = $state(false);

    const formatCOP = (value: number) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(value ?? 0);

    const formatDate = (date: string | null | undefined, withTime = false) => {
        if (!date) return '—';
        return new Intl.DateTimeFormat('es-CO', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            ...(withTime ? { hour: '2-digit', minute: '2-digit' } : {}),
        }).format(new Date(date));
    };

    function estadoBadgeClass(e: string): string {
        if (e === 'emitida')  return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        if (e === 'anulada')  return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        return 'bg-gray-100 text-gray-700 dark:bg-gray-700/40 dark:text-gray-300';
    }

    function dianBadgeClass(ed: string): string {
        if (ed === 'aceptada')  return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        if (ed === 'rechazada') return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    }

    function motivoNC(m: string): string {
        const map: Record<string, string> = {
            devolucion: 'Devolución',
            descuento: 'Descuento',
            anulacion: 'Anulación',
            error: 'Error en facturación',
        };
        return map[m] ?? m;
    }

    function cpcEstadoLabel(e: string): string {
        if (e === 'pendiente') return 'Pendiente';
        if (e === 'parcial')   return 'Pago parcial';
        if (e === 'pagada')    return 'Pagada';
        if (e === 'vencida')   return 'Vencida';
        return e;
    }

    function cpcBadgeClass(e: string): string {
        if (e === 'pagada')    return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        if (e === 'vencida')   return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        if (e === 'parcial')   return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
    }

    function doAnular() {
        router.patch(`/facturacion/${factura.id}/anular`, {}, {
            onSuccess: () => { confirmandoAnular = false; },
        });
    }

    const totalIva = $derived((factura.iva_5 ?? 0) + (factura.iva_19 ?? 0));
</script>

<AppHead title={`Factura ${factura.numero_completo}`} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">

        <!-- Barra de acciones -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h1 class="font-mono text-xl font-bold">{factura.numero_completo}</h1>
                <span class={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${estadoBadgeClass(factura.estado)}`}>
                    {factura.estado === 'emitida' ? 'Emitida' : factura.estado === 'anulada' ? 'Anulada' : 'Borrador'}
                </span>
                <span class={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${dianBadgeClass(factura.estado_dian)}`}>
                    DIAN: {factura.estado_dian === 'aceptada' ? 'Aceptada' : factura.estado_dian === 'rechazada' ? 'Rechazada' : 'Pendiente'}
                </span>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" href={`/facturacion/${factura.id}/pdf`}>
                    <Printer class="mr-2 size-4" />
                    Descargar PDF
                </Button>
                {#if factura.xml_dian}
                    <Button variant="outline" size="sm" href={`/facturacion/${factura.id}/xml`}>
                        <Download class="mr-2 size-4" />
                        Descargar XML
                    </Button>
                {/if}
                {#if factura.estado === 'emitida'}
                    <Button variant="outline" size="sm" href={`/facturacion/${factura.id}/nota-credito/create`}>
                        <RotateCcw class="mr-2 size-4" />
                        Nota Crédito
                    </Button>
                    <Button variant="outline" size="sm" href={`/facturacion/${factura.id}/nota-debito/create`}>
                        <FileText class="mr-2 size-4" />
                        Nota Débito
                    </Button>
                    <Button
                        variant="destructive"
                        size="sm"
                        onclick={() => (confirmandoAnular = true)}
                    >
                        <XCircle class="mr-2 size-4" />
                        Anular
                    </Button>
                {/if}
            </div>
        </div>

        <!-- Emisor / Receptor -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Emisor -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-medium text-muted-foreground uppercase tracking-wide">Emisor</CardTitle>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    {#if configuracion?.empresa}
                        <p class="font-semibold">{configuracion.empresa.razon_social}</p>
                        <p class="text-muted-foreground">NIT: {configuracion.empresa.nit}-{configuracion.empresa.digito_verificacion ?? ''}</p>
                        {#if configuracion.empresa.direccion}<p class="text-muted-foreground">{configuracion.empresa.direccion}</p>{/if}
                        {#if configuracion.empresa.ciudad}<p class="text-muted-foreground">{configuracion.empresa.ciudad}</p>{/if}
                        {#if configuracion.empresa.telefono}<p class="text-muted-foreground">Tel: {configuracion.empresa.telefono}</p>{/if}
                        {#if configuracion.empresa.email}<p class="text-muted-foreground">{configuracion.empresa.email}</p>{/if}
                        {#if configuracion.empresa.regimen_tributario}<p class="text-muted-foreground">Régimen: {configuracion.empresa.regimen_tributario}</p>{/if}
                    {:else}
                        <p class="text-muted-foreground">Sin configuración de empresa.</p>
                    {/if}
                    {#if factura.resolucion}
                        <p class="mt-2 text-xs text-muted-foreground border-t pt-2">
                            Res. DIAN N° {factura.resolucion.numero_resolucion}
                            — vigencia {formatDate(factura.resolucion.fecha_inicio)} al {formatDate(factura.resolucion.fecha_fin)}
                        </p>
                    {/if}
                </CardContent>
            </Card>

            <!-- Receptor -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-sm font-medium text-muted-foreground uppercase tracking-wide">Cliente</CardTitle>
                </CardHeader>
                <CardContent class="space-y-1 text-sm">
                    {#if factura.cliente}
                        <p class="font-semibold">{factura.cliente.nombre}</p>
                        <p class="font-mono text-muted-foreground">{factura.cliente.identificacion}</p>
                        {#if factura.cliente.tipo === 'natural' && factura.cliente.persona}
                            {#if factura.cliente.persona.direccion}<p class="text-muted-foreground">{factura.cliente.persona.direccion}</p>{/if}
                            {#if factura.cliente.persona.email}<p class="text-muted-foreground">{factura.cliente.persona.email}</p>{/if}
                            {#if factura.cliente.persona.telefono}<p class="text-muted-foreground">Tel: {factura.cliente.persona.telefono}</p>{/if}
                        {:else if factura.cliente.tipo === 'juridico' && factura.cliente.empresa}
                            {#if factura.cliente.empresa.direccion}<p class="text-muted-foreground">{factura.cliente.empresa.direccion}</p>{/if}
                            {#if factura.cliente.empresa.email}<p class="text-muted-foreground">{factura.cliente.empresa.email}</p>{/if}
                            {#if factura.cliente.empresa.telefono}<p class="text-muted-foreground">Tel: {factura.cliente.empresa.telefono}</p>{/if}
                        {/if}
                    {:else}
                        <p class="text-muted-foreground">Consumidor Final</p>
                    {/if}
                    <div class="mt-2 flex gap-4 border-t pt-2 text-xs text-muted-foreground">
                        <span>Fecha: {formatDate(factura.fecha, true)}</span>
                        <span>Tipo pago: {factura.tipo_pago === 'contado' ? 'Contado' : 'Crédito'}</span>
                        {#if factura.fecha_vencimiento}
                            <span>Vence: {formatDate(factura.fecha_vencimiento)}</span>
                        {/if}
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- CUFE -->
        {#if factura.cufe}
            <Card class="bg-muted/30">
                <CardContent class="py-3">
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-muted-foreground">CUFE</p>
                    <p class="break-all font-mono text-xs text-muted-foreground">{factura.cufe}</p>
                </CardContent>
            </Card>
        {/if}

        <!-- Tabla de ítems -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Detalle de ítems</CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Descripción</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Cant.</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Precio Unit.</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Desc%</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Subtotal</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">IVA%</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">IVA</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each factura.detalles as det (det.id)}
                                <tr class="hover:bg-muted/20">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{det.descripcion ?? det.producto?.nombre ?? '—'}</p>
                                        {#if det.producto?.referencia}
                                            <p class="text-xs text-muted-foreground">Ref: {det.producto.referencia}</p>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3 text-right">{Number(det.cantidad).toFixed(2)}</td>
                                    <td class="px-4 py-3 text-right">{formatCOP(det.precio_unitario)}</td>
                                    <td class="px-4 py-3 text-right">{Number(det.descuento_pct).toFixed(1)}%</td>
                                    <td class="px-4 py-3 text-right">{formatCOP(det.subtotal)}</td>
                                    <td class="px-4 py-3 text-right">{det.tarifa_iva?.porcentaje ?? 0}%</td>
                                    <td class="px-4 py-3 text-right">{formatCOP(det.iva)}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{formatCOP(det.total)}</td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">Sin ítems.</td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Totales + Desglose tributario -->
        <div class="grid gap-4 md:grid-cols-2">
            <!-- Desglose tributario -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Resumen tributario</CardTitle>
                </CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <tbody class="divide-y">
                            <tr>
                                <td class="py-1.5 text-muted-foreground">Base excluida (0%)</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(factura.base_iva_0)}</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 text-muted-foreground">Base gravable 5%</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(factura.base_iva_5)}</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 text-muted-foreground">IVA 5%</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(factura.iva_5)}</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 text-muted-foreground">Base gravable 19%</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(factura.base_iva_19)}</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 text-muted-foreground">IVA 19%</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(factura.iva_19)}</td>
                            </tr>
                            {#if factura.inc > 0}
                                <tr>
                                    <td class="py-1.5 text-muted-foreground">INC</td>
                                    <td class="py-1.5 text-right font-mono">{formatCOP(factura.inc)}</td>
                                </tr>
                            {/if}
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <!-- Totales -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Totales</CardTitle>
                </CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <tbody class="divide-y">
                            <tr>
                                <td class="py-1.5 text-muted-foreground">Subtotal</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(factura.subtotal)}</td>
                            </tr>
                            <tr>
                                <td class="py-1.5 text-muted-foreground">IVA (5% + 19%)</td>
                                <td class="py-1.5 text-right font-mono">{formatCOP(totalIva)}</td>
                            </tr>
                            {#if factura.inc > 0}
                                <tr>
                                    <td class="py-1.5 text-muted-foreground">INC</td>
                                    <td class="py-1.5 text-right font-mono">{formatCOP(factura.inc)}</td>
                                </tr>
                            {/if}
                            {#if factura.descuento_global > 0}
                                <tr>
                                    <td class="py-1.5 text-muted-foreground">Descuento global</td>
                                    <td class="py-1.5 text-right font-mono text-destructive">- {formatCOP(factura.descuento_global)}</td>
                                </tr>
                            {/if}
                            <tr class="border-t-2">
                                <td class="py-2 text-base font-bold">TOTAL</td>
                                <td class="py-2 text-right font-mono text-base font-bold">{formatCOP(factura.total)}</td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>

        <!-- Pagos -->
        {#if factura.pagos && factura.pagos.length > 0}
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Pagos registrados</CardTitle>
                </CardHeader>
                <CardContent>
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="pb-2 text-left font-medium text-muted-foreground">Medio de pago</th>
                                <th class="pb-2 text-right font-medium text-muted-foreground">Monto</th>
                                <th class="pb-2 text-left font-medium text-muted-foreground">Referencia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each factura.pagos as pago (pago.id)}
                                <tr>
                                    <td class="py-2">{pago.medio_pago?.nombre ?? '—'}</td>
                                    <td class="py-2 text-right font-mono font-medium">{formatCOP(pago.monto)}</td>
                                    <td class="py-2 font-mono text-xs text-muted-foreground">{pago.referencia ?? '—'}</td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        {/if}

        <!-- Cuenta por cobrar (crédito) -->
        {#if factura.tipo_pago === 'credito' && factura.cuenta_por_cobrar}
            {@const cpc = factura.cuenta_por_cobrar}
            <Card class="border-blue-400/30">
                <CardHeader class="pb-3">
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-base">Cuenta por cobrar</CardTitle>
                        <span class={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${cpcBadgeClass(cpc.estado)}`}>
                            {cpcEstadoLabel(cpc.estado)}
                        </span>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-xs text-muted-foreground">Monto total</p>
                            <p class="font-mono font-semibold">{formatCOP(cpc.monto_total)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Pagado</p>
                            <p class="font-mono font-semibold text-green-600">{formatCOP(cpc.monto_pagado)}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Saldo pendiente</p>
                            <p class="font-mono font-bold {(cpc.saldo ?? cpc.monto_total - cpc.monto_pagado) > 0 ? 'text-destructive' : 'text-green-600'}">
                                {formatCOP(cpc.saldo ?? (cpc.monto_total - cpc.monto_pagado))}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Vencimiento</p>
                            <p class="font-medium">{formatDate(cpc.fecha_vencimiento)}</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        {/if}

        <!-- Notas Crédito -->
        {#if factura.notas_credito && factura.notas_credito.length > 0}
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Notas crédito</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Número</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Motivo</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Subtotal</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">IVA</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each factura.notas_credito as nc (nc.id)}
                                <tr class="hover:bg-muted/20">
                                    <td class="px-4 py-3 font-mono font-medium">{nc.numero_completo}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{formatDate(nc.fecha)}</td>
                                    <td class="px-4 py-3">{motivoNC(nc.motivo)}</td>
                                    <td class="px-4 py-3 text-right font-mono">{formatCOP(nc.subtotal)}</td>
                                    <td class="px-4 py-3 text-right font-mono">{formatCOP(nc.iva)}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold">{formatCOP(nc.total)}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            {nc.estado}
                                        </span>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        {/if}

        <!-- Notas Débito -->
        {#if factura.notas_debito && factura.notas_debito.length > 0}
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Notas débito</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Número</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Fecha</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Motivo</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Subtotal</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">IVA</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each factura.notas_debito as nd (nd.id)}
                                <tr class="hover:bg-muted/20">
                                    <td class="px-4 py-3 font-mono font-medium">{nd.numero_completo}</td>
                                    <td class="px-4 py-3 text-muted-foreground">{formatDate(nd.fecha)}</td>
                                    <td class="px-4 py-3">{nd.motivo}</td>
                                    <td class="px-4 py-3 text-right font-mono">{formatCOP(nd.subtotal)}</td>
                                    <td class="px-4 py-3 text-right font-mono">{formatCOP(nd.iva)}</td>
                                    <td class="px-4 py-3 text-right font-mono font-semibold">{formatCOP(nd.total)}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                            {nd.estado}
                                        </span>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        {/if}

        {#if factura.observaciones}
            <Card class="bg-muted/30">
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground uppercase tracking-wide">Observaciones</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-sm">{factura.observaciones}</p>
                </CardContent>
            </Card>
        {/if}

    </div>
</AppLayout>

<!-- Modal confirmar anulación -->
{#if confirmandoAnular}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="mx-4 w-full max-w-sm rounded-lg bg-background p-6 shadow-xl">
            <div class="mb-4 flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-full bg-destructive/10">
                    <AlertTriangle class="size-5 text-destructive" />
                </div>
                <div>
                    <h3 class="font-semibold">Anular factura</h3>
                    <p class="text-sm text-muted-foreground">{factura.numero_completo}</p>
                </div>
            </div>
            <p class="mb-6 text-sm text-muted-foreground">
                Esta acción revertirá el inventario de todos los productos facturados y no se puede deshacer.
                ¿Desea continuar?
            </p>
            <div class="flex justify-end gap-2">
                <Button variant="outline" onclick={() => (confirmandoAnular = false)}>Cancelar</Button>
                <Button variant="destructive" onclick={doAnular}>Confirmar anulación</Button>
            </div>
        </div>
    </div>
{/if}
