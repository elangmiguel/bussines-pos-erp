<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { AlertCircle, Unlock, DollarSign } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
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
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { BreadcrumbItem } from '@/types';

    interface Caja {
        id: number;
        nombre: string;
        ubicacion: string | null;
    }

    let {
        cajas,
        errors = {},
    }: {
        cajas: Caja[];
        errors?: Record<string, string>;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Caja', href: '/caja' },
        { title: 'Turnos', href: '/caja/turnos' },
        { title: 'Abrir Turno', href: '/caja/turnos/abrir' },
    ];

    const form = useForm(untrack(() => ({
        caja_id: '',
        saldo_inicial: '',
    })));

    function formatCOP(value: string | number): string {
        const num = Number(value);
        if (isNaN(num)) return '';
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(num);
    }

    let saldoPreview = $derived(
        $form.saldo_inicial ? formatCOP($form.saldo_inicial) : ''
    );

    function handleSubmit(e: Event) {
        e.preventDefault();
        $form.post('/caja/turnos/abrir');
    }

    let cajaSeleccionadaNombre = $derived(
        $form.caja_id
            ? (cajas.find((c) => String(c.id) === $form.caja_id)?.nombre ?? 'Seleccionar caja')
            : 'Seleccionar caja'
    );
</script>

<AppHead title="Abrir Turno de Caja" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col items-center p-6">
        <div class="w-full max-w-lg">
            <div class="mb-6 flex items-center gap-2">
                <Unlock class="size-6 text-primary" />
                <h1 class="text-2xl font-bold">Abrir Turno de Caja</h1>
            </div>

            {#if cajas.length === 0}
                <!-- No hay cajas disponibles -->
                <div class="flex flex-col items-center gap-3 rounded-xl border border-dashed border-yellow-400 bg-yellow-50 dark:bg-yellow-950/20 p-8 text-center">
                    <AlertCircle class="size-10 text-yellow-500" />
                    <p class="font-semibold text-yellow-800 dark:text-yellow-300">
                        No hay cajas disponibles para abrir turno
                    </p>
                    <p class="text-sm text-yellow-700 dark:text-yellow-400">
                        Todas las cajas activas ya tienen un turno abierto, o no existen cajas activas. Contacte al administrador.
                    </p>
                    <Button variant="outline" href="/caja">Volver a Caja</Button>
                </div>
            {:else}
                <Card>
                    <CardHeader>
                        <CardTitle>Datos del turno</CardTitle>
                        <CardDescription>
                            Seleccione la caja e ingrese el monto en efectivo con el que inicia el turno.
                        </CardDescription>
                    </CardHeader>

                    <form onsubmit={handleSubmit}>
                        <CardContent class="space-y-5">
                            <!-- Selección de caja -->
                            <div class="space-y-1.5">
                                <Label for="caja_select">Caja registradora</Label>
                                <Select
                                    value={$form.caja_id}
                                    onValueChange={(v) => ($form.caja_id = v)}
                                >
                                    <SelectTrigger id="caja_select" class="w-full {$form.errors.caja_id ? 'border-destructive' : ''}">
                                        {cajaSeleccionadaNombre}
                                    </SelectTrigger>
                                    <SelectContent>
                                        {#each cajas as caja (caja.id)}
                                            <SelectItem value={String(caja.id)}>
                                                {caja.nombre}
                                                {#if caja.ubicacion}
                                                    — <span class="text-muted-foreground text-xs">{caja.ubicacion}</span>
                                                {/if}
                                            </SelectItem>
                                        {/each}
                                    </SelectContent>
                                </Select>
                                {#if $form.errors.caja_id}
                                    <p class="text-xs text-destructive">{$form.errors.caja_id}</p>
                                {/if}
                            </div>

                            <!-- Saldo inicial -->
                            <div class="space-y-1.5">
                                <Label for="saldo_inicial">Saldo inicial en efectivo</Label>
                                <div class="relative">
                                    <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                                    <Input
                                        id="saldo_inicial"
                                        type="number"
                                        min="0"
                                        step="1"
                                        placeholder="0"
                                        class="pl-9 {$form.errors.saldo_inicial ? 'border-destructive' : ''}"
                                        bind:value={$form.saldo_inicial}
                                    />
                                </div>
                                {#if saldoPreview}
                                    <p class="text-sm font-medium text-green-700 dark:text-green-400">
                                        {saldoPreview}
                                    </p>
                                {/if}
                                {#if $form.errors.saldo_inicial}
                                    <p class="text-xs text-destructive">{$form.errors.saldo_inicial}</p>
                                {/if}
                            </div>

                            <!-- Error general -->
                            {#if errors.general}
                                <div class="flex items-center gap-2 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                                    <AlertCircle class="size-4 shrink-0" />
                                    {errors.general}
                                </div>
                            {/if}
                        </CardContent>

                        <CardFooter class="flex gap-3 justify-end">
                            <Button type="button" variant="outline" href="/caja">
                                Cancelar
                            </Button>
                            <Button
                                type="submit"
                                disabled={$form.processing || !$form.caja_id}
                            >
                                <Unlock class="mr-2 size-4" />
                                {$form.processing ? 'Abriendo...' : 'Abrir Turno'}
                            </Button>
                        </CardFooter>
                    </form>
                </Card>
            {/if}
        </div>
    </div>
</AppLayout>
