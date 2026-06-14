<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Models\CategoriaProducto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(): Response
    {
        $categorias = CategoriaProducto::withCount('productos')
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('nombre')
            ->get();

        $todasCategorias = CategoriaProducto::orderBy('nombre')->get();

        return Inertia::render('Inventario/Categorias/Index', [
            'categorias'      => $categorias,
            'todasCategorias' => $todasCategorias,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inventario/Categorias/Form', [
            'categoria'  => null,
            'categorias' => CategoriaProducto::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'parent_id'   => ['nullable', 'integer', 'exists:categorias_producto,id'],
            'activo'      => ['boolean'],
        ], [
            'nombre.required' => 'El nombre de la categoría es obligatorio.',
            'nombre.max'      => 'El nombre no puede superar los 150 caracteres.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        CategoriaProducto::create($validated);

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría creada correctamente.');
    }

    public function show(CategoriaProducto $categoria): Response
    {
        $categoria->load(['parent', 'children', 'productos']);

        return Inertia::render('Inventario/Categorias/Show', [
            'categoria' => $categoria,
        ]);
    }

    public function edit(CategoriaProducto $categoria): Response
    {
        return Inertia::render('Inventario/Categorias/Form', [
            'categoria'  => $categoria,
            'categorias' => CategoriaProducto::where('id', '!=', $categoria->id)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function update(Request $request, CategoriaProducto $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'      => ['required', 'string', 'max:150'],
            'descripcion' => ['nullable', 'string', 'max:500'],
            'parent_id'   => [
                'nullable',
                'integer',
                'exists:categorias_producto,id',
                function ($attribute, $value, $fail) use ($categoria) {
                    if ((int) $value === $categoria->id) {
                        $fail('Una categoría no puede ser su propia categoría padre.');
                    }
                },
            ],
            'activo' => ['boolean'],
        ], [
            'nombre.required'  => 'El nombre de la categoría es obligatorio.',
            'nombre.max'       => 'El nombre no puede superar los 150 caracteres.',
            'parent_id.exists' => 'La categoría padre seleccionada no existe.',
        ]);

        $validated['activo'] = $request->boolean('activo', true);

        $categoria->update($validated);

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(CategoriaProducto $categoria): RedirectResponse
    {
        $categoria->update(['activo' => false]);

        return redirect()
            ->route('inventario.categorias.index')
            ->with('success', 'Categoría desactivada correctamente.');
    }
}
