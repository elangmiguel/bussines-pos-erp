<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Calculator, Receipt } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import { formatCOP } from '@/lib/currency';
    import type { BreadcrumbItem } from '@/types';

    interface GastoData {
        id?: number;
        categoria_id: number | null;
        descripcion: string;
        monto: number;
        iva: number;
        fecha: string;
        proveedor_id: number | null;
        medio_pago_id: number | null;
        numero_documento: string | null;
        comprobante: string | null;
    }

    interface SelectOption { id: number; nombre: string }

    let {
        gasto,
        categorias,
        proveedores,
        medios_pago,
    }: {
        gasto: GastoData | null;
        categorias: SelectOption[];
        proveedores: SelectOption[];
        medios_pago: SelectOption[];
    } = $props();

    const isEditing = $derived(!!gasto?.id);

    const breadcrumbs = $derived([
        { title: 'Gastos', href: '/gastos' },
        { title: isEditing ? 'Editar Gasto' : 'Registrar Gasto', href: '#' },
    ]);

    const today = new Date().toISOString().split('T')[0];

    const form = useForm(untrack(() => ({
        categoria_id:     gasto?.categoria_id ?? null as number | null,
        descripcion:      gasto?.descripcion ?? '',
        monto:            gasto?.monto ?? ('' as unknown as number),
        iva:              gasto?.iva ?? 0,
        fecha:            gasto?.fecha
                              ? (typeof gasto.fecha === 'string' ? gasto.fecha.substring(0, 10) : gasto.fecha)
                              : today,
        proveedor_id:     gasto?.proveedor_id ?? null as number | null,
        medio_pago_id:    gasto?.medio_pago_id ?? null as number | null,
        numero_documento: gasto?.numero_documento ?? '',
        comprobante:      gasto?.comprobante ?? '',
    })));

    const total = $derived(() => {
        const m = Number($form.monto) || 0;
        const i = Number($form.iva) || 0;
        return m + i;
    });

    function calcularIva19() {
        const base = Number($form.monto) || 0;
        $form.iva = Math.round(base * 0.19 * 100) / 100;
    }

    function submit(e: Event) {
        e.preventDefault();
        if (isEditing && gasto?.id) {
            $form.put(`/gastos/${gasto.id}`);
        } else {
            $form.post('/gastos');
        }
    }

    const selectClass = 'h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring';
</script>

<AppHead title={isEditing ? 'Editar Gasto' : 'Registrar Gasto'} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6 max-w-3xl mx-auto">
        <div class="flex items-center gap-2">
            <Receipt class="h-6 w-6 text-primary" />
            <h1 class="text-2xl font-bold">
                {isEditing ? 'Editar Gasto' : 'Registrar Gasto'}
            </h1>
        </div>

        <form onsubmit={submit} class="flex flex-col gap-6">

            <!-- Información principal -->
            <Card>
                <CardHeader>
                    <CardTitle>Información del Gasto</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">

                    <!-- Descripción -->
                    <div class="grid gap-2">
                        <Label for="descripcion">Descripción <span class="text-destructive">*</span></Label>
                        <textarea
                            id="descripcion"
                            bind:value={$form.descripcion}
                            rows={3}
                            placeholder="Describa el gasto..."
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                        ></textarea>
                        <InputError message={$form.errors.descripcion} />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Categoría -->
                        <div class="grid gap-2">
                            <Label for="categoria_id">Categoría <span class="text-destructive">*</span></Label>
                            <select
                                id="categoria_id"
                                class={selectClass}
                                bind:value={$form.categoria_id}
                            >
                                <option value={null}>Seleccionar categoría...</option>
                                {#each categorias as cat (cat.id)}
                                    <option value={cat.id}>{cat.nombre}</option>
                                {/each}
                            </select>
                            <InputError message={$form.errors.categoria_id} />
                        </div>

                        <!-- Fecha -->
                        <div class="grid gap-2">
                            <Label for="fecha">Fecha <span class="text-destructive">*</span></Label>
                            <Input
                                id="fecha"
                                type="date"
                                bind:value={$form.fecha}
                            />
                            <InputError message={$form.errors.fecha} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Montos -->
            <Card>
                <CardHeader>
                    <CardTitle>Montos</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Monto base -->
                        <div class="grid gap-2">
                            <Label for="monto">Monto (COP) <span class="text-destructive">*</span></Label>
                            <Input
                                id="monto"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="0.00"
                                bind:value={$form.monto}
                            />
                            {#if Number($form.monto) > 0}
                                <p class="text-xs text-muted-foreground">{formatCOP(Number($form.monto))}</p>
                            {/if}
                            <InputError message={$form.errors.monto} />
                        </div>

                        <!-- IVA -->
                        <div class="grid gap-2">
                            <Label for="iva">IVA (COP)</Label>
                            <div class="flex gap-2">
                                <Input
                                    id="iva"
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    placeholder="0.00"
                                    bind:value={$form.iva}
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    onclick={calcularIva19}
                                    title="Calcular 19% de IVA sobre el monto"
                                    class="shrink-0"
                                >
                                    <Calculator class="h-4 w-4" />
                                    <span class="ml-1 text-xs">19%</span>
                                </Button>
                            </div>
                            {#if Number($form.iva) > 0}
                                <p class="text-xs text-muted-foreground">{formatCOP(Number($form.iva))}</p>
                            {/if}
                            <InputError message={$form.errors.iva} />
                        </div>
                    </div>

                    <!-- Total preview -->
                    <div class="rounded-md bg-muted/50 px-4 py-3 flex items-center justify-between">
                        <span class="text-sm font-medium text-muted-foreground">Total (Monto + IVA)</span>
                        <span class="text-lg font-bold">{formatCOP(total())}</span>
                    </div>
                </CardContent>
            </Card>

            <!-- Información adicional -->
            <Card>
                <CardHeader>
                    <CardTitle>Información Adicional</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Proveedor -->
                        <div class="grid gap-2">
                            <Label for="proveedor_id">Proveedor</Label>
                            <select
                                id="proveedor_id"
                                class={selectClass}
                                bind:value={$form.proveedor_id}
                            >
                                <option value={null}>Sin proveedor</option>
                                {#each proveedores as prov (prov.id)}
                                    <option value={prov.id}>{prov.nombre}</option>
                                {/each}
                            </select>
                            <InputError message={$form.errors.proveedor_id} />
                        </div>

                        <!-- Medio de Pago -->
                        <div class="grid gap-2">
                            <Label for="medio_pago_id">Medio de Pago</Label>
                            <select
                                id="medio_pago_id"
                                class={selectClass}
                                bind:value={$form.medio_pago_id}
                            >
                                <option value={null}>Sin especificar</option>
                                {#each medios_pago as mp (mp.id)}
                                    <option value={mp.id}>{mp.nombre}</option>
                                {/each}
                            </select>
                            <InputError message={$form.errors.medio_pago_id} />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <!-- Número de Documento -->
                        <div class="grid gap-2">
                            <Label for="numero_documento">Número de Documento</Label>
                            <Input
                                id="numero_documento"
                                bind:value={$form.numero_documento}
                                placeholder="Ej. FV-001234"
                                maxlength={100}
                            />
                            <InputError message={$form.errors.numero_documento} />
                        </div>

                        <!-- Comprobante / Referencia -->
                        <div class="grid gap-2">
                            <Label for="comprobante">Comprobante / Referencia</Label>
                            <Input
                                id="comprobante"
                                bind:value={$form.comprobante}
                                placeholder="Referencia o ruta del comprobante"
                                maxlength={255}
                            />
                            <InputError message={$form.errors.comprobante} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Separator />

            <div class="flex gap-3 justify-end">
                <Button variant="outline" href="/gastos" type="button">Cancelar</Button>
                <Button type="submit" disabled={$form.processing}>
                    {$form.processing ? 'Guardando...' : isEditing ? 'Actualizar Gasto' : 'Registrar Gasto'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
