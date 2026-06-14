<script lang="ts">
    import { router } from '@inertiajs/svelte'
    import { Plus, Edit, UserX, Users } from 'lucide-svelte'
    import AppHead from '@/components/AppHead.svelte'
    import AppLayout from '@/layouts/AppLayout.svelte'
    import { Badge } from '@/components/ui/badge'
    import { Button } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import type { BreadcrumbItem } from '@/types'
    import type { Paginated, User } from '@/types/models'
    import { formatFechaHora } from '@/lib/currency'

    let {
        usuarios,
    }: {
        usuarios: Paginated<User>
    } = $props()

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Configuración', href: '/settings/empresa' },
        { title: 'Usuarios', href: '/settings/usuarios' },
    ]

    type RolNombre = 'administrador' | 'vendedor' | 'cajero' | 'bodeguero'

    function rolVariant(nombre: RolNombre | undefined): string {
        switch (nombre) {
            case 'administrador': return 'destructive'
            case 'vendedor':      return 'default'
            case 'cajero':        return 'secondary'
            case 'bodeguero':     return 'outline'
            default:              return 'outline'
        }
    }

    function rolLabel(nombre: RolNombre | undefined): string {
        switch (nombre) {
            case 'administrador': return 'Administrador'
            case 'vendedor':      return 'Vendedor'
            case 'cajero':        return 'Cajero'
            case 'bodeguero':     return 'Bodeguero'
            default:              return nombre ?? 'Sin rol'
        }
    }

    function confirmarDesactivar(usuario: User) {
        if (confirm(`¿Desactivar al usuario "${usuario.display_name}"? El usuario no podrá iniciar sesión.`)) {
            router.delete(`/settings/usuarios/${usuario.id}`, { preserveScroll: true })
        }
    }

    function goToPage(url: string | null) {
        if (url) router.get(url, {}, { preserveState: true, replace: true })
    }
</script>

<AppHead title="Usuarios" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <Users class="text-primary size-6" />
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Usuarios del Sistema</h1>
                    <p class="text-muted-foreground text-sm">
                        Gestiona los accesos y roles de los colaboradores
                    </p>
                </div>
            </div>
            <Button href="/settings/usuarios/create">
                <Plus class="mr-2 size-4" />
                Nuevo Usuario
            </Button>
        </div>  

        <!-- Table -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">
                    {usuarios.total} Usuario{usuarios.total !== 1 ? 's' : ''} registrado{usuarios.total !== 1 ? 's' : ''}
                </CardTitle>
            </CardHeader>
            <CardContent class="p-0">
                {#if usuarios.data.length === 0}
                    <div class="py-12 text-center">
                        <Users class="text-muted-foreground mx-auto mb-3 size-12 opacity-40" />
                        <p class="text-muted-foreground">No hay usuarios registrados.</p>
                        <Button variant="outline" class="mt-4" href="/settings/usuarios/create">
                            Crear el primer usuario
                        </Button>
                    </div>
                {:else}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b bg-muted/50">
                                    <th class="px-4 py-3 text-left font-medium">Nombre</th>
                                    <th class="px-4 py-3 text-left font-medium">Correo</th>
                                    <th class="px-4 py-3 text-left font-medium">Rol</th>
                                    <th class="px-4 py-3 text-left font-medium">Último acceso</th>
                                    <th class="px-4 py-3 text-left font-medium">Estado</th>
                                    <th class="px-4 py-3 text-right font-medium">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each usuarios.data as usuario (usuario.id)}
                                    <tr class="border-b transition-colors hover:bg-muted/30">
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{usuario.display_name}</div>
                                            {#if usuario.persona}
                                                <div class="text-muted-foreground text-xs">
                                                    {usuario.persona.tipo_identificacion}
                                                    {usuario.persona.numero_identificacion}
                                                </div>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {usuario.email}
                                        </td>
                                        <td class="px-4 py-3">
                                            <Badge
                                                variant={rolVariant(usuario.rol?.nombre) as any}
                                                class={usuario.rol?.nombre === 'vendedor'
                                                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                                                    : usuario.rol?.nombre === 'cajero'
                                                      ? 'bg-green-600 text-white hover:bg-green-700'
                                                      : usuario.rol?.nombre === 'bodeguero'
                                                        ? 'bg-amber-500 text-white hover:bg-amber-600'
                                                        : ''}
                                            >
                                                {rolLabel(usuario.rol?.nombre)}
                                            </Badge>
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {usuario.ultimo_acceso
                                                ? formatFechaHora(usuario.ultimo_acceso)
                                                : 'Nunca'}
                                        </td>
                                        <td class="px-4 py-3">
                                            {#if usuario.activo}
                                                <Badge variant="secondary" class="bg-green-100 text-green-800">
                                                    Activo
                                                </Badge>
                                            {:else}
                                                <Badge variant="outline" class="text-muted-foreground">
                                                    Inactivo
                                                </Badge>
                                            {/if}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/settings/usuarios/${usuario.id}/edit`}
                                                >
                                                    <Edit class="size-4" />
                                                    <span class="sr-only">Editar</span>
                                                </Button>
                                                {#if usuario.activo}
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        class="text-destructive hover:text-destructive"
                                                        onclick={() => confirmarDesactivar(usuario)}
                                                    >
                                                        <UserX class="size-4" />
                                                        <span class="sr-only">Desactivar</span>
                                                    </Button>
                                                {/if}
                                            </div>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {#if usuarios.last_page > 1}
                        <div class="flex items-center justify-between border-t px-4 py-3">
                            <p class="text-muted-foreground text-sm">
                                Página {usuarios.current_page} de {usuarios.last_page}
                                · {usuarios.total} usuarios en total
                            </p>
                            <div class="flex gap-1">
                                {#each usuarios.links as link, i (i)}
                                    <button
                                        class="rounded border px-3 py-1 text-sm transition-colors
                                            {link.active
                                                ? 'bg-primary text-primary-foreground border-primary'
                                                : link.url
                                                  ? 'hover:bg-muted border-border'
                                                  : 'cursor-not-allowed border-border text-muted-foreground opacity-50'}
                                        "
                                        disabled={!link.url}
                                        onclick={() => goToPage(link.url)}
                                    >
                                        {@html link.label}
                                    </button>
                                {/each}
                            </div>
                        </div>
                    {/if}
                {/if}
            </CardContent>
        </Card>
    </div>
</AppLayout>
