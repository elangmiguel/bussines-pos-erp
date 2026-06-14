<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import { Truck } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Separator } from '@/components/ui/separator';
    import type { BreadcrumbItem } from '@/types';

    interface ProveedorData {
        id?: number;
        tipo: 'natural' | 'juridico';
        condiciones_pago: string | null;
        plazo_dias: number;
        activo: boolean;
        persona?: {
            tipo_identificacion: string;
            numero_identificacion: string;
            nombres: string;
            apellidos: string;
            email: string | null;
            telefono: string | null;
            celular: string | null;
            direccion: string | null;
            ciudad: string | null;
        } | null;
        empresa?: {
            razon_social: string;
            nit: string;
            digito_verificacion: string;
            regimen_tributario: string;
            email: string | null;
            telefono: string | null;
            direccion: string | null;
            ciudad: string | null;
        } | null;
    }

    interface TipoIdOption { value: string; label: string }

    let {
        proveedor,
        tiposIdentificacion,
        regimenesTributarios,
    }: {
        proveedor?: ProveedorData;
        tiposIdentificacion: TipoIdOption[];
        regimenesTributarios: TipoIdOption[];
    } = $props();

    const isEditing = $derived(!!proveedor?.id);

    const breadcrumbs = $derived([
        { title: 'Proveedores', href: '/proveedores' },
        { title: isEditing ? 'Editar Proveedor' : 'Nuevo Proveedor', href: '#' },
    ]);

    // Initialise form
    const form = useForm(untrack(() => ({
        tipo:                  proveedor?.tipo ?? 'natural',
        // Natural
        tipo_identificacion:   proveedor?.persona?.tipo_identificacion ?? 'CC',
        numero_identificacion: proveedor?.persona?.numero_identificacion ?? '',
        nombres:               proveedor?.persona?.nombres ?? '',
        apellidos:             proveedor?.persona?.apellidos ?? '',
        celular:               proveedor?.persona?.celular ?? '',
        // Juridico
        razon_social:          proveedor?.empresa?.razon_social ?? '',
        nit:                   proveedor?.empresa?.nit ?? '',
        digito_verificacion:   proveedor?.empresa?.digito_verificacion ?? '',
        regimen_tributario:    proveedor?.empresa?.regimen_tributario ?? 'no_responsable_iva',
        // Shared
        email:                 proveedor?.persona?.email ?? proveedor?.empresa?.email ?? '',
        telefono:              proveedor?.persona?.telefono ?? proveedor?.empresa?.telefono ?? '',
        direccion:             proveedor?.persona?.direccion ?? proveedor?.empresa?.direccion ?? '',
        ciudad:                proveedor?.persona?.ciudad ?? proveedor?.empresa?.ciudad ?? '',
        condiciones_pago:      proveedor?.condiciones_pago ?? '',
        plazo_dias:            proveedor?.plazo_dias ?? 0,
    })));

    function submit(e: Event) {
        e.preventDefault();
        if (isEditing && proveedor?.id) {
            $form.put(`/proveedores/${proveedor.id}`);
        } else {
            $form.post('/proveedores');
        }
    }
</script>

<AppHead title={isEditing ? 'Editar Proveedor' : 'Nuevo Proveedor'} />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-6 p-6 max-w-3xl mx-auto">
        <div class="flex items-center gap-2">
            <Truck class="h-6 w-6 text-primary" />
            <h1 class="text-2xl font-bold">
                {isEditing ? 'Editar Proveedor' : 'Nuevo Proveedor'}
            </h1>
        </div>

        <form onsubmit={submit} class="flex flex-col gap-6">
            <!-- Tipo de proveedor -->
            <Card>
                <CardHeader>
                    <CardTitle>Tipo de Proveedor</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                name="tipo"
                                value="natural"
                                bind:group={$form.tipo}
                                class="accent-primary"
                            />
                            <span class="text-sm font-medium">Persona Natural</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                name="tipo"
                                value="juridico"
                                bind:group={$form.tipo}
                                class="accent-primary"
                            />
                            <span class="text-sm font-medium">Persona Jurídica</span>
                        </label>
                    </div>
                    <InputError message={$form.errors.tipo} class="mt-1" />
                </CardContent>
            </Card>

            <!-- Natural fields -->
            {#if $form.tipo === 'natural'}
                <Card>
                    <CardHeader>
                        <CardTitle>Datos de la Persona</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="tipo_identificacion">Tipo de identificación</Label>
                                <select
                                    id="tipo_identificacion"
                                    bind:value={$form.tipo_identificacion}
                                    class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                    {#each tiposIdentificacion as opt (opt.value)}
                                        <option value={opt.value}>{opt.label}</option>
                                    {/each}
                                </select>
                                <InputError message={$form.errors.tipo_identificacion} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="numero_identificacion">Número de identificación</Label>
                                <Input
                                    id="numero_identificacion"
                                    bind:value={$form.numero_identificacion}
                                    placeholder="Ej. 1234567890"
                                />
                                <InputError message={$form.errors.numero_identificacion} />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="nombres">Nombres</Label>
                                <Input
                                    id="nombres"
                                    bind:value={$form.nombres}
                                    placeholder="Nombres"
                                />
                                <InputError message={$form.errors.nombres} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="apellidos">Apellidos</Label>
                                <Input
                                    id="apellidos"
                                    bind:value={$form.apellidos}
                                    placeholder="Apellidos"
                                />
                                <InputError message={$form.errors.apellidos} />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="celular">Celular</Label>
                                <Input
                                    id="celular"
                                    bind:value={$form.celular}
                                    placeholder="Ej. 3001234567"
                                />
                                <InputError message={$form.errors.celular} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="telefono">Teléfono</Label>
                                <Input
                                    id="telefono"
                                    bind:value={$form.telefono}
                                    placeholder="Ej. 6012345678"
                                />
                                <InputError message={$form.errors.telefono} />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Juridico fields -->
            {#if $form.tipo === 'juridico'}
                <Card>
                    <CardHeader>
                        <CardTitle>Datos de la Empresa</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-4">
                        <div class="grid gap-2">
                            <Label for="razon_social">Razón social</Label>
                            <Input
                                id="razon_social"
                                bind:value={$form.razon_social}
                                placeholder="Nombre completo de la empresa"
                            />
                            <InputError message={$form.errors.razon_social} />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="grid gap-2">
                                <Label for="nit">NIT</Label>
                                <Input
                                    id="nit"
                                    bind:value={$form.nit}
                                    placeholder="Ej. 900123456"
                                />
                                <InputError message={$form.errors.nit} />
                            </div>
                            <div class="grid gap-2">
                                <Label for="digito_verificacion">Dígito de verificación</Label>
                                <Input
                                    id="digito_verificacion"
                                    bind:value={$form.digito_verificacion}
                                    placeholder="Ej. 1"
                                    maxlength={2}
                                />
                                <InputError message={$form.errors.digito_verificacion} />
                            </div>
                        </div>

                        <div class="grid gap-2">
                            <Label for="regimen_tributario">Régimen tributario</Label>
                            <select
                                id="regimen_tributario"
                                bind:value={$form.regimen_tributario}
                                class="h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                                {#each regimenesTributarios as opt (opt.value)}
                                    <option value={opt.value}>{opt.label}</option>
                                {/each}
                            </select>
                            <InputError message={$form.errors.regimen_tributario} />
                        </div>

                        <div class="grid gap-2">
                            <Label for="telefono_emp">Teléfono</Label>
                            <Input
                                id="telefono_emp"
                                bind:value={$form.telefono}
                                placeholder="Teléfono principal"
                            />
                            <InputError message={$form.errors.telefono} />
                        </div>
                    </CardContent>
                </Card>
            {/if}

            <!-- Shared contact data -->
            <Card>
                <CardHeader>
                    <CardTitle>Información de Contacto</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="email">Correo electrónico</Label>
                        <Input
                            id="email"
                            type="email"
                            bind:value={$form.email}
                            placeholder="correo@ejemplo.com"
                        />
                        <InputError message={$form.errors.email} />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="grid gap-2">
                            <Label for="direccion">Dirección</Label>
                            <Input
                                id="direccion"
                                bind:value={$form.direccion}
                                placeholder="Dirección completa"
                            />
                            <InputError message={$form.errors.direccion} />
                        </div>
                        <div class="grid gap-2">
                            <Label for="ciudad">Ciudad</Label>
                            <Input
                                id="ciudad"
                                bind:value={$form.ciudad}
                                placeholder="Ciudad"
                            />
                            <InputError message={$form.errors.ciudad} />
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Commercial conditions -->
            <Card>
                <CardHeader>
                    <CardTitle>Condiciones Comerciales</CardTitle>
                </CardHeader>
                <CardContent class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="condiciones_pago">Condiciones de pago</Label>
                        <textarea
                            id="condiciones_pago"
                            bind:value={$form.condiciones_pago}
                            rows={3}
                            placeholder="Describa las condiciones de pago acordadas con el proveedor..."
                            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 resize-none"
                        ></textarea>
                        <InputError message={$form.errors.condiciones_pago} />
                    </div>

                    <div class="grid gap-2 max-w-[200px]">
                        <Label for="plazo_dias">Plazo de pago (días)</Label>
                        <Input
                            id="plazo_dias"
                            type="number"
                            min={0}
                            bind:value={$form.plazo_dias}
                            placeholder="0"
                        />
                        <InputError message={$form.errors.plazo_dias} />
                    </div>
                </CardContent>
            </Card>

            <Separator />

            <div class="flex gap-3 justify-end">
                <Button variant="outline" href="/proveedores" type="button">Cancelar</Button>
                <Button type="submit" disabled={$form.processing}>
                    {$form.processing ? 'Guardando...' : isEditing ? 'Actualizar Proveedor' : 'Crear Proveedor'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
