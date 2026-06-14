<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventario\AjusteStockRequest;
use App\Services\InventarioService;
use Illuminate\Http\RedirectResponse;

class AjusteController extends Controller
{
    public function __construct(private readonly InventarioService $inventarioService)
    {
    }

    public function store(AjusteStockRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->inventarioService->ajustarStock(
            productoId:    (int) $data['producto_id'],
            nuevaCantidad: (float) $data['nueva_cantidad'],
            motivo:        $data['motivo'],
            userId:        $request->user()->id,
        );

        return redirect()
            ->back()
            ->with('success', 'Ajuste de stock realizado correctamente.');
    }
}
