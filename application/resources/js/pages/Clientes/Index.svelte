<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import {
        Search,
        Plus,
        Edit,
        Trash2,
        User,
        Building2,
        CreditCard,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import type { BreadcrumbItem } from '@/types';

    interface Persona {
        id: number;
        nombres: string;
        apellidos: string | null;
        numero_identificacion: string;
        tipo_identificacion: string;
    }

    interface Empresa {
        id: number;
        razon_social: string;
        nit: string;
        digito_verificacion: string;
    }

    interface Cliente {
        id: number;
        tipo: 'natural' | 'juridico';
        tipo_cliente: 'regular' | 'frecuente' | 'corporativo';
        credito_activo: boolean;
        limite_credito: number;
        activo: boolean;
        nombre: string;
        identificacion: string;
        persona: Persona | null;
        empresa: Empresa | null;
    }

    interface PaginatedClientes {
        data: Cliente[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
        meta: {
            current_page: number;
            last_page: number;
            total: number;
            from: number;
            to: number;
        };
    }

    interface Filtros {
        search?: string;
        tipo?: string;
        tipo_cliente?: string;
        activo?: string;
        credito_activo?: string;
    }

    let {
        clientes,
        filtros,
    }: {
        clientes: PaginatedClientes;
        filtros: Filtros;
    } = $props();

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clientes', href: '/clientes' },
    ];

    let search = $state(untrack(() => filtros.search ?? ''));
    let tipoFiltro = $state(untrack(() => filtros.tipo ?? ''));
    let tipoClienteFiltro = $state(untrack(() => filtros.tipo_cliente ?? ''));
    let creditoActivoFiltro = $state(untrack(() => filtros.credito_activo ?? ''));

    const formatCOP = (value: number) =>
        new Intl.NumberFormat('es-CO', {
            style: 'currency',
            currency: 'COP',
            minimumFractionDigits: 0,
        }).format(value);

    function applyFilters() {
        const params: Record<string, string> = {};
        if (search) params.search = search;
        if (tipoFiltro) params.tipo = tipoFiltro;
        if (tipoClienteFiltro) params.tipo_cliente = tipoClienteFiltro;
        if (creditoActivoFiltro) params.credito_activo = creditoActivoFiltro;
        router.get('/clientes', params, { preserveState: true, replace: true });
    }

    function clearFilters() {
        search = '';
        tipoFiltro = '';
        tipoClienteFiltro = '';
        creditoActivoFiltro = '';
        router.get('/clientes', {}, { preserveState: true, replace: true });
    }

    function handleSearchKeydown(e: KeyboardEvent) {
        if (e.key === 'Enter') applyFilters();
    }

    function confirmDelete(cliente: Cliente) {
        if (confirm(`¿Eliminar al cliente "${cliente.nombre}"? Esta acción no se puede deshacer.`)) {
            router.delete(`/clientes/${cliente.id}`);
        }
    }

    function tipoClienteLabel(tc: string): string {
        const labels: Record<string, string> = {
            regular: 'Regular',
            frecuente: 'Frecuente',
            corporativo: 'Corporativo',
        };
        return labels[tc] ?? tc;
    }

    function tipoClienteVariant(tc: string): 'default' | 'secondary' | 'outline' {
        if (tc === 'corporativo') return 'default';
        if (tc === 'frecuente') return 'secondary';
        return 'outline';
    }
</script>

<AppHead title="Clientes" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Clientes</h1>
                <p class="text-muted-foreground text-sm">
                    Gestión de clientes naturales y jurídicos
                </p>
            </div>
            <Button href="/clientes/create">
                <Plus class="mr-2 size-4" />
                Nuevo Cliente
            </Button>
        </div>

        <!-- Filtros -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-base">Filtros de búsqueda</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap gap-3">
                    <div class="relative min-w-64 flex-1">
                        <Search class="text-muted-foreground absolute left-2.5 top-2.5 size-4" />
                        <Input
                            class="pl-8"
                            placeholder="Buscar por nombre o identificación..."
                            bind:value={search}
                            onkeydown={handleSearchKeydown}
                        />
                    </div>

                    <Select bind:value={tipoFiltro} onValueChange={applyFilters}>
                        <SelectTrigger class="w-40">
                            {tipoFiltro
                                ? tipoFiltro === 'natural'
                                    ? 'Persona Natural'
                                    : 'Persona Jurídica'
                                : 'Tipo'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todos los tipos</SelectItem>
                            <SelectItem value="natural">Persona Natural</SelectItem>
                            <SelectItem value="juridico">Persona Jurídica</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select bind:value={tipoClienteFiltro} onValueChange={applyFilters}>
                        <SelectTrigger class="w-40">
                            {tipoClienteFiltro
                                ? tipoClienteLabel(tipoClienteFiltro)
                                : 'Categoría'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todas las categorías</SelectItem>
                            <SelectItem value="regular">Regular</SelectItem>
                            <SelectItem value="frecuente">Frecuente</SelectItem>
                            <SelectItem value="corporativo">Corporativo</SelectItem>
                        </SelectContent>
                    </Select>

                    <Select bind:value={creditoActivoFiltro} onValueChange={applyFilters}>
                        <SelectTrigger class="w-36">
                            {creditoActivoFiltro === '1'
                                ? 'Con crédito'
                                : creditoActivoFiltro === '0'
                                  ? 'Sin crédito'
                                  : 'Crédito'}
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">Todos</SelectItem>
                            <SelectItem value="1">Con crédito</SelectItem>
                            <SelectItem value="0">Sin crédito</SelectItem>
                        </SelectContent>
                    </Select>

                    <Button onclick={applyFilters}>
                        <Search class="mr-2 size-4" />
                        Buscar
                    </Button>

                    <Button variant="outline" onclick={clearFilters}>
                        Limpiar
                    </Button>
                </div>
            </CardContent>
        </Card>

        <!-- Tabla -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Identificación</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Nombre / Razón Social</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Tipo</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Categoría</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Crédito</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Límite</th>
                                <th class="px-4 py-3 text-left font-medium text-muted-foreground">Estado</th>
                                <th class="px-4 py-3 text-right font-medium text-muted-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            {#each clientes.data as cliente (cliente.id)}
                                <tr class="hover:bg-muted/30 transition-colors">
                                    <td class="px-4 py-3 font-mono text-xs text-muted-foreground">
                                        {cliente.identificacion ?? '—'}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            {#if cliente.tipo === 'natural'}
                                                <User class="size-4 text-muted-foreground shrink-0" />
                                            {:else}
                                                <Building2 class="size-4 text-muted-foreground shrink-0" />
                                            {/if}
                                            <span class="font-medium">{cliente.nombre ?? '—'}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 capitalize text-muted-foreground">
                                        {cliente.tipo === 'natural' ? 'Natural' : 'Jurídico'}
                                    </td>
                                    <td class="px-4 py-3">
                                        <Badge variant={tipoClienteVariant(cliente.tipo_cliente)}>
                                            {tipoClienteLabel(cliente.tipo_cliente)}
                                        </Badge>
                                    </td>
                                    <td class="px-4 py-3">
                                        {#if cliente.credito_activo}
                                            <Badge variant="default">
                                                <CreditCard class="mr-1 size-3" />
                                                Activo
                                            </Badge>
                                        {:else}
                                            <Badge variant="outline">Sin crédito</Badge>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        {#if cliente.credito_activo}
                                            <span class="font-medium">{formatCOP(cliente.limite_credito)}</span>
                                        {:else}
                                            <span class="text-muted-foreground">—</span>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        {#if cliente.activo}
                                            <Badge variant="default" class="bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 border-green-200">Activo</Badge>
                                        {:else}
                                            <Badge variant="secondary">Inactivo</Badge>
                                        {/if}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-1">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/clientes/${cliente.id}`}
                                                title="Ver cliente"
                                            >
                                                <User class="size-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                href={`/clientes/${cliente.id}/edit`}
                                                title="Editar cliente"
                                            >
                                                <Edit class="size-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                onclick={() => confirmDelete(cliente)}
                                                title="Eliminar cliente"
                                                class="text-destructive hover:text-destructive"
                                            >
                                                <Trash2 class="size-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-muted-foreground">
                                        No se encontraron clientes con los filtros aplicados.
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                {#if clientes.meta && clientes.meta.last_page > 1}
                    <div class="flex items-center justify-between border-t px-4 py-3">
                        <p class="text-sm text-muted-foreground">
                            Mostrando {clientes.meta.from} – {clientes.meta.to} de {clientes.meta.total} clientes
                        </p>
                        <div class="flex gap-1">
                            {#each clientes.links as link, i (i)}
                                {#if link.url}
                                    <Button
                                        variant={link.active ? 'default' : 'outline'}
                                        size="sm"
                                        onclick={() => router.visit(link.url!)}
                                    >
                                        <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                                        {@html link.label}
                                    </Button>
                                {:else}
                                    <Button variant="outline" size="sm" disabled>
                                        <!-- eslint-disable-next-line svelte/no-at-html-tags -->
                                        {@html link.label}
                                    </Button>
                                {/if}
                            {/each}
                        </div>
                    </div>
                {/if}
            </CardContent>
        </Card>
    </div>
</AppLayout>
