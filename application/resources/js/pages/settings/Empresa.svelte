<script lang="ts">
    import { useForm } from '@inertiajs/svelte'
    import { untrack } from 'svelte'
    import { AlertTriangle, Building2, FileText, Settings } from 'lucide-svelte'
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
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select'
    import type { BreadcrumbItem } from '@/types'
    import type { Configuracion, ResolucionDian } from '@/types/models'
    import { formatFecha } from '@/lib/currency'

    let {
        configuracion,
        resolucion,
        errors = {},
    }: {
        configuracion: Configuracion | null
        resolucion: ResolucionDian | null
        errors?: Record<string, string>
    } = $props()

    const empresa = $derived(configuracion?.empresa)

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Configuración', href: '/settings/empresa' },
        { title: 'Empresa', href: '/settings/empresa' },
    ]

    const form = useForm(untrack(() => ({
        // Empresa
        razon_social:        empresa?.razon_social ?? '',
        nit:                 empresa?.nit ?? '',
        digito_verificacion: empresa?.digito_verificacion ?? '',
        regimen_tributario:  empresa?.regimen_tributario ?? 'no_responsable_iva',
        email:               empresa?.email ?? '',
        telefono:            empresa?.telefono ?? '',
        direccion:           empresa?.direccion ?? '',
        ciudad:              empresa?.ciudad ?? '',
        departamento:        empresa?.departamento ?? '',
        // Configuracion
        zona_horaria:          configuracion?.zona_horaria ?? 'America/Bogota',
        dias_vencimiento_cred: configuracion?.dias_vencimiento_cred ?? 30,
        prefijo_nota_credito:  configuracion?.prefijo_nota_credito ?? 'NC',
        prefijo_nota_debito:   configuracion?.prefijo_nota_debito ?? 'ND',
        logo:                  null as File | null,
    })))

    function handleSubmit(e: Event) {
        e.preventDefault()
        $form.put('/settings/empresa', { preserveScroll: true })
    }

    // DIAN resolution status helpers
    const hoy = new Date()

    const diasParaVencer = $derived(() => {
        if (!resolucion) return null
        const fin = new Date(resolucion.fecha_fin)
        return Math.ceil((fin.getTime() - hoy.getTime()) / (1000 * 60 * 60 * 24))
    })

    const porcentajeUsado = $derived(() => {
        if (!resolucion) return 0
        const rango = resolucion.rango_hasta - resolucion.rango_desde
        const usado = resolucion.numero_actual - resolucion.rango_desde + 1
        return rango > 0 ? Math.round((usado / rango) * 100) : 0
    })

    const advertenciaVencimiento = $derived(
        diasParaVencer() !== null && diasParaVencer()! <= 30
    )
    const advertenciaRango = $derived(porcentajeUsado() >= 90)
</script>

<AppHead title="Configuración de Empresa" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Building2 class="text-primary size-6" />
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Configuración de Empresa</h1>
                <p class="text-muted-foreground text-sm">
                    Administra los datos de tu empresa y configuración del sistema
                </p>
            </div>
        </div>

        <!-- Flash messages -->
        {#if $form.recentlySuccessful}
            <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                Configuración guardada correctamente.
            </div>
        {/if}

        <form onsubmit={handleSubmit} class="space-y-6" enctype="multipart/form-data">
            <!-- Datos de la empresa -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Building2 class="size-4" />
                        Datos de la Empresa
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Razon social -->
                    <div class="grid gap-2">
                        <Label for="razon_social">Razón Social <span class="text-destructive">*</span></Label>
                        <Input
                            id="razon_social"
                            name="razon_social"
                            bind:value={$form.razon_social}
                            placeholder="Nombre o razón social de la empresa"
                            required
                        />
                        <InputError message={$form.errors.razon_social} />
                    </div>

                    <!-- NIT + dígito -->
                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2 grid gap-2">
                            <Label for="nit">NIT <span class="text-destructive">*</span></Label>
                            <Input
                                id="nit"
                                name="nit"
                                bind:value={$form.nit}
                                placeholder="900123456"
                                required
                            />
                            <InputError message={$form.errors.nit} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="digito_verificacion">Dígito <span class="text-destructive">*</span></Label>
                            <Input
                                id="digito_verificacion"
                                name="digito_verificacion"
                                bind:value={$form.digito_verificacion}
                                placeholder="0"
                                maxlength={1}
                                required
                            />
                            <InputError message={$form.errors.digito_verificacion} />
                        </div>
                    </div>

                    <!-- Régimen tributario -->
                    <div class="grid gap-2">
                        <Label for="regimen_tributario">Régimen Tributario <span class="text-destructive">*</span></Label>
                        <Select bind:value={$form.regimen_tributario}>
                            <SelectTrigger id="regimen_tributario">
                                {$form.regimen_tributario === 'responsable_iva'
                                    ? 'Responsable de IVA (Gran Contribuyente / Régimen Común)'
                                    : 'No Responsable de IVA (Régimen Simple / Persona Natural)'}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="responsable_iva">
                                    Responsable de IVA (Gran Contribuyente / Régimen Común)
                                </SelectItem>
                                <SelectItem value="no_responsable_iva">
                                    No Responsable de IVA (Régimen Simple / Persona Natural)
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError message={$form.errors.regimen_tributario} />
                    </div>

                    <Separator />

                    <!-- Contacto -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="email">Correo Electrónico</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                bind:value={$form.email}
                                placeholder="empresa@correo.com"
                            />
                            <InputError message={$form.errors.email} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="telefono">Teléfono</Label>
                            <Input
                                id="telefono"
                                name="telefono"
                                bind:value={$form.telefono}
                                placeholder="601 123 4567"
                            />
                            <InputError message={$form.errors.telefono} />
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="grid gap-2">
                        <Label for="direccion">Dirección</Label>
                        <Input
                            id="direccion"
                            name="direccion"
                            bind:value={$form.direccion}
                            placeholder="Calle 123 # 45-67"
                        />
                        <InputError message={$form.errors.direccion} />
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="ciudad">Ciudad</Label>
                            <Input
                                id="ciudad"
                                name="ciudad"
                                bind:value={$form.ciudad}
                                placeholder="Bogotá"
                            />
                            <InputError message={$form.errors.ciudad} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="departamento">Departamento</Label>
                            <Input
                                id="departamento"
                                name="departamento"
                                bind:value={$form.departamento}
                                placeholder="Cundinamarca"
                            />
                            <InputError message={$form.errors.departamento} />
                        </div>
                    </div>

                    <!-- Logo -->
                    <div class="grid gap-2">
                        <Label for="logo">Logo de la Empresa</Label>
                        {#if configuracion?.logo}
                            <div class="mb-2">
                                <img
                                    src={`/storage/${configuracion.logo}`}
                                    alt="Logo actual"
                                    class="h-16 w-auto rounded border object-contain"
                                />
                                <p class="text-muted-foreground mt-1 text-xs">Logo actual. Carga uno nuevo para reemplazarlo.</p>
                            </div>
                        {/if}
                        <input
                            id="logo"
                            name="logo"
                            type="file"
                            accept="image/*"
                            class="block w-full text-sm text-slate-500 file:mr-4 file:rounded file:border-0 file:bg-primary/10 file:px-3 file:py-1 file:text-sm file:font-medium hover:file:bg-primary/20"
                            onchange={(e) => {
                                const target = e.target as HTMLInputElement
                                $form.logo = target.files?.[0] ?? null
                            }}
                        />
                        <InputError message={$form.errors.logo} />
                    </div>
                </CardContent>
            </Card>

            <!-- Configuración del sistema -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <Settings class="size-4" />
                        Configuración del Sistema
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="zona_horaria">Zona Horaria</Label>
                            <Select bind:value={$form.zona_horaria}>
                                <SelectTrigger id="zona_horaria">{$form.zona_horaria}</SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="America/Bogota">America/Bogota (UTC-5)</SelectItem>
                                    <SelectItem value="America/Caracas">America/Caracas (UTC-4)</SelectItem>
                                    <SelectItem value="America/Lima">America/Lima (UTC-5)</SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError message={$form.errors.zona_horaria} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="dias_vencimiento_cred">Días de vencimiento de crédito</Label>
                            <Input
                                id="dias_vencimiento_cred"
                                name="dias_vencimiento_cred"
                                type="number"
                                min={1}
                                max={365}
                                bind:value={$form.dias_vencimiento_cred}
                            />
                            <InputError message={$form.errors.dias_vencimiento_cred} />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="prefijo_nota_credito">Prefijo Nota Crédito</Label>
                            <Input
                                id="prefijo_nota_credito"
                                name="prefijo_nota_credito"
                                bind:value={$form.prefijo_nota_credito}
                                placeholder="NC"
                                maxlength={10}
                            />
                            <InputError message={$form.errors.prefijo_nota_credito} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="prefijo_nota_debito">Prefijo Nota Débito</Label>
                            <Input
                                id="prefijo_nota_debito"
                                name="prefijo_nota_debito"
                                bind:value={$form.prefijo_nota_debito}
                                placeholder="ND"
                                maxlength={10}
                            />
                            <InputError message={$form.errors.prefijo_nota_debito} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- DIAN info card -->
            <Card class={advertenciaVencimiento || advertenciaRango ? 'border-amber-400' : ''}>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-base">
                        <FileText class="size-4" />
                        Resolución DIAN Activa
                        {#if advertenciaVencimiento || advertenciaRango}
                            <Badge variant="destructive" class="ml-auto gap-1">
                                <AlertTriangle class="size-3" />
                                Atención requerida
                            </Badge>
                        {:else if resolucion}
                            <Badge variant="secondary" class="ml-auto">Vigente</Badge>
                        {/if}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    {#if resolucion}
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                                <div>
                                    <p class="text-muted-foreground font-medium">Resolución</p>
                                    <p class="font-mono">{resolucion.numero_resolucion}</p>
                                </div>
                                <div>
                                    <p class="text-muted-foreground font-medium">Prefijo</p>
                                    <p>{resolucion.prefijo ?? '(sin prefijo)'}</p>
                                </div>
                                <div>
                                    <p class="text-muted-foreground font-medium">Rango</p>
                                    <p class="font-mono">{resolucion.rango_desde} – {resolucion.rango_hasta}</p>
                                </div>
                                <div>
                                    <p class="text-muted-foreground font-medium">Número actual</p>
                                    <p class="font-mono">{resolucion.numero_actual}</p>
                                </div>
                            </div>

                            <!-- Progress bar -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-muted-foreground">Uso del rango</span>
                                    <span class={advertenciaRango ? 'font-semibold text-amber-600' : 'text-muted-foreground'}>
                                        {porcentajeUsado()}%
                                    </span>
                                </div>
                                <div class="h-2 w-full rounded-full bg-secondary">
                                    <div
                                        class="h-2 rounded-full transition-all {porcentajeUsado() >= 90
                                            ? 'bg-red-500'
                                            : porcentajeUsado() >= 70
                                              ? 'bg-amber-500'
                                              : 'bg-primary'}"
                                        style="width: {porcentajeUsado()}%"
                                    ></div>
                                </div>
                            </div>

                            <!-- Vencimiento -->
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-muted-foreground">Vigencia:</span>
                                <span>{formatFecha(resolucion.fecha_inicio)}</span>
                                <span class="text-muted-foreground">hasta</span>
                                <span class={advertenciaVencimiento ? 'font-semibold text-amber-600' : ''}>
                                    {formatFecha(resolucion.fecha_fin)}
                                </span>
                                {#if diasParaVencer() !== null}
                                    <span class={`text-xs ${advertenciaVencimiento ? 'text-amber-600 font-semibold' : 'text-muted-foreground'}`}>
                                        ({diasParaVencer()! > 0
                                            ? `vence en ${diasParaVencer()} días`
                                            : 'VENCIDA'})
                                    </span>
                                {/if}
                            </div>

                            {#if advertenciaVencimiento}
                                <div class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                                    <span>La resolución vence pronto. Tramita una nueva resolución ante la DIAN y regístrala en <a href="/settings/dian" class="underline">Resoluciones DIAN</a>.</span>
                                </div>
                            {/if}
                            {#if advertenciaRango}
                                <div class="flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    <AlertTriangle class="mt-0.5 size-4 shrink-0" />
                                    <span>Se ha utilizado el {porcentajeUsado()}% del rango de numeración. Solicita una ampliación o nueva resolución.</span>
                                </div>
                            {/if}
                        </div>
                    {:else}
                        <div class="py-4 text-center">
                            <p class="text-muted-foreground text-sm">No hay resolución DIAN activa.</p>
                            <Button variant="outline" class="mt-3" href="/settings/dian">
                                Registrar Resolución
                            </Button>
                        </div>
                    {/if}
                </CardContent>
            </Card>

            <!-- Save button -->
            <div class="flex items-center justify-end gap-3">
                {#if $form.recentlySuccessful}
                    <p class="text-sm text-green-600">Guardado correctamente.</p>
                {/if}
                <Button type="submit" disabled={$form.processing}>
                    {#if $form.processing}
                        Guardando…
                    {:else}
                        Guardar Cambios
                    {/if}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
