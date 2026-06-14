<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import { untrack } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Checkbox } from '@/components/ui/checkbox';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Separator } from '@/components/ui/separator';
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
    } from '@/components/ui/select';
    import type { BreadcrumbItem } from '@/types';

    interface CategoriaData {
        id: number;
        nombre: string;
        descripcion: string | null;
        parent_id: number | null;
        activo: boolean;
    }

    interface CategoriaOption {
        id: number;
        nombre: string;
    }

    let {
        categoria,
        categorias,
    }: {
        categoria: CategoriaData | null;
        categorias: CategoriaOption[];
    } = $props();

    const isEditing = $derived(categoria !== null);

    const breadcrumbs = $derived([
        { title: 'Inventario', href: '/inventario/productos' },
        { title: 'Categorías', href: '/inventario/categorias' },
        { title: isEditing ? 'Editar Categoría' : 'Nueva Categoría', href: '#' },
    ]);

    const form = useForm(untrack(() => ({
        nombre:      categoria?.nombre      ?? '',
        descripcion: categoria?.descripcion ?? '',
        parent_id:   categoria?.parent_id   ? String(categoria.parent_id) : '',
        activo:      categoria?.activo      ?? true,
    })));

    const parentNombre = $derived(
        $form.parent_id
            ? (categorias.find(c => String(c.id) === $form.parent_id)?.nombre ?? 'Seleccionar...')
            : 'Sin categoría padre'
    );

    function handleSubmit(e: Event) {
        e.preventDefault();
        if (isEditing && categoria) {
            $form.put(`/inventario/categorias/${categoria.id}`, { preserveScroll: true });
        } else {
            $form.post('/inventario/categorias', { preserveScroll: true });
        }
    }
</script>

<AppHead title={isEditing ? 'Editar Categoría' : 'Nueva Categoría'} />

<AppLayout {breadcrumbs}>
    <div class="mx-auto max-w-2xl p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">
                {isEditing ? 'Editar Categoría' : 'Nueva Categoría'}
            </h1>
            <p class="text-sm text-muted-foreground">
                {isEditing
                    ? 'Modifica los datos de la categoría.'
                    : 'Completa los datos para registrar una nueva categoría de productos.'}
            </p>
        </div>

        <form onsubmit={handleSubmit} class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>Información de la categoría</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <!-- Nombre -->
                    <div class="space-y-1.5">
                        <Label for="nombre">Nombre <span class="text-destructive">*</span></Label>
                        <Input
                            id="nombre"
                            placeholder="Ej: Bebidas, Lácteos, Electrónicos..."
                            bind:value={$form.nombre}
                            required
                            class={$form.errors.nombre ? 'border-destructive' : ''}
                        />
                        <InputError message={$form.errors.nombre} />
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-1.5">
                        <Label for="descripcion">Descripción</Label>
                        <textarea
                            id="descripcion"
                            rows={3}
                            placeholder="Descripción opcional de la categoría..."
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:border-ring disabled:cursor-not-allowed disabled:opacity-50 {$form.errors.descripcion ? 'border-destructive' : ''}"
                            bind:value={$form.descripcion}
                        ></textarea>
                        <InputError message={$form.errors.descripcion} />
                    </div>

                    <!-- Categoría padre -->
                    <div class="space-y-1.5">
                        <Label for="parent_id">Categoría Padre</Label>
                        <Select
                            value={$form.parent_id}
                            onValueChange={(v) => ($form.parent_id = v)}
                        >
                            <SelectTrigger class="w-full {$form.errors.parent_id ? 'border-destructive' : ''}">
                                {parentNombre}
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="">Sin categoría padre</SelectItem>
                                {#each categorias as cat (cat.id)}
                                    <SelectItem value={String(cat.id)}>{cat.nombre}</SelectItem>
                                {/each}
                            </SelectContent>
                        </Select>
                        <p class="text-xs text-muted-foreground">
                            Deja en blanco para crear una categoría raíz.
                        </p>
                        <InputError message={$form.errors.parent_id} />
                    </div>
                </CardContent>
            </Card>

            <!-- Estado activo -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex items-center gap-3">
                        <Checkbox
                            id="activo"
                            checked={$form.activo}
                            onCheckedChange={(val) => ($form.activo = val === true)}
                        />
                        <div>
                            <Label for="activo" class="cursor-pointer font-medium">Categoría activa</Label>
                            <p class="text-xs text-muted-foreground">
                                Las categorías inactivas no aparecen al crear o editar productos.
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <Separator />

            <!-- Acciones -->
            <div class="flex items-center justify-end gap-3">
                <Button variant="outline" href="/inventario/categorias" type="button">
                    Cancelar
                </Button>
                <Button type="submit" disabled={$form.processing}>
                    {$form.processing
                        ? 'Guardando...'
                        : isEditing
                            ? 'Actualizar categoría'
                            : 'Crear categoría'}
                </Button>
            </div>
        </form>
    </div>
</AppLayout>
