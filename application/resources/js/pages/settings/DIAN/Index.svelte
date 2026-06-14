<script lang="ts">
    import { useForm } from '@inertiajs/svelte'
    import { untrack } from 'svelte'
    import { AlertTriangle, CheckCircle2, FileText, History, Plus, XCircle } from 'lucide-svelte'
    import AppHead from '@/components/AppHead.svelte'
    import InputError from '@/components/InputError.svelte'
    import AppLayout from '@/layouts/AppLayout.svelte'
    import { Badge } from '@/components/ui/badge'
    import { Button } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import { Input } from '@/components/ui/input'
    import { Label } from '@/components/ui/label'
    import { Separator } from '@/components/ui/separator'
    import type { BreadcrumbItem } from '@/types'
    import type { ResolucionDian } from '@/types/models'
    import { formatFecha } from '@/lib/currency'

    let {
        resoluciones,
    }: {
        resoluciones: ResolucionDian[]
    } = $props()

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Configuración', href: '/settings/empresa' },
        { title: 'Resoluciones DIAN', href: '/settings/dian' },
    ]

    const activa = $derived(resoluciones.find((r) => r.activo) ?? null)
    const historicas = $derived(resoluciones.filter((r) => !r.activo))

    const hoy = new Date()

    function porcentajeUsado(r: ResolucionDian): number {
        const rango = r.rango_hasta - r.rango_desde
        const usado = r.numero_actual - r.rango_desde + 1
        return rango > 0 ? Math.min(100, Math.round((usado / rango) * 100)) : 0
    }

    function diasParaVencer(r: ResolucionDian): number {
        const fin = new Date(r.fecha_fin)
        return Math.ceil((fin.getTime() - hoy.getTime()) / (1000 * 60 * 60 * 24))
    }

    function estadoResolucion(r: ResolucionDian): 'vigente' | 'vencida' | 'agotada' {
        if (new Date(r.fecha_fin) < hoy) return 'vencida'
        if (r.numero_actual >= r.rango_hasta) return 'agotada'
        return 'vigente'
    }

    // Nueva resolución form
    let mostrarFormulario = $state(false)

    const form = useForm(untrack(() => ({
        numero_resolucion: '',
        fecha_resolucion:  '',
        fecha_inicio:      '',
        fecha_fin:         '',
        prefijo:           '',
        rango_desde:       '',
        rango_hasta:       '',
        clave_tecnica:     '',
    })))

    function handleSubmit(e: Event) {
        e.preventDefault()
        $form.post('/settings/dian', {
            preserveScroll: true,
            onSuccess: () => {
                mostrarFormulario = false
                $form.reset()
            },
        })
    }
</script>

<AppHead title="Resoluciones DIAN" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <FileText class="text-primary size-6" />
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Resoluciones DIAN</h1>
                    <p class="text-muted-foreground text-sm">
                        Gestiona las resoluciones de facturación electrónica
                    </p>
                </div>
            </div>
            <Button onclick={() => (mostrarFormulario = !mostrarFormulario)}>
                <Plus class="mr-2 size-4" />
                Nueva Resolución
            </Button>
        </div>

        <!-- Resolución activa -->
        {#if activa}
            {@const estado = estadoResolucion(activa)}
            {@const pct = porcentajeUsado(activa)}
            {@const dias = diasParaVencer(activa)}
            <Card class={estado !== 'vigente' ? 'border-destructive' : dias <= 30 || pct >= 90 ? 'border-amber-400' : 'border-green-400'}>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        {#if estado === 'vigente'}
                            <CheckCircle2 class="size-5 text-green-600" />
                            Resolución Activa
                        {:else if estado === 'vencida'}
                            <XCircle class="size-5 text-destructive" />
                            Resolución Vencida
                        {:else}
                            <AlertTriangle class="size-5 text-amber-600" />
                            Resolución Agotada
                        {/if}

                        <Badge
                            class="ml-2 {estado === 'vigente'
                                ? 'bg-green-600 text-white'
                                : estado === 'vencida'
                                  ? 'bg-destructive text-white'
                                  : 'bg-amber-500 text-white'}"
                        >
                            {estado === 'vigente' ? 'Vigente' : estado === 'vencida' ? 'Vencida' : 'Agotada'}
                        </Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                        <div>
                            <p class="text-muted-foreground font-medium">N.° Resolución</p>
                            <p class="font-mono font-semibold">{activa.numero_resolucion}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground font-medium">Prefijo</p>
                            <p class="font-semibold">{activa.prefijo ?? '(sin prefijo)'}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground font-medium">Rango</p>
                            <p class="font-mono">{activa.rango_desde} – {activa.rango_hasta}</p>
                        </div>
                        <div>
                            <p class="text-muted-foreground font-medium">Número actual</p>
                            <p class="font-mono font-semibold">{activa.numero_actual}</p>
                        </div>
                    </div>

                    <!-- Progress bar -->
                    <div class="space-y-1">
                        <div class="flex justify-between text-xs">
                            <span class="text-muted-foreground">
                                {activa.numero_actual - activa.rango_desde + 1} de
                                {activa.rango_hasta - activa.rango_desde + 1} números usados
                            </span>
                            <span class={pct >= 90 ? 'font-semibold text-amber-600' : 'text-muted-foreground'}>
                                {pct}%
                            </span>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-secondary">
                            <div
                                class="h-3 rounded-full transition-all {pct >= 90
                                    ? 'bg-red-500'
                                    : pct >= 70
                                      ? 'bg-amber-500'
                                      : 'bg-green-500'}"
                                style="width: {pct}%"
                            ></div>
                        </div>
                    </div>

                    <!-- Vencimiento -->
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Inicio:</span>
                            <span class="ml-1">{formatFecha(activa.fecha_inicio)}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Vence:</span>
                            <span class={`ml-1 ${dias <= 30 ? 'font-semibold text-amber-600' : dias <= 0 ? 'font-semibold text-destructive' : ''}`}>
                                {formatFecha(activa.fecha_fin)}
                            </span>
                            {#if dias > 0}
                                <span class={`ml-1 text-xs ${dias <= 30 ? 'text-amber-600' : 'text-muted-foreground'}`}>
                                    ({dias} días)
                                </span>
                            {:else}
                                <span class="text-destructive ml-1 text-xs font-semibold">(VENCIDA)</span>
                            {/if}
                        </div>
                    </div>

                    <!-- Warnings -->
                    {#if dias <= 30 && dias > 0}
                        <div class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                            <span>La resolución vence en {dias} días. Solicita y registra una nueva resolución DIAN para no interrumpir la facturación.</span>
                        </div>
                    {/if}
                    {#if pct >= 90}
                        <div class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                            <span>Se ha utilizado el {pct}% del rango autorizado ({activa.numero_actual}/{activa.rango_hasta}). Solicita una ampliación o nueva resolución.</span>
                        </div>
                    {/if}
                    {#if estado === 'vencida'}
                        <div class="bg-destructive/10 border-destructive/30 text-destructive flex items-start gap-2 rounded-md border px-3 py-2 text-sm">
                            <XCircle class="mt-0.5 size-4 shrink-0" />
                            <span>Esta resolución está vencida. No puedes emitir nuevas facturas. Registra la nueva resolución que obtengas ante la DIAN.</span>
                        </div>
                    {/if}
                </CardContent>
            </Card>
        {:else}
            <Card class="border-destructive">
                <CardContent class="py-8 text-center">
                    <FileText class="text-muted-foreground mx-auto mb-3 size-12 opacity-40" />
                    <p class="font-medium">No hay resolución DIAN activa</p>
                    <p class="text-muted-foreground mt-1 text-sm">
                        Sin una resolución activa no puedes emitir facturas electrónicas.
                    </p>
                    <Button class="mt-4" onclick={() => (mostrarFormulario = true)}>
                        <Plus class="mr-2 size-4" />
                        Registrar Resolución
                    </Button>
                </CardContent>
            </Card>
        {/if}

        <!-- Nueva resolución form -->
        {#if mostrarFormulario}
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Registrar Nueva Resolución</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onsubmit={handleSubmit} class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="numero_resolucion">N.° Resolución <span class="text-destructive">*</span></Label>
                                <Input
                                    id="numero_resolucion"
                                    bind:value={$form.numero_resolucion}
                                    placeholder="18764000001234"
                                    required
                                />
                                <InputError message={$form.errors.numero_resolucion} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="fecha_resolucion">Fecha de Resolución <span class="text-destructive">*</span></Label>
                                <Input
                                    id="fecha_resolucion"
                                    type="date"
                                    bind:value={$form.fecha_resolucion}
                                    required
                                />
                                <InputError message={$form.errors.fecha_resolucion} />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="fecha_inicio">Fecha de Inicio <span class="text-destructive">*</span></Label>
                                <Input
                                    id="fecha_inicio"
                                    type="date"
                                    bind:value={$form.fecha_inicio}
                                    required
                                />
                                <InputError message={$form.errors.fecha_inicio} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="fecha_fin">Fecha de Vencimiento <span class="text-destructive">*</span></Label>
                                <Input
                                    id="fecha_fin"
                                    type="date"
                                    bind:value={$form.fecha_fin}
                                    required
                                />
                                <InputError message={$form.errors.fecha_fin} />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div class="grid gap-2">
                                <Label for="prefijo">Prefijo</Label>
                                <Input
                                    id="prefijo"
                                    bind:value={$form.prefijo}
                                    placeholder="SETP (opcional)"
                                    maxlength={10}
                                />
                                <InputError message={$form.errors.prefijo} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="rango_desde">Rango desde <span class="text-destructive">*</span></Label>
                                <Input
                                    id="rango_desde"
                                    type="number"
                                    min={1}
                                    bind:value={$form.rango_desde}
                                    placeholder="990000000"
                                    required
                                />
                                <InputError message={$form.errors.rango_desde} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="rango_hasta">Rango hasta <span class="text-destructive">*</span></Label>
                                <Input
                                    id="rango_hasta"
                                    type="number"
                                    min={1}
                                    bind:value={$form.rango_hasta}
                                    placeholder="995000000"
                                    required
                                />
                                <InputError message={$form.errors.rango_hasta} />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="clave_tecnica">Clave Técnica</Label>
                            <Input
                                id="clave_tecnica"
                                bind:value={$form.clave_tecnica}
                                placeholder="Clave técnica proporcionada por la DIAN (opcional)"
                                class="font-mono text-sm"
                            />
                            <p class="text-muted-foreground text-xs">
                                Requerida para el cálculo del CUFE en facturación electrónica.
                            </p>
                            <InputError message={$form.errors.clave_tecnica} />
                        </div>

                        <div class="rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                            <strong>Nota:</strong> Al registrar una nueva resolución, todas las resoluciones anteriores
                            quedarán inactivas. Esta acción no se puede deshacer.
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <Button
                                type="button"
                                variant="outline"
                                onclick={() => {
                                    mostrarFormulario = false
                                    $form.reset()
                                }}
                            >
                                Cancelar
                            </Button>
                            <Button type="submit" disabled={$form.processing}>
                                {#if $form.processing}
                                    Guardando…
                                {:else}
                                    Registrar y Activar Resolución
                                {/if}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        {/if}

        <!-- Historial de resoluciones -->
        {#if historicas.length > 0}
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <History class="size-4" />
                        Historial de Resoluciones
                    </CardTitle>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left font-medium">N.° Resolución</th>
                                    <th class="px-4 py-3 text-left font-medium">Prefijo</th>
                                    <th class="px-4 py-3 text-left font-medium">Rango</th>
                                    <th class="px-4 py-3 text-right font-medium">Usados</th>
                                    <th class="px-4 py-3 text-left font-medium">Uso</th>
                                    <th class="px-4 py-3 text-left font-medium">Vigencia</th>
                                    <th class="px-4 py-3 text-left font-medium">Estado</th>
                                    <th class="px-4 py-3 text-right font-medium">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each historicas as r (r.id)}
                                    {@const estado = estadoResolucion(r)}
                                    {@const pct = porcentajeUsado(r)}
                                    <tr class="border-b transition-colors hover:bg-muted/30">
                                        <td class="px-4 py-3 font-mono">{r.numero_resolucion}</td>
                                        <td class="px-4 py-3">{r.prefijo ?? '—'}</td>
                                        <td class="px-4 py-3 font-mono text-xs">
                                            {r.rango_desde} – {r.rango_hasta}
                                        </td>
                                        <td class="px-4 py-3 text-right font-mono">
                                            {r.numero_actual}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="h-1.5 w-16 overflow-hidden rounded-full bg-secondary">
                                                    <div
                                                        class="h-1.5 rounded-full {pct >= 90 ? 'bg-red-500' : pct >= 70 ? 'bg-amber-500' : 'bg-green-500'}"
                                                        style="width: {pct}%"
                                                    ></div>
                                                </div>
                                                <span class="text-muted-foreground text-xs">{pct}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-xs">
                                            <span>{formatFecha(r.fecha_inicio)}</span>
                                            <span class="text-muted-foreground mx-1">—</span>
                                            <span>{formatFecha(r.fecha_fin)}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            {#if estado === 'vigente'}
                                                <Badge variant="secondary" class="bg-green-100 text-green-800">Vigente</Badge>
                                            {:else if estado === 'vencida'}
                                                <Badge variant="outline" class="text-muted-foreground">Vencida</Badge>
                                            {:else}
                                                <Badge variant="outline" class="text-amber-600">Agotada</Badge>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <form
                                                method="POST"
                                                action={`/settings/dian/${r.id}/activar`}
                                                onsubmit={(e) => {
                                                    e.preventDefault()
                                                    if (confirm(`¿Activar la resolución ${r.numero_resolucion}? La resolución activa actual quedará inactiva.`)) {
                                                        (e.target as HTMLFormElement).submit()
                                                    }
                                                }}
                                            >
                                                <input type="hidden" name="_method" value="PATCH" />
                                                <Button type="submit" variant="ghost" size="sm">
                                                    Activar
                                                </Button>
                                            </form>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        {/if}
    </div>
</AppLayout>
