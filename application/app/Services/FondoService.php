<?php

namespace App\Services;

use App\Models\Fondo;
use App\Models\MovimientoFondo;
use Illuminate\Support\Facades\DB;

class FondoService
{
    public function registrarIngreso(
        int $fondoId,
        float $monto,
        string $descripcion,
        string $referenciaType,
        int $referenciaId,
        int $userId
    ): void {
        DB::transaction(function () use ($fondoId, $monto, $descripcion, $referenciaType, $referenciaId, $userId) {
            /** @var Fondo $fondo */
            $fondo = Fondo::lockForUpdate()->findOrFail($fondoId);

            $fondo->saldo_actual = (float) $fondo->saldo_actual + $monto;
            $fondo->save();

            MovimientoFondo::create([
                'fondo_id'        => $fondoId,
                'tipo'            => 'ingreso',
                'monto'           => $monto,
                'descripcion'     => $descripcion,
                'referencia_tipo' => $referenciaType,
                'referencia_id'   => $referenciaId,
                'user_id'         => $userId,
                'created_at'      => now(),
            ]);
        });
    }

    public function registrarEgreso(
        int $fondoId,
        float $monto,
        string $descripcion,
        string $referenciaType,
        int $referenciaId,
        int $userId
    ): void {
        DB::transaction(function () use ($fondoId, $monto, $descripcion, $referenciaType, $referenciaId, $userId) {
            /** @var Fondo $fondo */
            $fondo = Fondo::lockForUpdate()->findOrFail($fondoId);

            $fondo->saldo_actual = max(0, (float) $fondo->saldo_actual - $monto);
            $fondo->save();

            MovimientoFondo::create([
                'fondo_id'        => $fondoId,
                'tipo'            => 'egreso',
                'monto'           => $monto,
                'descripcion'     => $descripcion,
                'referencia_tipo' => $referenciaType,
                'referencia_id'   => $referenciaId,
                'user_id'         => $userId,
                'created_at'      => now(),
            ]);
        });
    }

    public function transferir(
        int $fondoOrigenId,
        int $fondoDestinoId,
        float $monto,
        string $descripcion,
        int $userId
    ): void {
        DB::transaction(function () use ($fondoOrigenId, $fondoDestinoId, $monto, $descripcion, $userId) {
            $this->registrarEgreso(
                $fondoOrigenId,
                $monto,
                "Transferencia hacia fondo #{$fondoDestinoId}: {$descripcion}",
                'transferencia',
                $fondoDestinoId,
                $userId
            );

            $this->registrarIngreso(
                $fondoDestinoId,
                $monto,
                "Transferencia desde fondo #{$fondoOrigenId}: {$descripcion}",
                'transferencia',
                $fondoOrigenId,
                $userId
            );
        });
    }
}
