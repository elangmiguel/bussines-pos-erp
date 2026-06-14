<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import {
        User,
        Building2,
        CreditCard,
        CheckCircle,
        AlertCircle,
        Clock,
        Edit,
        Trash2,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        CardDescription,
    } from '@/components/ui/card';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    interface Persona {
        id: number;
        tipo_identificacion: string;
        numero_identificacion: string;
        nombres: string;
        apellidos: string | null;
        nombre_completo: string;
        email: string | null;
        telefono: string | null;
        celular: string | null;
        direccion: string | null;
        ciudad: string | null;
        departamento: string | null;
    }

    interface Empresa {
        id: number;
        razon_social: string;
        nit: string;
        digito_verificacion: string;
        nit_completo: string;
        regimen_tributario: string;
        email: string | null;
        telefono: string | null;
        direccion: string | null;
        ciudad: string | null;
    }

    interface Factura {
        id: number;
        numero: string;
        created_at: string;
        total: number;
        estado: string;
    }

    interface AbonoEntry {
        id: number;
        monto: number;
        fecha: string;
        observaciones: string | null;
    }

    interface CuentaPorCobrar {
        id: number;
        factura_id: number | null;
        monto_total: number;
        monto_pagado: number;
        saldo: number;
        fecha_vencimiento: string;
        estado: 'pendiente' | 'parcial' | 'pagada' | 'vencida';
        factura: Factura | null;
        abonos: AbonoEntry[];
    }

    interface MedioPago {
        id: number;
        nombre: string;
        tipo: string;
    }

    interface Cliente {
        id: number;
        tipo: 'natural' | 'juridico';
        tipo_cliente: 'regular' | 'frecuente' | 'corporativo';
        credito_activo: boolean;
        limite_credito: number;
        plazo_dias: number;
        observaciones: string | null;
        activo: boolean;
        nombre: string;
        identificacion: string;
        persona: Persona | null;
        empresa: Empresa | null;
    }

    let {
        cliente,
        facturas,
        cuentas,
        medios_pago,
    }: {
        cliente: Cliente;
        facturas: Factura[];
        cuentas: CuentaPorCobrar[];
        medios_pago: MedioPago[];
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clientes', href: '/clientes' },
        { title: cliente.nombre ?? 'Cliente', href: `/clientes/${cliente.id}` },
    ]);

    const formatCOP = (value: number) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(value);

    const formatDate = (date: string) =>
        new Intl.DateTimeFormat('es-CO', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
        }).format(new Date(date));

    const saldoTotal = $derived(
        cuentas.reduce((acc, c) => acc + (c.saldo ?? c.monto_total - c.monto_pagado), 0),
    );

    function estadoBadgeVariant(estado: string): 'default' | 'secondary' | 'destructive' | 'outline' {
        if (estado === 'pagada') return 'default';
        if (estado === 'parcial') return 'secondary';
        if (estado === 'vencida') return 'destructive';
        return 'outline';
    }

    function estadoLabel(estado: string): string {
        const labels: Record<string, string> = {
            pendiente: 'Pendiente',
            parcial: 'Parcial',
            pagada: 'Pagada',
            vencida: 'Vencida',
        };
        return labels[estado] ?? estado;
    }

    function facturaEstadoBadge(estado: string): 'default' | 'secondary' | 'destructive' | 'outline' {
        if (estado === 'pagada') return 'default';
        if (estado === 'anulada') return 'destructive';
        return 'secondary';
    }

    function tipoClienteLabel(tc: string): string {
        const labels: Record<string, string> = {
            regular: 'Regular',
            frecuente: 'Frecuente',
            corporativo: 'Corporativo',
        };
        return labels[tc] ?? tc;
    }

    // Abono forms per cuenta
    interface AbonoFormState {
        cuentaId: number;
        monto: number;
        medioPagoId: string;
        observaciones: string;
        processing: boolean;
        errors: Record<string, string>;
    }

    let abonoForms = $state<Record<number, AbonoFormState>>(
        untrack(() => Object.fromEntries(
            cuentas.map((c) => [
                c.id,
                {
                    cuentaId: c.id,
                    monto: 0,
                    medioPagoId: medios_pago[0]?.id?.toString() ?? '',
                    observaciones: '',
                    processing: false,
                    errors: {},
                },
            ]),
        )),
    );

    function submitAbono(cuentaId: number) {
        const abonoForm = useForm({
            cuenta_cobrar_id: cuentaId,
            medio_pago_id: abonoForms[cuentaId].medioPagoId,
            monto: abonoForms[cuentaId].monto,
            observaciones: abonoForms[cuentaId].observaciones,
        });
        abonoForm.post('/cartera/abonar', {
            preserveScroll: true,
            onError: (errors) => {
                abonoForms[cuentaId].errors = errors;
            },
            onSuccess: () => {
                abonoForms[cuentaId].monto = 0;
                abonoForms[cuentaId].observaciones = '';
                abonoForms[cuentaId].errors = {};
            },
        });
    }

    function isVencida(fecha: string): boolean {
        return new Date(fecha) < new Date();
    }
</script>

<AppHead title={`Cliente: ${cliente.nombre}`} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-12 items-center justify-center rounded-full bg-muted">
                    {#if cliente.tipo === 'natural'}
                        <User class="size-6 text-muted-foreground" />
                    {:else}
                        <Building2 class="size-6 text-muted-foreground" />
                    {/if}
                </div>
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">{cliente.nombre}</h1>
                    <p class="text-sm text-muted-foreground">{cliente.identificacion}</p>
                </div>
            </div>
            <div class="flex gap-2">
                <Button variant="outline" href={`/clientes/${cliente.id}/edit`}>
                    <Edit class="mr-2 size-4" />
                    Editar
                </Button>
                <Button variant="outline" href="/clientes">
                    Volver
                </Button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Columna izquierda: info + facturas -->
            <div class="space-y-6 lg:col-span-2">

                <!-- Información del cliente -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Información del Cliente</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="font-medium text-muted-foreground">Tipo</p>
                                <p class="capitalize">{cliente.tipo === 'natural' ? 'Persona Natural' : 'Persona Jurídica'}</p>
                            </div>
                            <div>
                                <p class="font-medium text-muted-foreground">Categoría</p>
                                <Badge variant="secondary">{tipoClienteLabel(cliente.tipo_cliente)}</Badge>
                            </div>
                            {#if cliente.tipo === 'natural' && cliente.persona}
                                <div>
                                    <p class="font-medium text-muted-foreground">Identificación</p>
                                    <p>{cliente.persona.tipo_identificacion} {cliente.persona.numero_identificacion}</p>
                                </div>
                                {#if cliente.persona.email}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Correo</p>
                                        <p>{cliente.persona.email}</p>
                                    </div>
                                {/if}
                                {#if cliente.persona.celular}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Celular</p>
                                        <p>{cliente.persona.celular}</p>
                                    </div>
                                {/if}
                                {#if cliente.persona.telefono}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Teléfono</p>
                                        <p>{cliente.persona.telefono}</p>
                                    </div>
                                {/if}
                                {#if cliente.persona.ciudad}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Ciudad</p>
                                        <p>{cliente.persona.ciudad}</p>
                                    </div>
                                {/if}
                                {#if cliente.persona.direccion}
                                    <div class="col-span-2">
                                        <p class="font-medium text-muted-foreground">Dirección</p>
                                        <p>{cliente.persona.direccion}</p>
                                    </div>
                                {/if}
                            {/if}
                            {#if cliente.tipo === 'juridico' && cliente.empresa}
                                <div>
                                    <p class="font-medium text-muted-foreground">NIT</p>
                                    <p>{cliente.empresa.nit_completo ?? `${cliente.empresa.nit}-${cliente.empresa.digito_verificacion}`}</p>
                                </div>
                                <div>
                                    <p class="font-medium text-muted-foreground">Régimen</p>
                                    <p>{cliente.empresa.regimen_tributario === 'responsable_iva' ? 'Responsable de IVA' : 'No responsable de IVA'}</p>
                                </div>
                                {#if cliente.empresa.email}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Correo</p>
                                        <p>{cliente.empresa.email}</p>
                                    </div>
                                {/if}
                                {#if cliente.empresa.telefono}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Teléfono</p>
                                        <p>{cliente.empresa.telefono}</p>
                                    </div>
                                {/if}
                                {#if cliente.empresa.ciudad}
                                    <div>
                                        <p class="font-medium text-muted-foreground">Ciudad</p>
                                        <p>{cliente.empresa.ciudad}</p>
                                    </div>
                                {/if}
                                {#if cliente.empresa.direccion}
                                    <div class="col-span-2">
                                        <p class="font-medium text-muted-foreground">Dirección</p>
                                        <p>{cliente.empresa.direccion}</p>
                                    </div>
                                {/if}
                            {/if}
                            {#if cliente.observaciones}
                                <div class="col-span-2">
                                    <p class="font-medium text-muted-foreground">Observaciones</p>
                                    <p class="text-muted-foreground">{cliente.observaciones}</p>
                                </div>
                            {/if}
                        </div>
                    </CardContent>
                </Card>

                <!-- Facturas recientes -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Facturas Recientes</CardTitle>
                        <CardDescription>Últimas 5 facturas del cliente</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        {#if facturas.length > 0}
                            <table class="w-full text-sm">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">N° Factura</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Fecha</th>
                                        <th class="px-4 py-2 text-right font-medium text-muted-foreground">Total</th>
                                        <th class="px-4 py-2 text-left font-medium text-muted-foreground">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    {#each facturas as factura (factura.id)}
                                        <tr class="hover:bg-muted/30">
                                            <td class="px-4 py-2 font-mono text-xs">{factura.numero ?? `#${factura.id}`}</td>
                                            <td class="px-4 py-2 text-muted-foreground">{formatDate(factura.created_at)}</td>
                                            <td class="px-4 py-2 text-right font-medium">{formatCOP(factura.total ?? 0)}</td>
                                            <td class="px-4 py-2">
                                                <Badge variant={facturaEstadoBadge(factura.estado)}>
                                                    {factura.estado ?? 'activa'}
                                                </Badge>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        {:else}
                            <div class="px-4 py-8 text-center text-sm text-muted-foreground">
                                Este cliente no tiene facturas registradas.
                            </div>
                        {/if}
                    </CardContent>
                </Card>

                <!-- Cartera / Cuentas por cobrar -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Cartera Pendiente</CardTitle>
                        <CardDescription>Cuentas por cobrar pendientes y parciales</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        {#if cuentas.length > 0}
                            {#each cuentas as cuenta (cuenta.id)}
                                <div class="rounded-lg border p-4 space-y-3">
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <p class="font-medium text-sm">
                                                Factura {cuenta.factura?.numero ?? `#${cuenta.factura_id}`}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                {#if isVencida(cuenta.fecha_vencimiento)}
                                                    <AlertCircle class="size-3.5 text-destructive" />
                                                    <span class="text-xs text-destructive">
                                                        Venció el {formatDate(cuenta.fecha_vencimiento)}
                                                    </span>
                                                {:else}
                                                    <Clock class="size-3.5 text-muted-foreground" />
                                                    <span class="text-xs text-muted-foreground">
                                                        Vence el {formatDate(cuenta.fecha_vencimiento)}
                                                    </span>
                                                {/if}
                                            </div>
                                        </div>
                                        <Badge variant={estadoBadgeVariant(cuenta.estado)}>
                                            {estadoLabel(cuenta.estado)}
                                        </Badge>
                                    </div>

                                    <div class="grid grid-cols-3 gap-2 text-sm">
                                        <div>
                                            <p class="text-xs text-muted-foreground">Total</p>
                                            <p class="font-medium">{formatCOP(cuenta.monto_total)}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-muted-foreground">Pagado</p>
                                            <p class="font-medium text-green-600">{formatCOP(cuenta.monto_pagado)}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-muted-foreground">Saldo</p>
                                            <p class="font-bold text-destructive">{formatCOP(cuenta.saldo ?? cuenta.monto_total - cuenta.monto_pagado)}</p>
                                        </div>
                                    </div>

                                    <Separator />

                                    <!-- Formulario de abono -->
                                    <div class="space-y-2">
                                        <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide">
                                            Registrar Abono
                                        </p>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="grid gap-1">
                                                <Label class="text-xs">Medio de Pago</Label>
                                                <Select
                                                    bind:value={abonoForms[cuenta.id].medioPagoId}
                                                    onValueChange={(v) => (abonoForms[cuenta.id].medioPagoId = v)}
                                                >
                                                    <SelectTrigger class="h-8 text-xs w-full">
                                                        {medios_pago.find(m => m.id.toString() === abonoForms[cuenta.id].medioPagoId)?.nombre ?? 'Seleccionar'}
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {#each medios_pago as mp (mp.id)}
                                                            <SelectItem value={mp.id.toString()}>
                                                                {mp.nombre}
                                                            </SelectItem>
                                                        {/each}
                                                    </SelectContent>
                                                </Select>
                                                {#if abonoForms[cuenta.id].errors.medio_pago_id}
                                                    <InputError message={abonoForms[cuenta.id].errors.medio_pago_id} />
                                                {/if}
                                            </div>

                                            <div class="grid gap-1">
                                                <Label class="text-xs">Monto del Abono</Label>
                                                <Input
                                                    type="number"
                                                    min="1"
                                                    step="1000"
                                                    class="h-8 text-xs"
                                                    bind:value={abonoForms[cuenta.id].monto}
                                                    placeholder="0"
                                                />
                                                {#if abonoForms[cuenta.id].errors.monto}
                                                    <InputError message={abonoForms[cuenta.id].errors.monto} />
                                                {/if}
                                            </div>
                                        </div>

                                        <div class="grid gap-1">
                                            <Label class="text-xs">Observaciones (opcional)</Label>
                                            <Input
                                                class="h-8 text-xs"
                                                bind:value={abonoForms[cuenta.id].observaciones}
                                                placeholder="Referencia de pago..."
                                            />
                                        </div>

                                        <Button
                                            size="sm"
                                            class="w-full"
                                            onclick={() => submitAbono(cuenta.id)}
                                            disabled={abonoForms[cuenta.id].monto <= 0}
                                        >
                                            <CheckCircle class="mr-2 size-3.5" />
                                            Registrar Abono
                                        </Button>
                                    </div>
                                </div>
                            {/each}
                        {:else}
                            <div class="py-8 text-center text-sm text-muted-foreground">
                                <CheckCircle class="mx-auto mb-2 size-8 text-green-500 opacity-50" />
                                Este cliente no tiene cartera pendiente.
                            </div>
                        {/if}
                    </CardContent>
                </Card>
            </div>

            <!-- Columna derecha: resumen crédito + saldo -->
            <div class="space-y-6">
                <!-- Saldo total -->
                {#if saldoTotal > 0}
                    <Card class="border-destructive/50 bg-destructive/5">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm text-destructive">Saldo Total Pendiente</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <p class="text-3xl font-bold text-destructive">{formatCOP(saldoTotal)}</p>
                            <p class="text-xs text-muted-foreground mt-1">
                                {cuentas.length} cuenta{cuentas.length !== 1 ? 's' : ''} pendiente{cuentas.length !== 1 ? 's' : ''}
                            </p>
                        </CardContent>
                    </Card>
                {:else}
                    <Card class="border-green-500/50 bg-green-500/5">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm text-green-700 dark:text-green-400">Sin Deuda Pendiente</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-2">
                                <CheckCircle class="size-6 text-green-500" />
                                <p class="text-sm text-muted-foreground">Cliente al día</p>
                            </div>
                        </CardContent>
                    </Card>
                {/if}

                <!-- Info de crédito -->
                {#if cliente.credito_activo}
                    <Card>
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm">
                                <CreditCard class="mr-2 inline-block size-4" />
                                Línea de Crédito
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Límite</span>
                                <span class="font-medium">{formatCOP(cliente.limite_credito)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Usado</span>
                                <span class="font-medium text-destructive">{formatCOP(saldoTotal)}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Disponible</span>
                                <span class="font-bold text-green-600">
                                    {formatCOP(Math.max(0, cliente.limite_credito - saldoTotal))}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Plazo</span>
                                <span class="font-medium">{cliente.plazo_dias} días</span>
                            </div>
                            <!-- Barra de uso -->
                            {#if cliente.limite_credito > 0}
                                {@const porcentaje = Math.min(100, (saldoTotal / cliente.limite_credito) * 100)}
                                <div>
                                    <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                                        <span>Uso del crédito</span>
                                        <span>{porcentaje.toFixed(0)}%</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-muted">
                                        <div
                                            class="h-2 rounded-full transition-all {porcentaje > 80 ? 'bg-destructive' : porcentaje > 50 ? 'bg-yellow-500' : 'bg-green-500'}"
                                            style="width: {porcentaje}%"
                                        ></div>
                                    </div>
                                </div>
                            {/if}
                        </CardContent>
                    </Card>
                {/if}

                <!-- Estado -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm">Estado del Cliente</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Estado</span>
                            {#if cliente.activo}
                                <Badge variant="default" class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">Activo</Badge>
                            {:else}
                                <Badge variant="secondary">Inactivo</Badge>
                            {/if}
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Crédito</span>
                            {#if cliente.credito_activo}
                                <Badge variant="default">Habilitado</Badge>
                            {:else}
                                <Badge variant="outline">Sin crédito</Badge>
                            {/if}
                        </div>
                    </CardContent>
                </Card>

                <!-- Acciones rápidas -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm">Acciones</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Button variant="outline" class="w-full justify-start" href={`/clientes/${cliente.id}/edit`}>
                            <Edit class="mr-2 size-4" />
                            Editar Cliente
                        </Button>
                        <Button variant="outline" class="w-full justify-start" href={`/cartera/cliente/${cliente.id}`}>
                            <CreditCard class="mr-2 size-4" />
                            Ver Cartera Completa
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</AppLayout>
