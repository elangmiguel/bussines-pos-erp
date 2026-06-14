<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronUp from 'lucide-svelte/icons/chevron-up';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import User from 'lucide-svelte/icons/user';
    import Building2 from 'lucide-svelte/icons/building-2';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import { formatCOP, formatFecha, formatFechaHora } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    // medio_pago_id is required by AbonoRequest (exists:medios_pago,id).
    // Since medios_pago aren't passed as props, we use a hardcoded map of common
    // IDs. The server must have these seeded. Adjust IDs if needed.
    const MEDIOS_PAGO = [
        { id: 1, nombre: 'Efectivo' },
        { id: 2, nombre: 'Transferencia' },
        { id: 3, nombre: 'Nequi' },
        { id: 4, nombre: 'Daviplata' },
        { id: 5, nombre: 'Tarjeta débito' },
        { id: 6, nombre: 'Tarjeta crédito' },
    ];

    interface Persona {
        nombres: string;
        apellidos: string | null;
    }

    interface Empresa {
        razon_social: string;
        nit: string;
    }

    interface Cliente {
        id: number;
        nombre: string;
        identificacion: string;
        tipo: 'natural' | 'juridico';
        credito_activo: boolean;
        limite_credito: number | string | null;
        plazo_dias: number | null;
        persona: Persona | null;
        empresa: Empresa | null;
    }

    interface MedioPago {
        nombre: string;
    }

    interface UserRef {
        name: string;
    }

    interface Abono {
        id: number;
        monto: number | string;
        fecha: string;
        medioPago: MedioPago | null;
        user: UserRef | null;
        observaciones: string | null;
    }

    interface FacturaRef {
        numero_completo: string;
        fecha: string;
        total: number | string;
    }

    interface CuentaPorCobrar {
        id: number;
        factura: FacturaRef | null;
        monto_total: number | string;
        monto_pagado: number | string;
        saldo: number | string;
        fecha_vencimiento: string;
        estado: 'pendiente' | 'parcial' | 'vencida' | 'pagada';
        abonos: Abono[];
    }

    let {
        cliente,
        cuentas,
    }: {
        cliente: Cliente;
        cuentas: CuentaPorCobrar[];
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Cartera', href: '/cartera' },
        { title: cliente.nombre, href: `/cartera/${cliente.id}` },
    ]);

    // Per-cuenta UI state: abonos expanded and form state
    let abonosExpanded = $state<Record<number, boolean>>({});
    let formaMonto = $state<Record<number, string>>({});
    let formaMedioPago = $state<Record<number, string>>({});
    let formaObs = $state<Record<number, string>>({});
    let formProcessing = $state<Record<number, boolean>>({});
    let formErrors = $state<Record<number, Record<string, string>>>({});

    function toggleAbonos(id: number) {
        abonosExpanded[id] = !abonosExpanded[id];
    }

    function numVal(v: number | string | null | undefined): number {
        if (v === null || v === undefined) return 0;
        return Number(v);
    }

    function saldoCuenta(cuenta: CuentaPorCobrar): number {
        if (cuenta.saldo !== undefined && cuenta.saldo !== null) return numVal(cuenta.saldo);
        return numVal(cuenta.monto_total) - numVal(cuenta.monto_pagado);
    }

    function estadoBadgeClass(estado: string): string {
        const map: Record<string, string> = {
            pendiente: 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900 dark:text-yellow-100',
            parcial:   'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900 dark:text-blue-100',
            vencida:   'bg-red-100 text-red-800 border-red-200 dark:bg-red-900 dark:text-red-100',
            pagada:    'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100',
        };
        return map[estado] ?? '';
    }

    function estadoLabel(estado: string): string {
        const map: Record<string, string> = {
            pendiente: 'Pendiente', parcial: 'Parcial', vencida: 'Vencida', pagada: 'Pagada',
        };
        return map[estado] ?? estado;
    }

    function submitAbono(cuenta: CuentaPorCobrar) {
        const id = cuenta.id;
        const monto = formaMonto[id] ?? '';
        const medioPagoId = formaMedioPago[id] ?? '';
        const obs = formaObs[id] ?? '';

        formErrors[id] = {};

        if (!monto || Number(monto) <= 0) {
            formErrors[id] = { ...formErrors[id], monto: 'El monto debe ser mayor a cero.' };
            return;
        }
        if (!medioPagoId) {
            formErrors[id] = { ...formErrors[id], medio_pago_id: 'Seleccione un medio de pago.' };
            return;
        }

        formProcessing[id] = true;

        router.post(
            '/cartera/abonar',
            {
                cuenta_cobrar_id: id,
                monto: Number(monto),
                medio_pago_id: Number(medioPagoId),
                observaciones: obs || null,
            },
            {
                onSuccess: () => {
                    formaMonto[id] = '';
                    formaMedioPago[id] = '';
                    formaObs[id] = '';
                    formErrors[id] = {};
                },
                onError: (errors) => {
                    formErrors[id] = errors as Record<string, string>;
                },
                onFinish: () => {
                    formProcessing[id] = false;
                },
                preserveScroll: true,
            }
        );
    }
</script>

<AppHead title="Cartera — {cliente.nombre}" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header cliente -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        {#if cliente.tipo === 'natural'}
                            <div class="flex size-10 items-center justify-center rounded-full bg-muted">
                                <User class="size-5 text-muted-foreground" />
                            </div>
                        {:else}
                            <div class="flex size-10 items-center justify-center rounded-full bg-muted">
                                <Building2 class="size-5 text-muted-foreground" />
                            </div>
                        {/if}
                        <div>
                            <h1 class="text-xl font-bold">{cliente.nombre}</h1>
                            <p class="font-mono text-sm text-muted-foreground">{cliente.identificacion}</p>
                            {#if cliente.empresa}
                                <p class="text-sm text-muted-foreground">{cliente.empresa.razon_social} · NIT {cliente.empresa.nit}</p>
                            {/if}
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <CreditCard class="size-4 text-muted-foreground" />
                            <span class="text-muted-foreground">Crédito:</span>
                            {#if cliente.credito_activo}
                                <Badge class="bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-100">Activo</Badge>
                            {:else}
                                <Badge variant="secondary">Inactivo</Badge>
                            {/if}
                        </div>
                        {#if cliente.limite_credito}
                            <div>
                                <span class="text-muted-foreground">Límite: </span>
                                <span class="font-semibold">{formatCOP(numVal(cliente.limite_credito))}</span>
                            </div>
                        {/if}
                        {#if cliente.plazo_dias}
                            <div>
                                <span class="text-muted-foreground">Plazo: </span>
                                <span class="font-semibold">{cliente.plazo_dias} días</span>
                            </div>
                        {/if}
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Cuentas por cobrar -->
        {#if cuentas.length === 0}
            <Card>
                <CardContent class="py-12 text-center text-muted-foreground">
                    Este cliente no tiene cuentas por cobrar registradas.
                </CardContent>
            </Card>
        {:else}
            {#each cuentas as cuenta (cuenta.id)}
                {@const saldo = saldoCuenta(cuenta)}
                {@const pagada = cuenta.estado === 'pagada'}
                <Card class={pagada ? 'opacity-70' : ''}>
                    <CardHeader class="pb-3">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <CardTitle class="text-base">
                                    {cuenta.factura ? `Factura ${cuenta.factura.numero_completo}` : `Cuenta #${cuenta.id}`}
                                </CardTitle>
                                {#if cuenta.factura}
                                    <p class="text-xs text-muted-foreground mt-0.5">
                                        Emitida el {formatFecha(cuenta.factura.fecha)}
                                    </p>
                                {/if}
                            </div>
                            <Badge class={estadoBadgeClass(cuenta.estado)}>
                                {estadoLabel(cuenta.estado)}
                            </Badge>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-4">
                        <!-- Resumen financiero -->
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div class="rounded-lg border bg-muted/30 px-3 py-2">
                                <p class="text-xs text-muted-foreground">Total factura</p>
                                <p class="font-semibold">{formatCOP(numVal(cuenta.monto_total))}</p>
                            </div>
                            <div class="rounded-lg border bg-muted/30 px-3 py-2">
                                <p class="text-xs text-muted-foreground">Pagado</p>
                                <p class="font-semibold text-green-700 dark:text-green-400">{formatCOP(numVal(cuenta.monto_pagado))}</p>
                            </div>
                            <div class="rounded-lg border bg-muted/30 px-3 py-2">
                                <p class="text-xs text-muted-foreground">Saldo pendiente</p>
                                <p class="font-bold {saldo > 0 ? 'text-destructive' : 'text-green-600'}">{formatCOP(saldo)}</p>
                            </div>
                            <div class="rounded-lg border bg-muted/30 px-3 py-2">
                                <p class="text-xs text-muted-foreground">Vencimiento</p>
                                <p class="font-semibold">{formatFecha(cuenta.fecha_vencimiento)}</p>
                            </div>
                        </div>

                        <!-- Toggle abonos -->
                        {#if cuenta.abonos.length > 0}
                            <div>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onclick={() => toggleAbonos(cuenta.id)}
                                >
                                    {#if abonosExpanded[cuenta.id]}
                                        <ChevronUp class="mr-1.5 size-4" />
                                    {:else}
                                        <ChevronDown class="mr-1.5 size-4" />
                                    {/if}
                                    {cuenta.abonos.length} {cuenta.abonos.length === 1 ? 'abono' : 'abonos'} registrados
                                </Button>

                                {#if abonosExpanded[cuenta.id]}
                                    <div class="mt-3 overflow-x-auto rounded-lg border">
                                        <table class="w-full text-sm">
                                            <thead class="border-b bg-muted/50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left font-medium text-muted-foreground">Fecha</th>
                                                    <th class="px-3 py-2 text-right font-medium text-muted-foreground">Monto</th>
                                                    <th class="px-3 py-2 text-left font-medium text-muted-foreground">Medio de Pago</th>
                                                    <th class="px-3 py-2 text-left font-medium text-muted-foreground">Registrado por</th>
                                                    <th class="px-3 py-2 text-left font-medium text-muted-foreground">Observaciones</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y">
                                                {#each cuenta.abonos as abono (abono.id)}
                                                    <tr class="hover:bg-muted/30">
                                                        <td class="px-3 py-2 text-xs text-muted-foreground">
                                                            {formatFechaHora(abono.fecha)}
                                                        </td>
                                                        <td class="px-3 py-2 text-right font-medium text-green-700 dark:text-green-400">
                                                            {formatCOP(numVal(abono.monto))}
                                                        </td>
                                                        <td class="px-3 py-2 text-muted-foreground">
                                                            {abono.medioPago?.nombre ?? '—'}
                                                        </td>
                                                        <td class="px-3 py-2 text-muted-foreground">
                                                            {abono.user?.name ?? '—'}
                                                        </td>
                                                        <td class="px-3 py-2 text-muted-foreground text-xs">
                                                            {abono.observaciones ?? '—'}
                                                        </td>
                                                    </tr>
                                                {/each}
                                            </tbody>
                                        </table>
                                    </div>
                                {/if}
                            </div>
                        {/if}

                        <!-- Formulario de abono (solo si no está pagada) -->
                        {#if !pagada}
                            <Separator />
                            <div class="rounded-lg border border-dashed bg-muted/20 p-4">
                                <h4 class="mb-3 text-sm font-semibold">Registrar abono</h4>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div class="space-y-1.5">
                                        <Label for="monto-{cuenta.id}">
                                            Monto <span class="text-destructive">*</span>
                                        </Label>
                                        <Input
                                            id="monto-{cuenta.id}"
                                            type="number"
                                            min="1"
                                            max={saldo}
                                            step="1"
                                            placeholder="0"
                                            class={formErrors[cuenta.id]?.monto ? 'border-destructive' : ''}
                                            bind:value={formaMonto[cuenta.id]}
                                        />
                                        {#if formErrors[cuenta.id]?.monto}
                                            <p class="text-xs text-destructive">{formErrors[cuenta.id].monto}</p>
                                        {/if}
                                        <p class="text-xs text-muted-foreground">Máx: {formatCOP(saldo)}</p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label for="medio-{cuenta.id}">
                                            Medio de pago <span class="text-destructive">*</span>
                                        </Label>
                                        <select
                                            id="medio-{cuenta.id}"
                                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:border-ring {formErrors[cuenta.id]?.medio_pago_id ? 'border-destructive' : ''}"
                                            bind:value={formaMedioPago[cuenta.id]}
                                        >
                                            <option value="">Seleccionar...</option>
                                            {#each MEDIOS_PAGO as mp (mp.id)}
                                                <option value={String(mp.id)}>{mp.nombre}</option>
                                            {/each}
                                        </select>
                                        {#if formErrors[cuenta.id]?.medio_pago_id}
                                            <p class="text-xs text-destructive">{formErrors[cuenta.id].medio_pago_id}</p>
                                        {/if}
                                    </div>

                                    <div class="space-y-1.5">
                                        <Label for="obs-{cuenta.id}">Observaciones</Label>
                                        <Input
                                            id="obs-{cuenta.id}"
                                            type="text"
                                            placeholder="Opcional..."
                                            bind:value={formaObs[cuenta.id]}
                                        />
                                    </div>
                                </div>

                                {#if formErrors[cuenta.id]?.monto && !formErrors[cuenta.id]?.monto.includes('mayor')}
                                    <p class="mt-2 text-xs text-destructive">{formErrors[cuenta.id].monto}</p>
                                {/if}

                                <div class="mt-3 flex justify-end">
                                    <Button
                                        size="sm"
                                        disabled={formProcessing[cuenta.id]}
                                        onclick={() => submitAbono(cuenta)}
                                    >
                                        {formProcessing[cuenta.id] ? 'Registrando...' : 'Registrar abono'}
                                    </Button>
                                </div>
                            </div>
                        {/if}
                    </CardContent>
                </Card>
            {/each}
        {/if}
    </div>
</AppLayout>
