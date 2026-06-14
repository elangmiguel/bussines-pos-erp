<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { ArrowDownCircle, ArrowUpCircle, Calculator, Scale } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import type { BreadcrumbItem } from '@/types';

    interface DetalleGenerado {
        base_5: number;
        iva_5: number;
        base_19: number;
        iva_19: number;
        inc: number;
    }

    interface DetalleDescontable {
        total_compras: number;
        iva_compras: number;
    }

    interface Resumen {
        iva_generado: number;
        iva_descontable: number;
        saldo: number;
        detalle_generado: DetalleGenerado;
        detalle_descontable: DetalleDescontable;
    }

    interface Filtros {
        desde: string;
        hasta: string;
    }

    let {
        resumen,
        filtros,
    }: {
        resumen: Resumen | null;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
        { title: 'DIAN', href: '/reportes/dian/resumen-iva' },
        { title: 'Resumen IVA', href: '/reportes/dian/resumen-iva' },
    ];

    let desde = $state(untrack(() => filtros?.desde ?? ''));
    let hasta = $state(untrack(() => filtros?.hasta ?? ''));

    function generar() {
        router.get('/reportes/dian/resumen-iva', { desde, hasta }, { preserveState: false });
    }

    function formatCop(value: number | undefined): string {
        if (value === undefined || value === null) return '—';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(value);
    }

    const saldoPositivo = $derived(resumen && resumen.saldo >= 0);
</script>

<AppHead title="Resumen IVA — DIAN" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Encabezado -->
        <div>
            <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight">
                <Calculator class="size-6 text-teal-600" />
                Resumen IVA
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Cuadro comparativo de IVA generado vs IVA descontable para declaración tributaria.
            </p>
        </div>

        <!-- Filtros -->
        <Card>
            <CardContent class="pt-4">
                <div class="flex flex-wrap items-end gap-4">
                    <div class="flex flex-col gap-1.5">
                        <Label for="desde">Desde</Label>
                        <Input id="desde" type="date" bind:value={desde} class="w-40" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <Label for="hasta">Hasta</Label>
                        <Input id="hasta" type="date" bind:value={hasta} class="w-40" />
                    </div>
                    <Button onclick={generar}>Calcular</Button>
                </div>
            </CardContent>
        </Card>

        {#if resumen}
            <!-- Saldo final destacado -->
            <div
                class="rounded-xl border-2 p-6 {saldoPositivo
                    ? 'border-red-300 bg-red-50 dark:border-red-800 dark:bg-red-950/30'
                    : 'border-emerald-300 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/30'}"
            >
                <div class="flex flex-col items-center gap-2 text-center">
                    <Scale
                        class="size-8 {saldoPositivo
                            ? 'text-red-600'
                            : 'text-emerald-600'}"
                    />
                    <p class="text-sm font-medium text-muted-foreground">
                        {saldoPositivo ? 'IVA a pagar (saldo a favor del Estado)' : 'Saldo a favor del contribuyente'}
                    </p>
                    <p
                        class="text-3xl font-bold tabular-nums {saldoPositivo
                            ? 'text-red-700 dark:text-red-400'
                            : 'text-emerald-700 dark:text-emerald-400'}"
                    >
                        {formatCop(Math.abs(resumen.saldo))}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Período: {desde} — {hasta}
                    </p>
                </div>
            </div>

            <!-- IVA Generado vs Descontable -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <!-- IVA Generado -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <ArrowUpCircle class="size-5 text-red-500" />
                            IVA Generado (Ventas)
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="flex flex-col gap-0">
                        <div class="rounded-lg bg-muted/40 p-4">
                            <table class="w-full text-sm">
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 text-muted-foreground">Base gravable 5%</td>
                                        <td class="py-2 text-right tabular-nums">
                                            {formatCop(resumen.detalle_generado.base_5)}
                                        </td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-muted-foreground">IVA al 5%</td>
                                        <td class="py-2 text-right tabular-nums font-medium text-rose-600">
                                            {formatCop(resumen.detalle_generado.iva_5)}
                                        </td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-muted-foreground">Base gravable 19%</td>
                                        <td class="py-2 text-right tabular-nums">
                                            {formatCop(resumen.detalle_generado.base_19)}
                                        </td>
                                    </tr>
                                    <tr class="border-b">
                                        <td class="py-2 text-muted-foreground">IVA al 19%</td>
                                        <td class="py-2 text-right tabular-nums font-medium text-rose-600">
                                            {formatCop(resumen.detalle_generado.iva_19)}
                                        </td>
                                    </tr>
                                    {#if resumen.detalle_generado.inc > 0}
                                        <tr class="border-b">
                                            <td class="py-2 text-muted-foreground">INC</td>
                                            <td class="py-2 text-right tabular-nums">
                                                {formatCop(resumen.detalle_generado.inc)}
                                            </td>
                                        </tr>
                                    {/if}
                                    <tr>
                                        <td class="pt-3 font-semibold">Total IVA generado</td>
                                        <td class="pt-3 text-right text-lg font-bold tabular-nums text-red-600">
                                            {formatCop(resumen.iva_generado)}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- IVA Descontable -->
                <Card>
                    <CardHeader class="pb-3">
                        <CardTitle class="flex items-center gap-2 text-base">
                            <ArrowDownCircle class="size-5 text-emerald-500" />
                            IVA Descontable (Compras)
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="rounded-lg bg-muted/40 p-4">
                            <table class="w-full text-sm">
                                <tbody>
                                    <tr class="border-b">
                                        <td class="py-2 text-muted-foreground">Total compras (sin IVA)</td>
                                        <td class="py-2 text-right tabular-nums">
                                            {formatCop(resumen.detalle_descontable.total_compras)}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="pt-3 font-semibold">Total IVA descontable</td>
                                        <td class="pt-3 text-right text-lg font-bold tabular-nums text-emerald-600">
                                            {formatCop(resumen.iva_descontable)}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Liquidación -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Liquidación del período</CardTitle>
                </CardHeader>
                <CardContent>
                    <table class="w-full max-w-md text-sm">
                        <tbody>
                            <tr class="border-b">
                                <td class="py-2 text-muted-foreground">(+) IVA generado</td>
                                <td class="py-2 text-right tabular-nums font-medium">
                                    {formatCop(resumen.iva_generado)}
                                </td>
                            </tr>
                            <tr class="border-b">
                                <td class="py-2 text-muted-foreground">(-) IVA descontable</td>
                                <td class="py-2 text-right tabular-nums font-medium text-emerald-600">
                                    ({formatCop(resumen.iva_descontable)})
                                </td>
                            </tr>
                            <tr>
                                <td class="pt-3 font-bold">
                                    {saldoPositivo ? 'IVA a pagar' : 'Saldo a favor'}
                                </td>
                                <td
                                    class="pt-3 text-right text-base font-bold tabular-nums {saldoPositivo
                                        ? 'text-red-600'
                                        : 'text-emerald-600'}"
                                >
                                    {formatCop(Math.abs(resumen.saldo))}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="mt-4 text-xs text-muted-foreground">
                        * Este resumen es informativo. Consulte con su contador para la declaración oficial ante la DIAN.
                    </p>
                </CardContent>
            </Card>
        {:else}
            <div class="rounded-lg border border-dashed p-12 text-center text-muted-foreground">
                Selecciona un rango de fechas y haz clic en <strong>Calcular</strong> para ver el resumen de IVA.
            </div>
        {/if}
    </div>
</AppLayout>
