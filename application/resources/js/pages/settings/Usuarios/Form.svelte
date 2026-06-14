<script lang="ts">
    import { useForm } from '@inertiajs/svelte'
    import { untrack } from 'svelte'
    import { ArrowLeft, UserCog } from 'lucide-svelte'
    import AppHead from '@/components/AppHead.svelte'
    import InputError from '@/components/InputError.svelte'
    import AppLayout from '@/layouts/AppLayout.svelte'
    import { Button } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import { Checkbox } from '@/components/ui/checkbox'
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
    import type { Rol, User } from '@/types/models'

    let {
        usuario,
        roles,
        auth_user_id,
        errors = {},
    }: {
        usuario: User | null
        roles: Rol[]
        auth_user_id: number
        errors?: Record<string, string>
    } = $props()

    const esEdicion = $derived(usuario !== null)
    const titulo = $derived(esEdicion ? 'Editar Usuario' : 'Nuevo Usuario')
    const esPropioPerfil = $derived(esEdicion && usuario?.id === auth_user_id)

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Configuración', href: '/settings/empresa' },
        { title: 'Usuarios', href: '/settings/usuarios' },
        { title: titulo, href: '#' },
    ])

    const form = useForm(untrack(() => ({
        tipo_identificacion:   usuario?.persona?.tipo_identificacion ?? 'CC',
        numero_identificacion: usuario?.persona?.numero_identificacion ?? '',
        nombres:               usuario?.persona?.nombres ?? '',
        apellidos:             usuario?.persona?.apellidos ?? '',
        email:                 usuario?.email ?? '',
        telefono:              usuario?.persona?.telefono ?? '',
        celular:               usuario?.persona?.celular ?? '',
        rol_id:                String(usuario?.rol?.id ?? (roles[0]?.id ?? '')),
        password:              '',
        password_confirmation: '',
        activo:                usuario?.activo ?? true,
    })))

    function handleSubmit(e: Event) {
        e.preventDefault()
        if (esEdicion && usuario) {
            $form.put(`/settings/usuarios/${usuario.id}`, { preserveScroll: true })
        } else {
            $form.post('/settings/usuarios', { preserveScroll: true })
        }
    }

    const tiposId = [
        { value: 'CC',  label: 'Cédula de Ciudadanía (CC)' },
        { value: 'CE',  label: 'Cédula de Extranjería (CE)' },
        { value: 'TI',  label: 'Tarjeta de Identidad (TI)' },
        { value: 'PAS', label: 'Pasaporte (PAS)' },
        { value: 'NIT', label: 'NIT' },
        { value: 'RC',  label: 'Registro Civil (RC)' },
    ]

    function tipoIdLabel(val: string): string {
        return tiposId.find((t) => t.value === val)?.label ?? val
    }

    function rolLabel(id: string): string {
        return roles.find((r) => String(r.id) === id)?.nombre ?? 'Seleccionar rol…'
    }
</script>

<AppHead title={titulo} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <Button variant="ghost" size="sm" href="/settings/usuarios" class="-ml-2">
                <ArrowLeft class="size-4" />
            </Button>
            <UserCog class="text-primary size-6" />
            <div>
                <h1 class="text-2xl font-bold tracking-tight">{titulo}</h1>
                <p class="text-muted-foreground text-sm">
                    {esEdicion
                        ? 'Actualiza los datos, rol y contraseña del usuario'
                        : 'Completa los datos para crear un nuevo usuario del sistema'}
                </p>
            </div>
        </div>

        <form onsubmit={handleSubmit} class="space-y-6">
            <!-- Identificación -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Identificación</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="grid gap-2">
                            <Label for="tipo_identificacion">Tipo de ID <span class="text-destructive">*</span></Label>
                            <Select bind:value={$form.tipo_identificacion}>
                                <SelectTrigger id="tipo_identificacion">
                                    {tipoIdLabel($form.tipo_identificacion)}
                                </SelectTrigger>
                                <SelectContent>
                                    {#each tiposId as tipo (tipo.value)}
                                        <SelectItem value={tipo.value}>{tipo.label}</SelectItem>
                                    {/each}
                                </SelectContent>
                            </Select>
                            <InputError message={$form.errors.tipo_identificacion} />
                        </div>
                        <div class="col-span-2 grid gap-2">
                            <Label for="numero_identificacion">Número de Identificación <span class="text-destructive">*</span></Label>
                            <Input
                                id="numero_identificacion"
                                name="numero_identificacion"
                                bind:value={$form.numero_identificacion}
                                placeholder="1234567890"
                                required
                            />
                            <InputError message={$form.errors.numero_identificacion} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Datos personales -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Datos Personales</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="nombres">Nombres <span class="text-destructive">*</span></Label>
                            <Input
                                id="nombres"
                                name="nombres"
                                bind:value={$form.nombres}
                                placeholder="Juan"
                                required
                            />
                            <InputError message={$form.errors.nombres} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="apellidos">Apellidos</Label>
                            <Input
                                id="apellidos"
                                name="apellidos"
                                bind:value={$form.apellidos}
                                placeholder="Pérez García"
                            />
                            <InputError message={$form.errors.apellidos} />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
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
                        <div class="grid gap-2">
                            <Label for="celular">Celular</Label>
                            <Input
                                id="celular"
                                name="celular"
                                bind:value={$form.celular}
                                placeholder="300 123 4567"
                            />
                            <InputError message={$form.errors.celular} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Acceso -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Acceso al Sistema</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-2">
                        <Label for="email">Correo Electrónico <span class="text-destructive">*</span></Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            bind:value={$form.email}
                            placeholder="usuario@empresa.com"
                            required
                            autocomplete="email"
                        />
                        <InputError message={$form.errors.email} />
                    </div>

                    <div class="grid gap-2">
                        <Label for="rol_id">Rol <span class="text-destructive">*</span></Label>
                        <Select bind:value={$form.rol_id} disabled={esPropioPerfil}>
                            <SelectTrigger id="rol_id" class={esPropioPerfil ? 'opacity-50 cursor-not-allowed' : ''}>
                                {rolLabel($form.rol_id)}
                            </SelectTrigger>
                            <SelectContent>
                                {#each roles as rol (rol.id)}
                                    <SelectItem value={String(rol.id)}>
                                        {#if rol.nombre === 'administrador'}Administrador
                                        {:else if rol.nombre === 'vendedor'}Vendedor
                                        {:else if rol.nombre === 'cajero'}Cajero
                                        {:else if rol.nombre === 'bodeguero'}Bodeguero
                                        {:else}{rol.nombre}{/if}
                                        {#if rol.descripcion}
                                            <span class="text-muted-foreground ml-1 text-xs">– {rol.descripcion}</span>
                                        {/if}
                                    </SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                        {#if esPropioPerfil}
                            <p class="text-muted-foreground text-xs">
                                No puedes cambiar tu propio rol.
                            </p>
                        {/if}
                        <InputError message={$form.errors.rol_id} />
                    </div>

                    <Separator />

                    <!-- Password -->
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="password">
                                Contraseña
                                {#if !esEdicion}<span class="text-destructive">*</span>{/if}
                            </Label>
                            {#if esEdicion}
                                <p class="text-muted-foreground text-xs">Déjala en blanco para mantener la contraseña actual.</p>
                            {/if}
                            <Input
                                id="password"
                                name="password"
                                type="password"
                                bind:value={$form.password}
                                placeholder={esEdicion ? 'Nueva contraseña (opcional)' : 'Mínimo 8 caracteres'}
                                required={!esEdicion}
                                autocomplete="new-password"
                            />
                            <InputError message={$form.errors.password} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="password_confirmation">Confirmar Contraseña</Label>
                            <Input
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                bind:value={$form.password_confirmation}
                                placeholder="Repite la contraseña"
                                autocomplete="new-password"
                            />
                            <InputError message={$form.errors.password_confirmation} />
                        </div>
                    </div>

                    <!-- Activo (edit only) -->
                    {#if esEdicion}
                        <Separator />
                        <div class="flex items-center gap-3">
                            <Checkbox
                                id="activo"
                                bind:checked={$form.activo}
                            />
                            <div>
                                <Label for="activo" class="cursor-pointer">Usuario activo</Label>
                                <p class="text-muted-foreground text-xs">
                                    Los usuarios inactivos no pueden iniciar sesión en el sistema.
                                </p>
                            </div>
                        </div>
                        <InputError message={$form.errors.activo} />
                    {/if}
                </CardContent>
            </Card>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Button variant="outline" href="/settings/usuarios">Cancelar</Button>
                <Button type="submit" disabled={$form.processing} onclick={() => console.log('button clicked, activo:', $form.activo)}>
                    {#if $form.processing}
                        Guardando…
                    {:else if esEdicion}
                        Actualizar Usuario
                    {:else}
                        Crear Usuario
                    {/if}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
