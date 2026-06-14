<script lang="ts">
    import { router, useForm } from '@inertiajs/svelte';
    import { RotateCcw, AlertTriangle } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
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
    import type { BreadcrumbItem } from '@/types';

    // ---------- types ----------
    interface Motivo {
        value: string;
        label: string;
    }

    interface TarifaIva {
        id: number;
        porcentaje: number;
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

    interface Factura {
        id: number;
        numero_completo: string;
        subtotal: number;
        iva_5: number;
        iva_19: number;
        total: number;
        detalles: DetalleFactura[];
    }

    let {
        factura,
        motivos,
        errors = {},
    }: {
        factura: Factura;
        motivos: Motivo[];
        errors?: Record<string, string>;
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Facturación', href: '/facturacion' },
        { title: factura.numero_completo, href: `/facturacion/${factura.id}` },
        { title: 'Nota Crédito', href: '#' },
    ]);

    // Form state
    let motivo      = $state('');
    let descripcion = $state('');
    let submitting  = $state(false);

    // Quantities to return (keyed by detalle_id)
    let cantidades = $state<Record<number, number>>({});

    // Initialize quantities to 0
    $effect(() => {
        const init: Record<number, number> = {};
        for (const det of factura.detalles) {
            init[det.id] = 0;
        }
        cantidades = init;
    });

    const formatCOP = (value: number) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(value ?? 0);

    // Live totals calculated from returned quantities
    const totalesDevolucion = $derived.by(() => {
        if (motivo !== 'devolucion') return { subtotal: 0, iva: 0, total: 0 };

        let subtotal = 0;
        let iva      = 0;

        for (const det of factura.detalles) {
            const devuelta = cantidades[det.id] ?? 0;
            if (devuelta <= 0) continue;
            const proporcion = Math.min(devuelta, Number(det.cantidad)) / Number(det.cantidad);
            subtotal += Number(det.subtotal) * proporcion;
            iva      += Number(det.iva) * proporcion;
        }

        return {
            subtotal: Math.round(subtotal),
            iva:      Math.round(iva),
            total:    Math.round(subtotal + iva),
        };
    });

    const totalesCompletos = $derived.by(() => {
        if (motivo === 'devolucion') return totalesDevolucion;
        return {
            subtotal: Number(factura.subtotal),
            iva:      Number(factura.iva_5) + Number(factura.iva_19),
            total:    Number(factura.total),
        };
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        if (submitting) return;
        submitting = true;

        const items = motivo === 'devolucion'
            ? factura.detalles
                .filter(det => (cantidades[det.id] ?? 0) > 0)
                .map(det => ({
                    detalle_id: det.id,
                    cantidad:   cantidades[det.id],
                }))
            : [];

        router.post(`/facturacion/${factura.id}/nota-credito`, {
            motivo,
            descripcion,
            items,
        }, {
            onFinish: () => { submitting = false; },
        });
    }
</script>

<AppHead title={`Nota Crédito — ${factura.numero_completo}`} />

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-3xl p-6">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-full bg-primary/10">
                <RotateCcw class="size-5 text-primary" />
            </div>
            <div>
                <h1 class="text-xl font-bold">Nueva Nota Crédito</h1>
                <p class="text-sm text-muted-foreground">Factura: <span class="font-mono font-medium">{factura.numero_completo}</span></p>
            </div>
        </div>

        <form onsubmit={handleSubmit} class="flex flex-col gap-5">

            <!-- Motivo -->
            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Motivo y descripción</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium" for="motivo">Motivo <span class="text-destructive">*</span></label>
                        <Select bind:value={motivo}>
                            <SelectTrigger id="motivo" class={errors.motivo ? 'border-destructive' : ''}>
                                {motivo ? motivos.find(m => m.value === motivo)?.label ?? 'Seleccione motivo' : 'Seleccione motivo'}
                            </SelectTrigger>
                            <SelectContent>
                                {#each motivos as m (m.value)}
                                    <SelectItem value={m.value}>{m.label}</SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                        {#if errors.motivo}
                            <p class="text-xs text-destructive">{errors.motivo}</p>
                        {/if}
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium" for="descripcion">Descripción <span class="text-destructive">*</span></label>
                        <textarea
                            id="descripcion"
                            class={[
                                'min-h-[80px] w-full rounded-md border bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none',
                                errors.descripcion ? 'border-destructive' : 'border-input',
                            ].join(' ')}
                            placeholder="Describa el motivo de la nota crédito (mínimo 5 caracteres)..."
                            bind:value={descripcion}
                        ></textarea>
                        {#if errors.descripcion}
                            <p class="text-xs text-destructive">{errors.descripcion}</p>
                        {/if}
                    </div>
                </CardContent>
            </Card>

            <!-- Ítems (solo si motivo = devolucion) -->
            {#if motivo === 'devolucion'}
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="text-base">Ítems a devolver</CardTitle>
                        <CardDescription>Ingrese la cantidad a devolver de cada producto (máx. cantidad original).</CardDescription>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="border-b bg-muted/50">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-medium text-muted-foreground">Producto</th>
                                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">Cant. Original</th>
                                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">Precio Unit.</th>
                                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">Subtotal</th>
                                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">IVA%</th>
                                        <th class="px-4 py-3 text-center font-medium text-muted-foreground">Cant. Devolver</th>
                                        <th class="px-4 py-3 text-right font-medium text-muted-foreground">Total a NC</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    {#each factura.detalles as det (det.id)}
                                        {@const devuelta = cantidades[det.id] ?? 0}
                                        {@const proporcion = devuelta > 0 ? Math.min(devuelta, Number(det.cantidad)) / Number(det.cantidad) : 0}
                                        {@const totalNC = Math.round((Number(det.total)) * proporcion)}
                                        <tr class={devuelta > 0 ? 'bg-primary/5' : 'hover:bg-muted/20'}>
                                            <td class="px-4 py-3">
                                                <p class="font-medium">{det.descripcion ?? det.producto?.nombre ?? '—'}</p>
                                                {#if det.producto?.referencia}
                                                    <p class="text-xs text-muted-foreground">Ref: {det.producto.referencia}</p>
                                                {/if}
                                            </td>
                                            <td class="px-4 py-3 text-right">{Number(det.cantidad).toFixed(2)}</td>
                                            <td class="px-4 py-3 text-right">{formatCOP(det.precio_unitario)}</td>
                                            <td class="px-4 py-3 text-right">{formatCOP(det.subtotal)}</td>
                                            <td class="px-4 py-3 text-right">{det.tarifa_iva?.porcentaje ?? 0}%</td>
                                            <td class="px-4 py-3">
                                                <Input
                                                    type="number"
                                                    class="mx-auto w-24 text-center"
                                                    min="0"
                                                    max={Number(det.cantidad)}
                                                    step="0.001"
                                                    value={cantidades[det.id] ?? 0}
                                                    oninput={(e) => {
                                                        const v = parseFloat((e.target as HTMLInputElement).value) || 0;
                                                        cantidades = { ...cantidades, [det.id]: Math.min(v, Number(det.cantidad)) };
                                                    }}
                                                />
                                                {#if errors[`items.${factura.detalles.indexOf(det)}.cantidad`]}
                                                    <p class="text-center text-xs text-destructive">{errors[`items.${factura.detalles.indexOf(det)}.cantidad`]}</p>
                                                {/if}
                                            </td>
                                            <td class="px-4 py-3 text-right font-semibold {devuelta > 0 ? 'text-primary' : 'text-muted-foreground'}">
                                                {devuelta > 0 ? formatCOP(totalNC) : '—'}
                                            </td>
                                        </tr>
                                    {:else}
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                                Sin ítems en esta factura.
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Totales calculados -->
            {#if motivo}
                <Card class="border-primary/30 bg-primary/5">
                    <CardHeader class="pb-3">
                        <CardTitle class="text-base">Totales de la nota crédito</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col gap-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span class="font-mono">{formatCOP(totalesCompletos.subtotal)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">IVA</span>
                                <span class="font-mono">{formatCOP(totalesCompletos.iva)}</span>
                            </div>
                            <div class="mt-1 flex justify-between border-t pt-1">
                                <span class="font-bold">Total NC</span>
                                <span class="font-mono font-bold text-primary">{formatCOP(totalesCompletos.total)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Alerta si motivo = anulacion -->
            {#if motivo === 'anulacion'}
                <div class="flex items-start gap-3 rounded-md border border-yellow-400/50 bg-yellow-50 p-4 dark:bg-yellow-900/10">
                    <AlertTriangle class="mt-0.5 size-5 shrink-0 text-yellow-600 dark:text-yellow-400" />
                    <div class="text-sm">
                        <p class="font-medium text-yellow-800 dark:text-yellow-300">Nota crédito de anulación</p>
                        <p class="text-yellow-700 dark:text-yellow-400">
                            Esta nota crédito anulará los efectos económicos de la factura pero no cambia su estado en el sistema.
                            Para anular la factura completamente, use el botón "Anular" en el detalle de la factura.
                        </p>
                    </div>
                </div>
            {/if}

            <!-- Botones -->
            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    variant="outline"
                    onclick={() => router.visit(`/facturacion/${factura.id}`)}
                >
                    Cancelar
                </Button>
                <Button
                    type="submit"
                    disabled={submitting || !motivo || !descripcion}
                >
                    {#if submitting}
                        Emitiendo...
                    {:else}
                        <RotateCcw class="mr-2 size-4" />
                        Emitir nota crédito
                    {/if}
                </Button>
            </div>

        </form>
    </div>
</AppLayout>
