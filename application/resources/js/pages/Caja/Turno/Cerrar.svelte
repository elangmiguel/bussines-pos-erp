<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Lock, DollarSign, AlertCircle, Clock } from 'lucide-svelte';
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
        CardFooter,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    interface MedioPagoResumen {
        medio_pago: string;
        total: string | number;
    }

    interface Turno {
        id: number;
        saldo_inicial: string;
        apertura: string;
        estado: string;
        caja: { id: number; nombre: string; ubicacion: string | null };
        facturas_count?: number;
        total_ventas?: string | number;
        breakdown?: MedioPagoResumen[];
    }

    let {
        turno,
        errors = {},
    }: {
        turno: Turno;
        errors?: Record<string, string>;
    } = $props();

    const breadcrumbs = $derived([
        { title: 'Caja', href: '/caja' },
        { title: 'Turnos', href: '/caja/turnos' },
        { title: `Turno #${turno.id}`, href: `/caja/turnos/${turno.id}` },
        { title: 'Cerrar Turno', href: `/caja/turnos/${turno.id}/cerrar` },
    ]);

    function formatCOP(value: string | number | undefined | null): string {
        if (value === undefined || value === null) return '$0';
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

    const form = useForm(untrack(() => ({
        saldo_final: '',
        observaciones: '',
    })));

    // Efectivo esperado = lo recaudado en efectivo según las facturas
    let efectivoEsperado = $derived((): number => {
        if (!turno.breakdown) return Number(turno.saldo_inicial);
        const efectivoFact = turno.breakdown
            .filter((b) => b.medio_pago.toLowerCase().includes('efectivo'))
            .reduce((acc, b) => acc + Number(b.total), 0);
        return Number(turno.saldo_inicial) + efectivoFact;
    });

    let diferencia = $derived((): number => {
        const final = Number($form.saldo_final);
        if (isNaN(final) || !$form.saldo_final) return 0;
        return final - efectivoEsperado();
    });

    let diferenciaLabel = $derived((): string => {
        const d = diferencia();
        if (d === 0) return 'Cuadre exacto';
        return d > 0 ? `Sobrante: ${formatCOP(d)}` : `Faltante: ${formatCOP(Math.abs(d))}`;
    });

    let diferenciaClass = $derived((): string => {
        const d = diferencia();
        if (d === 0) return 'text-green-700 dark:text-green-400';
        if (d > 0) return 'text-blue-600 dark:text-blue-400';
        return 'text-destructive';
    });

    function handleSubmit(e: Event) {
        e.preventDefault();
        $form.patch(`/caja/turnos/${turno.id}/cerrar`);
    }
</script>

<AppHead title="Cerrar Turno — {turno.caja.nombre}" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col items-center p-6">
        <div class="w-full max-w-2xl space-y-6">
            <div class="flex items-center gap-2">
                <Lock class="size-6 text-primary" />
                <h1 class="text-2xl font-bold">Cerrar Turno de Caja</h1>
            </div>

            <!-- Resumen del turno -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Resumen del Turno</CardTitle>
                    <CardDescription>
                        Caja: <strong>{turno.caja.nombre}</strong>
                        {#if turno.caja.ubicacion} — {turno.caja.ubicacion}{/if}
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-md bg-muted/40 p-3 space-y-0.5">
                            <p class="text-xs text-muted-foreground">Apertura</p>
                            <div class="flex items-center gap-1 text-sm font-medium">
                                <Clock class="size-3.5 text-muted-foreground" />
                                {formatDateTime(turno.apertura)}
                            </div>
                        </div>
                        <div class="rounded-md bg-muted/40 p-3 space-y-0.5">
                            <p class="text-xs text-muted-foreground">Saldo Inicial</p>
                            <p class="text-sm font-medium">{formatCOP(turno.saldo_inicial)}</p>
                        </div>
                        <div class="rounded-md bg-muted/40 p-3 space-y-0.5">
                            <p class="text-xs text-muted-foreground">Total Ventas</p>
                            <p class="text-lg font-bold text-green-700 dark:text-green-400">
                                {formatCOP(turno.total_ventas ?? 0)}
                            </p>
                        </div>
                        <div class="rounded-md bg-muted/40 p-3 space-y-0.5">
                            <p class="text-xs text-muted-foreground">Número de Facturas</p>
                            <p class="text-lg font-bold">{turno.facturas_count ?? 0}</p>
                        </div>
                    </div>

                    {#if turno.breakdown && turno.breakdown.length > 0}
                        <Separator class="my-4" />
                        <p class="mb-2 text-sm font-medium text-muted-foreground">Desglose por medio de pago</p>
                        <div class="space-y-1.5">
                            {#each turno.breakdown as item (item.medio_pago)}
                                <div class="flex items-center justify-between text-sm">
                                    <span>{item.medio_pago}</span>
                                    <span class="font-medium">{formatCOP(item.total)}</span>
                                </div>
                            {/each}
                        </div>
                        <Separator class="my-3" />
                        <div class="flex items-center justify-between text-sm font-semibold">
                            <span>Efectivo esperado en caja</span>
                            <span class="text-green-700 dark:text-green-400">{formatCOP(efectivoEsperado())}</span>
                        </div>
                    {/if}
                </CardContent>
            </Card>

            <!-- Formulario de cierre -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Datos de Cierre</CardTitle>
                </CardHeader>

                <form onsubmit={handleSubmit}>
                    <CardContent class="space-y-5">
                        <div class="space-y-1.5">
                            <Label for="saldo_final">Saldo final en efectivo (conteo físico)</Label>
                            <div class="relative">
                                <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                <Input
                                    id="saldo_final"
                                    type="number"
                                    min="0"
                                    step="1"
                                    placeholder="0"
                                    class="pl-9 {$form.errors.saldo_final ? 'border-destructive' : ''}"
                                    bind:value={$form.saldo_final}
                                />
                            </div>
                            {#if $form.errors.saldo_final}
                                <p class="text-xs text-destructive">{$form.errors.saldo_final}</p>
                            {/if}

                            {#if $form.saldo_final}
                                <p class="text-sm font-semibold {diferenciaClass}">
                                    {diferenciaLabel}
                                </p>
                            {/if}
                        </div>

                        <div class="space-y-1.5">
                            <Label for="observaciones">Observaciones <span class="text-muted-foreground text-xs">(opcional)</span></Label>
                            <textarea
                                id="observaciones"
                                rows={3}
                                placeholder="Novedades del turno, diferencias, etc."
                                bind:value={$form.observaciones}
                                class="w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring/50 disabled:opacity-50 resize-none {$form.errors.observaciones ? 'border-destructive' : ''}"
                            ></textarea>
                            {#if $form.errors.observaciones}
                                <p class="text-xs text-destructive">{$form.errors.observaciones}</p>
                            {/if}
                        </div>

                        {#if errors.general}
                            <div class="flex items-center gap-2 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                                <AlertCircle class="size-4 shrink-0" />
                                {errors.general}
                            </div>
                        {/if}
                    </CardContent>

                    <CardFooter class="flex gap-3 justify-end">
                        <Button type="button" variant="outline" href={`/caja/turnos/${turno.id}`}>
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            variant="destructive"
                            disabled={$form.processing || !$form.saldo_final}
                        >
                            <Lock class="mr-2 size-4" />
                            {$form.processing ? 'Cerrando...' : 'Confirmar Cierre'}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </div>
</AppLayout>
