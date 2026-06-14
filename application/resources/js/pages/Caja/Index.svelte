<script lang="ts">
    import { router, useForm } from '@inertiajs/svelte';
    import {
        DollarSign,
        ArrowUpCircle,
        ArrowDownCircle,
        Lock,
        Unlock,
        Clock,
        AlertCircle,
        Plus,
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
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    // --- Types ---
    interface Persona { nombres: string; apellidos: string | null; }
    interface User { persona: Persona | null; }
    interface Colaborador { user: User | null; }
    interface Cajero { id: number; colaborador: Colaborador | null; }
    interface TurnoActivo {
        id: number;
        cajero_id: number;
        cajero: Cajero | null;
        saldo_inicial: string;
        apertura: string;
    }
    interface Caja {
        id: number;
        nombre: string;
        ubicacion: string | null;
        activo: boolean;
        turno_activo: TurnoActivo | null;
    }
    interface MedioPago { id: number; nombre: string; }
    interface Fondo {
        id: number;
        nombre: string;
        tipo: string;
        saldo_actual: string;
        activo: boolean;
        medio_pago: MedioPago | null;
    }

    let { cajas, fondos }: { cajas: Caja[]; fondos: Fondo[] } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Caja', href: '/caja' },
        { title: 'Gestión de Caja', href: '/caja' },
    ];

    // --- COP formatter ---
    function formatCOP(value: string | number): string {
        return new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(Number(value));
    }

    // --- Abrir turno dialog ---
    let abrirDialogOpen = $state(false);
    let cajaSeleccionada = $state<Caja | null>(null);

    const abrirForm = useForm({
        caja_id: '',
        saldo_inicial: '',
    });

    function openAbrirDialog(caja: Caja) {
        cajaSeleccionada = caja;
        $abrirForm.caja_id = String(caja.id);
        $abrirForm.saldo_inicial = '';
        abrirDialogOpen = true;
    }

    function submitAbrirTurno() {
        $abrirForm.post('/caja/turnos/abrir', {
            onSuccess: () => {
                abrirDialogOpen = false;
            },
        });
    }

    // --- Movimiento fondo dialog ---
    let movDialogOpen = $state(false);
    let movFondo = $state<Fondo | null>(null);
    let movTipoPreset = $state<'ingreso' | 'egreso'>('ingreso');

    const movForm = useForm({
        tipo: 'ingreso',
        monto: '',
        descripcion: '',
    });

    function openMovDialog(fondo: Fondo, tipo: 'ingreso' | 'egreso') {
        movFondo = fondo;
        movTipoPreset = tipo;
        $movForm.tipo = tipo;
        $movForm.monto = '';
        $movForm.descripcion = '';
        movDialogOpen = true;
    }

    function submitMovimiento() {
        if (!movFondo) return;
        $movForm.post(`/caja/fondos/${movFondo.id}/movimiento`, {
            onSuccess: () => {
                movDialogOpen = false;
            },
        });
    }

    // --- Helpers ---
    function cajeroNombre(turno: TurnoActivo | null): string {
        if (!turno?.cajero?.colaborador?.user?.persona) return '—';
        const p = turno.cajero.colaborador.user.persona;
        return `${p.nombres} ${p.apellidos ?? ''}`.trim();
    }

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
</script>

<AppHead title="Gestión de Caja" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-8 p-6">

        <!-- ══════════ CAJAS REGISTRADORAS ══════════ -->
        <section>
            <div class="mb-4 flex items-center gap-2">
                <DollarSign class="size-5 text-primary" />
                <h2 class="text-xl font-bold">Cajas Registradoras</h2>
                <Badge variant="outline">{cajas.length} cajas</Badge>
            </div>

            {#if cajas.length === 0}
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed py-12 text-muted-foreground">
                    <AlertCircle class="mb-3 size-10 opacity-40" />
                    <p class="text-sm">No hay cajas registradas.</p>
                </div>
            {:else}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {#each cajas as caja (caja.id)}
                        {@const abierta = !!caja.turno_activo}
                        <Card class={abierta ? 'border-green-500/60' : ''}>
                            <CardHeader class="pb-3">
                                <div class="flex items-start justify-between gap-2">
                                    <CardTitle class="text-base">{caja.nombre}</CardTitle>
                                    {#if abierta}
                                        <Badge class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200 shrink-0">
                                            <Unlock class="mr-1 size-3" />
                                            Abierta
                                        </Badge>
                                    {:else}
                                        <Badge variant="secondary" class="shrink-0">
                                            <Lock class="mr-1 size-3" />
                                            Cerrada
                                        </Badge>
                                    {/if}
                                </div>
                                {#if caja.ubicacion}
                                    <p class="text-xs text-muted-foreground">{caja.ubicacion}</p>
                                {/if}
                            </CardHeader>

                            <CardContent class="space-y-2 pb-3">
                                {#if abierta && caja.turno_activo}
                                    <div class="rounded-md bg-muted/40 p-3 text-sm space-y-1">
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Cajero:</span>
                                            <span class="font-medium">{cajeroNombre(caja.turno_activo)}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-muted-foreground">Saldo inicial:</span>
                                            <span class="font-medium">{formatCOP(caja.turno_activo.saldo_inicial)}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-muted-foreground">Apertura:</span>
                                            <span class="flex items-center gap-1 text-xs">
                                                <Clock class="size-3" />
                                                {new Date(caja.turno_activo.apertura).toLocaleString('es-CO')}
                                            </span>
                                        </div>
                                    </div>
                                {:else}
                                    <p class="text-sm text-muted-foreground">Sin turno activo.</p>
                                {/if}
                            </CardContent>

                            <CardFooter class="flex gap-2 flex-wrap pt-0">
                                {#if !abierta}
                                    <Button size="sm" onclick={() => openAbrirDialog(caja)}>
                                        <Unlock class="mr-1 size-4" />
                                        Abrir Turno
                                    </Button>
                                {:else}
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        href={`/caja/turnos/${caja.turno_activo!.id}`}
                                    >
                                        <Clock class="mr-1 size-4" />
                                        Ver Turno Activo
                                    </Button>
                                    <Button
                                        size="sm"
                                        variant="destructive"
                                        href={`/caja/turnos/${caja.turno_activo!.id}/cerrar`}
                                    >
                                        <Lock class="mr-1 size-4" />
                                        Cerrar Turno
                                    </Button>
                                {/if}
                            </CardFooter>
                        </Card>
                    {/each}
                </div>
            {/if}
        </section>

        <Separator />

        <!-- ══════════ FONDOS ══════════ -->
        <section>
            <div class="mb-4 flex items-center gap-2">
                <DollarSign class="size-5 text-primary" />
                <h2 class="text-xl font-bold">Fondos</h2>
                <Badge variant="outline">{fondos.length} fondos</Badge>
            </div>

            {#if fondos.length === 0}
                <div class="flex flex-col items-center justify-center rounded-xl border border-dashed py-12 text-muted-foreground">
                    <AlertCircle class="mb-3 size-10 opacity-40" />
                    <p class="text-sm">No hay fondos activos.</p>
                </div>
            {:else}
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    {#each fondos as fondo (fondo.id)}
                        <Card>
                            <CardHeader class="pb-2">
                                <div class="flex items-start justify-between gap-2">
                                    <CardTitle class="text-sm font-semibold">{fondo.nombre}</CardTitle>
                                    <Badge class="{tipoBadgeClass(fondo.tipo)} shrink-0 text-xs">
                                        {tipoLabel(fondo.tipo)}
                                    </Badge>
                                </div>
                                {#if fondo.medio_pago}
                                    <p class="text-xs text-muted-foreground">{fondo.medio_pago.nombre}</p>
                                {/if}
                            </CardHeader>

                            <CardContent class="pb-3">
                                <p class="text-2xl font-bold tracking-tight">
                                    {formatCOP(fondo.saldo_actual)}
                                </p>
                            </CardContent>

                            <CardFooter class="flex gap-2 pt-0">
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
                            </CardFooter>
                        </Card>
                    {/each}
                </div>
            {/if}
        </section>
    </div>
</AppLayout>

<!-- ══════════ DIALOG: ABRIR TURNO ══════════ -->
<Dialog bind:open={abrirDialogOpen}>
    <DialogContent class="sm:max-w-md">
        <DialogTitle>Abrir Turno de Caja</DialogTitle>
        <DialogDescription>
            {#if cajaSeleccionada}
                Abriendo turno para <strong>{cajaSeleccionada.nombre}</strong>. Ingrese el saldo inicial en efectivo.
            {/if}
        </DialogDescription>

        <form onsubmit={(e) => { e.preventDefault(); submitAbrirTurno(); }} class="space-y-4">
            <input type="hidden" name="caja_id" value={$abrirForm.caja_id} />

            <div class="space-y-1.5">
                <Label for="saldo_inicial">Saldo inicial (COP)</Label>
                <Input
                    id="saldo_inicial"
                    type="number"
                    min="0"
                    step="1"
                    placeholder="0"
                    bind:value={$abrirForm.saldo_inicial}
                    class={$abrirForm.errors.saldo_inicial ? 'border-destructive' : ''}
                />
                {#if $abrirForm.errors.saldo_inicial}
                    <p class="text-xs text-destructive">{$abrirForm.errors.saldo_inicial}</p>
                {/if}
            </div>

            {#if $abrirForm.errors.caja_id}
                <div class="flex items-center gap-2 rounded-md bg-destructive/10 px-3 py-2 text-sm text-destructive">
                    <AlertCircle class="size-4 shrink-0" />
                    {$abrirForm.errors.caja_id}
                </div>
            {/if}

            <DialogFooter>
                <Button type="button" variant="outline" onclick={() => (abrirDialogOpen = false)}>
                    Cancelar
                </Button>
                <Button type="submit" disabled={$abrirForm.processing}>
                    {$abrirForm.processing ? 'Abriendo...' : 'Abrir Turno'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>

<!-- ══════════ DIALOG: NUEVO MOVIMIENTO FONDO ══════════ -->
<Dialog bind:open={movDialogOpen}>
    <DialogContent class="sm:max-w-md">
        <DialogTitle>
            {movTipoPreset === 'ingreso' ? 'Registrar Ingreso' : 'Registrar Egreso'}
        </DialogTitle>
        <DialogDescription>
            {#if movFondo}
                Fondo: <strong>{movFondo.nombre}</strong> — Saldo actual: {formatCOP(movFondo.saldo_actual)}
            {/if}
        </DialogDescription>

        <form onsubmit={(e) => { e.preventDefault(); submitMovimiento(); }} class="space-y-4">
            <div class="space-y-1.5">
                <Label>Tipo de movimiento</Label>
                <div class="flex gap-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="tipo_mov"
                            value="ingreso"
                            bind:group={$movForm.tipo}
                        />
                        <span class="text-sm text-green-700 font-medium">Ingreso</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="radio"
                            name="tipo_mov"
                            value="egreso"
                            bind:group={$movForm.tipo}
                        />
                        <span class="text-sm text-red-700 font-medium">Egreso</span>
                    </label>
                </div>
            </div>

            <div class="space-y-1.5">
                <Label for="mov_monto">Monto (COP)</Label>
                <Input
                    id="mov_monto"
                    type="number"
                    min="1"
                    step="1"
                    placeholder="0"
                    bind:value={$movForm.monto}
                    class={$movForm.errors.monto ? 'border-destructive' : ''}
                />
                {#if $movForm.errors.monto}
                    <p class="text-xs text-destructive">{$movForm.errors.monto}</p>
                {/if}
            </div>

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
                    class={movTipoPreset === 'egreso' ? 'bg-red-600 hover:bg-red-700' : ''}
                >
                    {$movForm.processing ? 'Guardando...' : 'Registrar'}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>
