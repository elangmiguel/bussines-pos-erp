<?php

namespace App\Events;

use App\Models\Producto;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockBajoDetectado implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $producto_id,
        public readonly string $codigo,
        public readonly string $nombre,
        public readonly float $stock_actual,
        public readonly float $stock_minimo,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('stock-alertas'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.bajo';
    }

    public function broadcastWith(): array
    {
        return [
            'producto_id'  => $this->producto_id,
            'codigo'       => $this->codigo,
            'nombre'       => $this->nombre,
            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,
        ];
    }

    /**
     * Fire this event from the InventarioService when stock drops to or below minimum.
     */
    public static function dispatchIfBajoStock(Producto $producto): void
    {
        if ($producto->activo && (float) $producto->stock_actual <= (float) $producto->stock_minimo) {
            static::dispatch(
                $producto->id,
                $producto->codigo,
                $producto->nombre,
                (float) $producto->stock_actual,
                (float) $producto->stock_minimo,
            );
        }
    }
}
