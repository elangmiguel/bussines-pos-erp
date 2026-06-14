<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { User, Building2 } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
        CardDescription,
    } from '@/components/ui/card';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import { Separator } from '@/components/ui/separator';
    import { Checkbox } from '@/components/ui/checkbox';
    import type { BreadcrumbItem } from '@/types';

    interface Persona {
        id: number;
        tipo_identificacion: string;
        numero_identificacion: string;
        nombres: string;
        apellidos: string | null;
        email: string | null;
        telefono: string | null;
        celular: string | null;
        direccion: string | null;
        ciudad: string | null;
    }

    interface Empresa {
        id: number;
        razon_social: string;
        nit: string;
        digito_verificacion: string;
        regimen_tributario: string;
        email: string | null;
        telefono: string | null;
        direccion: string | null;
        ciudad: string | null;
    }

    interface Cliente {
        id: number;
        tipo: 'natural' | 'juridico';
        tipo_cliente: 'regular' | 'frecuente' | 'corporativo';
        credito_activo: boolean;
        limite_credito: number;
        plazo_dias: number;
        observaciones: string | null;
        persona: Persona | null;
        empresa: Empresa | null;
    }

    interface Lookups {
        tipos_identificacion: string[];
        tipos_cliente: string[];
        regimenes: string[];
    }

    let {
        cliente,
        lookups,
    }: {
        cliente: Cliente | null;
        lookups: Lookups;
    } = $props();

    const isEditing = $derived(cliente !== null);

    const breadcrumbs = $derived([
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Clientes', href: '/clientes' },
        {
            title: isEditing ? 'Editar Cliente' : 'Nuevo Cliente',
            href: isEditing ? `/clientes/${cliente?.id}/edit` : '/clientes/create',
        },
    ]);

    const form = useForm(untrack(() => ({
        tipo: (cliente?.tipo ?? 'natural') as 'natural' | 'juridico',
        // Natural
        tipo_identificacion: cliente?.persona?.tipo_identificacion ?? 'CC',
        numero_identificacion: cliente?.persona?.numero_identificacion ?? '',
        nombres: cliente?.persona?.nombres ?? '',
        apellidos: cliente?.persona?.apellidos ?? '',
        email: (cliente?.persona?.email ?? cliente?.empresa?.email) ?? '',
        telefono: (cliente?.persona?.telefono ?? cliente?.empresa?.telefono) ?? '',
        celular: cliente?.persona?.celular ?? '',
        direccion: (cliente?.persona?.direccion ?? cliente?.empresa?.direccion) ?? '',
        ciudad: (cliente?.persona?.ciudad ?? cliente?.empresa?.ciudad) ?? '',
        // Juridico
        razon_social: cliente?.empresa?.razon_social ?? '',
        nit: cliente?.empresa?.nit ?? '',
        digito_verificacion: cliente?.empresa?.digito_verificacion ?? '',
        regimen_tributario: cliente?.empresa?.regimen_tributario ?? 'no_responsable_iva',
        // Comunes
        tipo_cliente: cliente?.tipo_cliente ?? 'regular',
        credito_activo: cliente?.credito_activo ?? false,
        limite_credito: cliente?.limite_credito ?? 0,
        plazo_dias: cliente?.plazo_dias ?? 0,
        observaciones: cliente?.observaciones ?? '',
    })));

    function submit() {
        if (isEditing) {
            $form.put(`/clientes/${cliente!.id}`, {
                preserveScroll: true,
            });
        } else {
            $form.post('/clientes', {
                preserveScroll: true,
            });
        }
    }

    function tipoLabel(tipo: string): string {
        const labels: Record<string, string> = {
            CC: 'Cédula de Ciudadanía',
            CE: 'Cédula de Extranjería',
            TI: 'Tarjeta de Identidad',
            PAS: 'Pasaporte',
            RC: 'Registro Civil',
        };
        return labels[tipo] ?? tipo;
    }

    function regimenLabel(r: string): string {
        return r === 'responsable_iva' ? 'Responsable de IVA' : 'No responsable de IVA';
    }

    function tipoClienteLabel(tc: string): string {
        const labels: Record<string, string> = {
            regular: 'Regular',
            frecuente: 'Frecuente',
            corporativo: 'Corporativo',
        };
        return labels[tc] ?? tc;
    }
</script>

<AppHead title={isEditing ? 'Editar Cliente' : 'Nuevo Cliente'} />

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-3xl p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight">
                {isEditing ? 'Editar Cliente' : 'Nuevo Cliente'}
            </h1>
            <p class="text-muted-foreground text-sm">
                {isEditing
                    ? 'Actualice la información del cliente'
                    : 'Complete la información para registrar un nuevo cliente'}
            </p>
        </div>

        <form onsubmit={(e) => { e.preventDefault(); submit(); }} class="space-y-6">

            <!-- Tipo de persona -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Tipo de Persona</CardTitle>
                    <CardDescription>Seleccione si el cliente es una persona natural o jurídica</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            onclick={() => ($form.tipo = 'natural')}
                            class={[
                                'flex flex-1 cursor-pointer flex-col items-center gap-2 rounded-lg border-2 p-4 transition-all',
                                $form.tipo === 'natural'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-muted-foreground/50',
                            ].join(' ')}
                        >
                            <User class="size-8 text-muted-foreground" />
                            <div class="text-center">
                                <p class="font-medium">Persona Natural</p>
                                <p class="text-xs text-muted-foreground">CC, CE, TI, PAS, RC</p>
                            </div>
                        </button>

                        <button
                            type="button"
                            onclick={() => ($form.tipo = 'juridico')}
                            class={[
                                'flex flex-1 cursor-pointer flex-col items-center gap-2 rounded-lg border-2 p-4 transition-all',
                                $form.tipo === 'juridico'
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border hover:border-muted-foreground/50',
                            ].join(' ')}
                        >
                            <Building2 class="size-8 text-muted-foreground" />
                            <div class="text-center">
                                <p class="font-medium">Persona Jurídica</p>
                                <p class="text-xs text-muted-foreground">Empresa / NIT</p>
                            </div>
                        </button>
                    </div>
                    <InputError message={$form.errors.tipo} class="mt-2" />
                </CardContent>
            </Card>

            <!-- Datos Natural -->
            {#if $form.tipo === 'natural'}
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Datos Personales</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label for="tipo_identificacion">Tipo de Identificación *</Label>
                                <Select
                                    bind:value={$form.tipo_identificacion}
                                    onValueChange={(v) => ($form.tipo_identificacion = v)}
                                >
                                    <SelectTrigger id="tipo_identificacion" class="w-full">
                                        {tipoLabel($form.tipo_identificacion)}
                                    </SelectTrigger>
                                    <SelectContent>
                                        {#each lookups.tipos_identificacion as tipo (tipo)}
                                            <SelectItem value={tipo}>{tipoLabel(tipo)}</SelectItem>
                                        {/each}
                                    </SelectContent>
                                </Select>
                                <InputError message={$form.errors.tipo_identificacion} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="numero_identificacion">Número de Identificación *</Label>
                                <Input
                                    id="numero_identificacion"
                                    bind:value={$form.numero_identificacion}
                                    placeholder="Ej: 1234567890"
                                    maxlength={20}
                                />
                                <InputError message={$form.errors.numero_identificacion} />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label for="nombres">Nombres *</Label>
                                <Input
                                    id="nombres"
                                    bind:value={$form.nombres}
                                    placeholder="Nombres completos"
                                    maxlength={100}
                                />
                                <InputError message={$form.errors.nombres} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="apellidos">Apellidos</Label>
                                <Input
                                    id="apellidos"
                                    bind:value={$form.apellidos}
                                    placeholder="Apellidos"
                                    maxlength={100}
                                />
                                <InputError message={$form.errors.apellidos} />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label for="email">Correo Electrónico</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    bind:value={$form.email}
                                    placeholder="correo@ejemplo.com"
                                />
                                <InputError message={$form.errors.email} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="celular">Celular</Label>
                                <Input
                                    id="celular"
                                    bind:value={$form.celular}
                                    placeholder="3XX XXX XXXX"
                                />
                                <InputError message={$form.errors.celular} />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label for="telefono">Teléfono</Label>
                                <Input
                                    id="telefono"
                                    bind:value={$form.telefono}
                                    placeholder="6XX XXX XXXX"
                                />
                                <InputError message={$form.errors.telefono} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="ciudad">Ciudad</Label>
                                <Input
                                    id="ciudad"
                                    bind:value={$form.ciudad}
                                    placeholder="Bogotá"
                                />
                                <InputError message={$form.errors.ciudad} />
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="direccion">Dirección</Label>
                            <Input
                                id="direccion"
                                bind:value={$form.direccion}
                                placeholder="Calle 123 # 45-67"
                            />
                            <InputError message={$form.errors.direccion} />
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Datos Jurídico -->
            {#if $form.tipo === 'juridico'}
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Datos de la Empresa</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-1.5">
                            <Label for="razon_social">Razón Social *</Label>
                            <Input
                                id="razon_social"
                                bind:value={$form.razon_social}
                                placeholder="Nombre legal de la empresa"
                                maxlength={200}
                            />
                            <InputError message={$form.errors.razon_social} />
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2 grid gap-1.5">
                                <Label for="nit">NIT *</Label>
                                <Input
                                    id="nit"
                                    bind:value={$form.nit}
                                    placeholder="900123456"
                                    maxlength={15}
                                />
                                <InputError message={$form.errors.nit} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="digito_verificacion">Dígito *</Label>
                                <Input
                                    id="digito_verificacion"
                                    bind:value={$form.digito_verificacion}
                                    placeholder="0"
                                    maxlength={1}
                                    class="text-center"
                                />
                                <InputError message={$form.errors.digito_verificacion} />
                            </div>
                        </div>

                        <div class="grid gap-1.5">
                            <Label for="regimen_tributario">Régimen Tributario *</Label>
                            <Select
                                bind:value={$form.regimen_tributario}
                                onValueChange={(v) => ($form.regimen_tributario = v)}
                            >
                                <SelectTrigger id="regimen_tributario" class="w-full">
                                    {regimenLabel($form.regimen_tributario)}
                                </SelectTrigger>
                                <SelectContent>
                                    {#each lookups.regimenes as r (r)}
                                        <SelectItem value={r}>{regimenLabel(r)}</SelectItem>
                                    {/each}
                                </SelectContent>
                            </Select>
                            <InputError message={$form.errors.regimen_tributario} />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label for="email_empresa">Correo Electrónico</Label>
                                <Input
                                    id="email_empresa"
                                    type="email"
                                    bind:value={$form.email}
                                    placeholder="contacto@empresa.com"
                                />
                                <InputError message={$form.errors.email} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="telefono_empresa">Teléfono</Label>
                                <Input
                                    id="telefono_empresa"
                                    bind:value={$form.telefono}
                                    placeholder="6XX XXX XXXX"
                                />
                                <InputError message={$form.errors.telefono} />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-1.5">
                                <Label for="ciudad_empresa">Ciudad</Label>
                                <Input
                                    id="ciudad_empresa"
                                    bind:value={$form.ciudad}
                                    placeholder="Bogotá"
                                />
                                <InputError message={$form.errors.ciudad} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="direccion_empresa">Dirección</Label>
                                <Input
                                    id="direccion_empresa"
                                    bind:value={$form.direccion}
                                    placeholder="Cra 7 # 12-34"
                                />
                                <InputError message={$form.errors.direccion} />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Categoría y Crédito -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Condiciones Comerciales</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-1.5">
                        <Label for="tipo_cliente">Categoría del Cliente *</Label>
                        <Select
                            bind:value={$form.tipo_cliente}
                            onValueChange={(v) => ($form.tipo_cliente = v as 'regular' | 'frecuente' | 'corporativo')}
                        >
                            <SelectTrigger id="tipo_cliente" class="w-full">
                                {tipoClienteLabel($form.tipo_cliente)}
                            </SelectTrigger>
                            <SelectContent>
                                {#each lookups.tipos_cliente as tc (tc)}
                                    <SelectItem value={tc}>{tipoClienteLabel(tc)}</SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                        <InputError message={$form.errors.tipo_cliente} />
                    </div>

                    <Separator />

                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="credito_activo"
                            checked={$form.credito_activo}
                            onCheckedChange={(v) => ($form.credito_activo = !!v)}
                        />
                        <div>
                            <Label for="credito_activo" class="cursor-pointer font-medium">
                                Habilitar línea de crédito
                            </Label>
                            <p class="text-xs text-muted-foreground">
                                Permite al cliente comprar a crédito con un límite establecido
                            </p>
                        </div>
                    </div>

                    {#if $form.credito_activo}
                        <div class="ml-7 grid grid-cols-2 gap-4 rounded-lg bg-muted/40 p-4">
                            <div class="grid gap-1.5">
                                <Label for="limite_credito">Límite de Crédito (COP) *</Label>
                                <Input
                                    id="limite_credito"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    bind:value={$form.limite_credito}
                                    placeholder="1000000"
                                />
                                <InputError message={$form.errors.limite_credito} />
                            </div>

                            <div class="grid gap-1.5">
                                <Label for="plazo_dias">Plazo en Días *</Label>
                                <Input
                                    id="plazo_dias"
                                    type="number"
                                    min="0"
                                    bind:value={$form.plazo_dias}
                                    placeholder="30"
                                />
                                <InputError message={$form.errors.plazo_dias} />
                            </div>
                        </div>
                    {/if}
                </CardContent>
            </Card>

            <!-- Observaciones -->
            <Card>
                <CardHeader>
                    <CardTitle class="text-base">Observaciones</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-1.5">
                        <Label for="observaciones">Notas adicionales</Label>
                        <textarea
                            id="observaciones"
                            bind:value={$form.observaciones}
                            rows={3}
                            placeholder="Notas o comentarios sobre el cliente..."
                            class="border-input placeholder:text-muted-foreground focus-visible:ring-ring/50 flex w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] disabled:cursor-not-allowed disabled:opacity-50"
                        ></textarea>
                        <InputError message={$form.errors.observaciones} />
                    </div>
                </CardContent>
            </Card>

            <!-- Acciones -->
            <div class="flex items-center justify-end gap-3">
                <Button variant="outline" href="/clientes" type="button">
                    Cancelar
                </Button>
                <Button type="submit" disabled={$form.processing}>
                    {$form.processing
                        ? 'Guardando...'
                        : isEditing
                          ? 'Actualizar Cliente'
                          : 'Crear Cliente'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
