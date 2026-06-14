<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import {
        DollarSign,
        ArrowUpCircle,
        ArrowDownCircle,
        ArrowDownUp,
        AlertCircle,
        Clock,
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
        CardFooter,
    } from '@/components/ui/card';
    import {
        Dialog,
        DialogContent,
        DialogDescription,
        DialogFooter,
        DialogTitle,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    // --- Types ---
    interface MedioPago { id: number; nombre: string; }
    interface User { name: string; }
    interface Movimiento {
        id?: number;
        tipo: 'ingreso' | 'egreso';
        monto: string | number;
        descripcion: string;
        created_at: string;
        user: User | null;
    }
    interface Fondo {
        id: number;
        nombre: string;
        tipo: string;
        saldo_actual: string;
        activo: boolean;
        medio_pago: MedioPago | null;
        movimientos: Movimiento[];
    }

    let {
        fondos,
        otros_fondos = [],
        medios_pago = [],
    }: {
        fondos: Fondo[];
        otros_fondos?: { id: number; nombre: string }[];
        medios_pago?: MedioPago[];
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Caja', href: '/caja' },
        { title: 'Fondos', href: '/caja/fondos' },
    ];

    function formatCOP(value: string | number): string {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(Number(value));
    }

    function formatDateTime(dt: string): string {
        return new Date(dt).toLocaleString('es-CO', {
            dateStyle: 'short',
            timeStyle: 'short',
        });
    }

    // --- Tipo badge styling ---
    function tipoBadgeClass(tipo: string): string {
        const map: Record<string, string> = {
            caja: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100 border-blue-200',
            digital: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-100 border-purple-200',
            banco: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200',
            reserva: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100 border-yellow-200',
            otro: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200',
        };
        return map[tipo] ?? map['otro'];
    }

    function tipoLabel(tipo: string): string {
        const map: Record<string, string> = {
            caja: 'Caja',
            digital: 'Digital',
            banco: 'Banco',
            reserva: 'Reserva',
            otro: 'Otro',
        };
        return map[tipo] ?? tipo;
    }

    // ══════════ MOVIMIENTO DIALOG ══════════
    let movDialogOpen = $state(false);
    let movFondo = $state<Fondo | null>(null);

    const movForm = useForm({
        tipo: 'ingreso' as 'ingreso' | 'egreso',
        monto: '',
        descripcion: '',
    });

    function openMovDialog(fondo: Fondo, tipo: 'ingreso' | 'egreso') {
        movFondo = fondo;
        $movForm.tipo = tipo;
        $movForm.monto = '';
        $movForm.descripcion = '';
        $movForm.clearErrors();
        movDialogOpen = true;
    }

    function submitMovimiento() {
        if (!movFondo) return;
        $movForm.post(`/caja/fondos/${movFondo.id}/movimiento`, {
            onSuccess: () => { movDialogOpen = false; },
        });
    }

    // ══════════ TRANSFERENCIA DIALOG ══════════
    let transDialogOpen = $state(false);
    let transFondoOrigen = $state<Fondo | null>(null);

    const transForm = useForm({
        fondo_origen_id: '',
        fondo_destino_id: '',
        monto: '',
        descripcion: '',
    });

    // Destinos disponibles (todos los fondos excepto el origen)
    let transDestinos = $derived(
        transFondoOrigen
            ? fondos.filter((f) => f.id !== transFondoOrigen!.id)
            : fondos
    );

    let transDestinoLabel = $derived(
        $transForm.fondo_destino_id
            ? (fondos.find((f) => String(f.id) === $transForm.fondo_destino_id)?.nombre ?? 'Seleccionar destino')
            : 'Seleccionar destino'
    );

    function openTransDialog(fondo: Fondo) {
        transFondoOrigen = fondo;
        $transForm.fondo_origen_id = String(fondo.id);
        $transForm.fondo_destino_id = '';
        $transForm.monto = '';
        $transForm.descripcion = '';
        $transForm.clearErrors();
        transDialogOpen = true;
    }

    function submitTransferencia() {
        $transForm.post('/caja/fondos/transferencia', {
            onSuccess: () => { transDialogOpen = false; },
        });
    }
</script>

<AppHead title="Fondos" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">

        <!-- Header -->
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2">
                <DollarSign class="size-6 text-primary" />
                <h1 class="text-2xl font-bold">Fondos</h1>
                <Badge variant="outline">{fondos.length} fondos</Badge>
            </div>
        </div>

        {#if fondos.length === 0}
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed py-16 text-muted-foreground">
                <AlertCircle class="mb-3 size-10 opacity-40" />
                <p class="text-sm">No hay fondos registrados.</p>
            </div>
        {:else}
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                {#each fondos as fondo (fondo.id)}
                    <Card>
                        <!-- Card header: nombre + tipo badge -->
                        <CardHeader class="pb-2">
                            <div class="flex items-start justify-between gap-2">
                                <CardTitle class="text-base">{fondo.nombre}</CardTitle>
                                <Badge class="{tipoBadgeClass(fondo.tipo)} shrink-0 text-xs">
                                    {tipoLabel(fondo.tipo)}
                                </Badge>
                            </div>
                            {#if fondo.medio_pago}
                                <p class="text-xs text-muted-foreground">{fondo.medio_pago.nombre}</p>
                            {/if}
                        </CardHeader>

                        <!-- Saldo prominente -->
                        <CardContent class="pb-3 space-y-4">
                            <div>
                                <p class="text-xs text-muted-foreground mb-0.5">Saldo actual</p>
                                <p class="text-3xl font-bold tracking-tight">
                                    {formatCOP(fondo.saldo_actual)}
                                </p>
                            </div>

                            <!-- Movimientos recientes -->
                            {#if fondo.movimientos && fondo.movimientos.length > 0}
                                <div>
                                    <Separator class="mb-3" />
                                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wide mb-2">
                                        Movimientos recientes
                                    </p>
                                    <div class="space-y-1.5">
                                        {#each fondo.movimientos as mov, i (i)}
                                            <div class="flex items-start justify-between text-xs gap-2">
                                                <div class="flex items-center gap-1.5 min-w-0">
                                                    {#if mov.tipo === 'ingreso'}
                                                        <ArrowUpCircle class="size-3.5 text-green-600 shrink-0" />
                                                    {:else}
                                                        <ArrowDownCircle class="size-3.5 text-red-500 shrink-0" />
                                                    {/if}
                                                    <span class="truncate text-muted-foreground" title={mov.descripcion}>
                                                        {mov.descripcion}
                                                    </span>
                                                </div>
                                                <div class="text-right shrink-0">
                                                    <span class={mov.tipo === 'ingreso' ? 'font-semibold text-green-700 dark:text-green-400' : 'font-semibold text-red-600 dark:text-red-400'}>
                                                        {mov.tipo === 'ingreso' ? '+' : '-'}{formatCOP(mov.monto)}
                                                    </span>
                                                    <p class="text-muted-foreground flex items-center gap-0.5 justify-end mt-0.5">
                                                        <Clock class="size-2.5" />
                                                        {formatDateTime(mov.created_at)}
                                                    </p>
                                                </div>
                                            </div>
                                        {/each}
                                    </div>
                                </div>
                            {:else}
                                <p class="text-xs text-muted-foreground">Sin movimientos recientes.</p>
                            {/if}
                        </CardContent>

                        <!-- Acciones -->
                        <CardFooter class="flex gap-2 pt-0 flex-wrap">
                            <Button
                                size="sm"
                                variant="outline"
                                class="flex-1 text-green-700 border-green-300 hover:bg-green-50"
                                onclick={() => openMovDialog(fondo, 'ingreso')}
                            >
                                <ArrowUpCircle class="mr-1 size-4" />
                                Ingreso
                            </Button>
                            <Button
                                size="sm"
                                variant="outline"
                                class="flex-1 text-red-700 border-red-300 hover:bg-red-50"
                                onclick={() => openMovDialog(fondo, 'egreso')}
                            >
                                <ArrowDownCircle class="mr-1 size-4" />
                                Egreso
                            </Button>
                            {#if fondos.length > 1}
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="w-full"
                                    onclick={() => openTransDialog(fondo)}
                                >
                                    <ArrowDownUp class="mr-1 size-4" />
                                    Transferencia
                                </Button>
                            {/if}
                        </CardFooter>
                    </Card>
                {/each}
            </div>
        {/if}
    </div>
</AppLayout>

<!-- ══════════ DIALOG: MOVIMIENTO ══════════ -->
<Dialog bind:open={movDialogOpen}>
    <DialogContent class="sm:max-w-md">
        <DialogTitle>
            {$movForm.tipo === 'ingreso' ? 'Registrar Ingreso' : 'Registrar Egreso'}
        </DialogTitle>
        <DialogDescription>
            {#if movFondo}
                Fondo: <strong>{movFondo.nombre}</strong> — Saldo: {formatCOP(movFondo.saldo_actual)}
            {/if}
        </DialogDescription>

        <form onsubmit={(e) => { e.preventDefault(); submitMovimiento(); }} class="space-y-4">
            <!-- Tipo -->
            <div class="space-y-1.5">
                <Label>Tipo de movimiento</Label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="mov_tipo"
                            value="ingreso"
                            bind:group={$movForm.tipo}
                        />
                        <span class="text-sm font-medium text-green-700">Ingreso</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="mov_tipo"
                            value="egreso"
                            bind:group={$movForm.tipo}
                        />
                        <span class="text-sm font-medium text-red-700">Egreso</span>
                    </label>
                </div>
                {#if $movForm.errors.tipo}
                    <p class="text-xs text-destructive">{$movForm.errors.tipo}</p>
                {/if}
            </div>

            <!-- Monto -->
            <div class="space-y-1.5">
                <Label for="mov_monto">Monto (COP)</Label>
                <div class="relative">
                    <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        id="mov_monto"
                        type="number"
                        min="1"
                        step="1"
                        placeholder="0"
                        class="pl-9 {$movForm.errors.monto ? 'border-destructive' : ''}"
                        bind:value={$movForm.monto}
                    />
                </div>
                {#if $movForm.errors.monto}
                    <p class="text-xs text-destructive">{$movForm.errors.monto}</p>
                {/if}
            </div>

            <!-- Descripción -->
            <div class="space-y-1.5">
                <Label for="mov_descripcion">Descripción</Label>
                <Input
                    id="mov_descripcion"
                    type="text"
                    placeholder="Motivo del movimiento..."
                    bind:value={$movForm.descripcion}
                    class={$movForm.errors.descripcion ? 'border-destructive' : ''}
                />
                {#if $movForm.errors.descripcion}
                    <p class="text-xs text-destructive">{$movForm.errors.descripcion}</p>
                {/if}
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" onclick={() => (movDialogOpen = false)}>
                    Cancelar
                </Button>
                <Button
                    type="submit"
                    disabled={$movForm.processing}
                    class={$movForm.tipo === 'egreso' ? 'bg-red-600 hover:bg-red-700 text-white' : ''}
                >
                    {$movForm.processing ? 'Guardando...' : 'Registrar'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>

<!-- ══════════ DIALOG: TRANSFERENCIA ══════════ -->
<Dialog bind:open={transDialogOpen}>
    <DialogContent class="sm:max-w-md">
        <DialogTitle>Transferencia entre Fondos</DialogTitle>
        <DialogDescription>
            {#if transFondoOrigen}
                Origen: <strong>{transFondoOrigen.nombre}</strong> — Saldo: {formatCOP(transFondoOrigen.saldo_actual)}
            {/if}
        </DialogDescription>

        <form onsubmit={(e) => { e.preventDefault(); submitTransferencia(); }} class="space-y-4">
            <input type="hidden" name="fondo_origen_id" value={$transForm.fondo_origen_id} />

            <!-- Fondo destino -->
            <div class="space-y-1.5">
                <Label>Fondo destino</Label>
                <Select
                    value={$transForm.fondo_destino_id}
                    onValueChange={(v) => ($transForm.fondo_destino_id = v)}
                >
                    <SelectTrigger class="w-full {$transForm.errors.fondo_destino_id ? 'border-destructive' : ''}">
                        {transDestinoLabel}
                    </SelectTrigger>
                    <SelectContent>
                        {#each transDestinos as dest (dest.id)}
                            <SelectItem value={String(dest.id)}>
                                {dest.nombre}
                            </SelectItem>
                        {/each}
                    </SelectContent>
                </Select>
                {#if $transForm.errors.fondo_destino_id}
                    <p class="text-xs text-destructive">{$transForm.errors.fondo_destino_id}</p>
                {/if}
            </div>

            <!-- Monto -->
            <div class="space-y-1.5">
                <Label for="trans_monto">Monto (COP)</Label>
                <div class="relative">
                    <DollarSign class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" />
                    <Input
                        id="trans_monto"
                        type="number"
                        min="1"
                        step="1"
                        placeholder="0"
                        class="pl-9 {$transForm.errors.monto ? 'border-destructive' : ''}"
                        bind:value={$transForm.monto}
                    />
                </div>
                {#if $transForm.errors.monto}
                    <p class="text-xs text-destructive">{$transForm.errors.monto}</p>
                {/if}
            </div>

            <!-- Descripción -->
            <div class="space-y-1.5">
                <Label for="trans_descripcion">Descripción</Label>
                <Input
                    id="trans_descripcion"
                    type="text"
                    placeholder="Motivo de la transferencia..."
                    bind:value={$transForm.descripcion}
                    class={$transForm.errors.descripcion ? 'border-destructive' : ''}
                />
                {#if $transForm.errors.descripcion}
                    <p class="text-xs text-destructive">{$transForm.errors.descripcion}</p>
                {/if}
            </div>

            {#if $transForm.errors.fondo_origen_id}
                <div class="flex items-center gap-2 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                    <AlertCircle class="size-4 shrink-0" />
                    {$transForm.errors.fondo_origen_id}
                </div>
            {/if}

            <DialogFooter>
                <Button type="button" variant="outline" onclick={() => (transDialogOpen = false)}>
                    Cancelar
                </Button>
                <Button
                    type="submit"
                    disabled={$transForm.processing || !$transForm.fondo_destino_id}
                >
                    <ArrowDownUp class="mr-2 size-4" />
                    {$transForm.processing ? 'Procesando...' : 'Transferir'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
