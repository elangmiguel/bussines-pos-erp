<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { FileText, AlertTriangle } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import type { BreadcrumbItem } from '@/types';

    // ---------- types ----------
    interface Factura {
        id: number;
        numero_completo: string;
        fecha: string;
        subtotal: number;
        total: number;
        cliente?: {
            nombre: string;
            identificacion: string;
        } | null;
    }

    let {
        factura,
        errors = {},
    }: {
        factura: Factura;
        errors?: Record<string, string>;
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Facturación', href: '/facturacion' },
        { title: factura.numero_completo, href: `/facturacion/${factura.id}` },
        { title: 'Nota Débito', href: '#' },
    ]);

    let motivo     = $state('');
    let subtotal   = $state<number | string>('');
    let iva        = $state<number | string>('');
    let submitting = $state(false);

    const formatCOP = (value: number) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(value ?? 0);

    const totalCalculado = $derived(
        (Number(subtotal) || 0) + (Number(iva) || 0)
    );

    function handleSubmit(e: Event) {
        e.preventDefault();
        if (submitting) return;
        submitting = true;

        router.post(`/facturacion/${factura.id}/nota-debito`, {
            motivo,
            subtotal: Number(subtotal) || 0,
            iva:      Number(iva) || 0,
        }, {
            onFinish: () => { submitting = false; },
        });
    }
</script>

<AppHead title={`Nota Débito — ${factura.numero_completo}`} />

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-2xl p-6">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex size-10 items-center justify-center rounded-full bg-primary/10">
                <FileText class="size-5 text-primary" />
            </div>
            <div>
                <h1 class="text-xl font-bold">Nueva Nota Débito</h1>
                <p class="text-sm text-muted-foreground">
                    Factura: <span class="font-mono font-medium">{factura.numero_completo}</span>
                    {#if factura.cliente}
                        &mdash; {factura.cliente.nombre}
                    {/if}
                </p>
            </div>
        </div>

        <!-- Info de la factura referenciada -->
        <Card class="mb-5 bg-muted/30">
            <CardContent class="py-3">
                <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                    <div>
                        <p class="text-xs text-muted-foreground">Factura</p>
                        <p class="font-mono font-semibold">{factura.numero_completo}</p>
                    </div>
                    {#if factura.cliente}
                        <div>
                            <p class="text-xs text-muted-foreground">Cliente</p>
                            <p class="font-medium">{factura.cliente.nombre}</p>
                            <p class="font-mono text-xs text-muted-foreground">{factura.cliente.identificacion}</p>
                        </div>
                    {/if}
                    <div>
                        <p class="text-xs text-muted-foreground">Total factura</p>
                        <p class="font-mono font-semibold">{formatCOP(factura.total)}</p>
                    </div>
                </div>
            </CardContent>
        </Card>

        <form onsubmit={handleSubmit} class="flex flex-col gap-5">

            <Card>
                <CardHeader class="pb-3">
                    <CardTitle class="text-base">Datos de la nota débito</CardTitle>
                </CardHeader>
                <CardContent class="flex flex-col gap-4">

                    <!-- Motivo -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-sm font-medium" for="motivo">
                            Motivo <span class="text-destructive">*</span>
                        </label>
                        <textarea
                            id="motivo"
                            class={[
                                'min-h-[80px] w-full rounded-md border bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring resize-none',
                                errors.motivo ? 'border-destructive' : 'border-input',
                            ].join(' ')}
                            placeholder="Describa el motivo del cargo adicional (mínimo 5 caracteres)..."
                            bind:value={motivo}
                        ></textarea>
                        {#if errors.motivo}
                            <p class="text-xs text-destructive">{errors.motivo}</p>
                        {/if}
                    </div>

                    <!-- Subtotal e IVA -->
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium" for="subtotal">
                                Subtotal (sin IVA) <span class="text-destructive">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-muted-foreground">$</span>
                                <Input
                                    id="subtotal"
                                    type="number"
                                    class={['pl-7', errors.subtotal ? 'border-destructive' : ''].join(' ')}
                                    placeholder="0"
                                    min="1"
                                    step="1"
                                    bind:value={subtotal}
                                />
                            </div>
                            {#if errors.subtotal}
                                <p class="text-xs text-destructive">{errors.subtotal}</p>
                            {/if}
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-sm font-medium" for="iva">
                                IVA <span class="text-muted-foreground text-xs">(opcional)</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2.5 text-sm text-muted-foreground">$</span>
                                <Input
                                    id="iva"
                                    type="number"
                                    class={['pl-7', errors.iva ? 'border-destructive' : ''].join(' ')}
                                    placeholder="0"
                                    min="0"
                                    step="1"
                                    bind:value={iva}
                                />
                            </div>
                            {#if errors.iva}
                                <p class="text-xs text-destructive">{errors.iva}</p>
                            {/if}
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Total calculado -->
            {#if (Number(subtotal) || 0) > 0}
                <Card class="border-primary/30 bg-primary/5">
                    <CardContent class="py-4">
                        <div class="flex flex-col gap-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span class="font-mono">{formatCOP(Number(subtotal) || 0)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">IVA</span>
                                <span class="font-mono">{formatCOP(Number(iva) || 0)}</span>
                            </div>
                            <div class="mt-1 flex justify-between border-t pt-1">
                                <span class="font-bold">Total Nota Débito</span>
                                <span class="font-mono font-bold text-primary">{formatCOP(totalCalculado)}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Aviso -->
            <div class="flex items-start gap-3 rounded-md border border-yellow-400/50 bg-yellow-50 p-4 dark:bg-yellow-900/10">
                <AlertTriangle class="mt-0.5 size-5 shrink-0 text-yellow-600 dark:text-yellow-400" />
                <p class="text-sm text-yellow-700 dark:text-yellow-400">
                    Una nota débito incrementa el valor de la factura original.
                    Asegúrese de haber acordado el cargo adicional con el cliente antes de emitirla.
                </p>
            </div>

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
                    disabled={submitting || !motivo || !subtotal || Number(subtotal) < 1}
                >
                    {#if submitting}
                        Emitiendo...
                    {:else}
                        <FileText class="mr-2 size-4" />
                        Emitir nota débito
                    {/if}
                </Button>
            </div>

        </form>
    </div>
</AppLayout>
