<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo {{ $factura->numero_completo }}</title>
    <style>
        @page {
            margin: 4mm 3mm 4mm 3mm;
            size: 80mm auto;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 8pt;
            color: #000;
            width: 72mm;
            max-width: 72mm;
            margin: 0 auto;
            line-height: 1.35;
        }
        .center  { text-align: center; }
        .right   { text-align: right; }
        .left    { text-align: left; }
        .bold    { font-weight: bold; }
        .small   { font-size: 7pt; }
        .tiny    { font-size: 6.5pt; }
        .mt2     { margin-top: 2px; }
        .mt4     { margin-top: 4px; }
        .mt6     { margin-top: 6px; }
        .empresa-nombre {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .empresa-info {
            font-size: 7pt;
            text-align: center;
            color: #222;
        }
        .separator {
            border: none;
            border-top: 1px dashed #000;
            margin: 4px 0;
            width: 100%;
        }
        .separator-solid {
            border: none;
            border-top: 1px solid #000;
            margin: 4px 0;
            width: 100%;
        }
        .factura-num {
            font-size: 10pt;
            font-weight: bold;
            text-align: center;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .items-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .totals-table td {
            padding: 1px 0;
        }
        .total-row td {
            font-weight: bold;
            font-size: 9.5pt;
            border-top: 1px solid #000;
            padding-top: 2px;
        }
        .cufe-section {
            font-size: 6pt;
            word-break: break-all;
            color: #333;
            margin-top: 4px;
        }
        .gracias {
            font-size: 9pt;
            font-weight: bold;
            text-align: center;
            margin-top: 6px;
            margin-bottom: 2px;
        }
        .dian-text {
            font-size: 6.5pt;
            text-align: center;
            color: #444;
        }
    </style>
</head>
<body>

{{-- ===== ENCABEZADO EMPRESA ===== --}}
@if ($configuracion && $configuracion->empresa)
    <p class="empresa-nombre">{{ strtoupper($configuracion->empresa->razon_social) }}</p>
    <p class="empresa-info">NIT: {{ $configuracion->empresa->nit }}-{{ $configuracion->empresa->digito_verificacion }}</p>
    @if ($configuracion->empresa->direccion)
        <p class="empresa-info">{{ $configuracion->empresa->direccion }}</p>
    @endif
    @if ($configuracion->empresa->ciudad)
        <p class="empresa-info">{{ $configuracion->empresa->ciudad }}</p>
    @endif
    @if ($configuracion->empresa->telefono)
        <p class="empresa-info">Tel: {{ $configuracion->empresa->telefono }}</p>
    @endif
@endif

<hr class="separator">

{{-- ===== DATOS FACTURA ===== --}}
<p class="factura-num">FACTURA</p>
<p class="factura-num">{{ $factura->numero_completo }}</p>
<p class="center small mt2">
    {{ \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y H:i') }}
</p>

<hr class="separator">

{{-- ===== DATOS CLIENTE ===== --}}
<p class="small"><span class="bold">Cliente:</span> {{ $factura->cliente?->nombre ?? 'Consumidor Final' }}</p>
<p class="small"><span class="bold">ID:</span> {{ $factura->cliente?->identificacion ?? '222222222222' }}</p>

<hr class="separator">

{{-- ===== ÍTEMS ===== --}}
<table class="items-table">
    <tbody>
        @foreach ($factura->detalles as $det)
            @php
                $nombreCorto = mb_substr($det->descripcion ?? $det->producto?->nombre ?? '—', 0, 20);
                $qty  = (float) $det->cantidad;
                $pu   = (float) $det->precio_unitario;
                $tot  = (float) $det->total;
            @endphp
            <tr>
                <td colspan="2" class="bold">{{ $nombreCorto }}</td>
            </tr>
            <tr>
                <td class="left small">
                    {{ number_format($qty, 2) }}x{{ number_format($pu, 0, ',', '.') }}
                </td>
                <td class="right bold">
                    ${{ number_format($tot, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<hr class="separator-solid">

{{-- ===== TOTALES ===== --}}
<table class="totals-table">
    <tr>
        <td class="left">Subtotal:</td>
        <td class="right">${{ number_format((float)$factura->subtotal, 0, ',', '.') }}</td>
    </tr>
    @php $totalIva = (float)$factura->iva_5 + (float)$factura->iva_19; @endphp
    @if ($totalIva > 0)
    <tr>
        <td class="left">IVA:</td>
        <td class="right">${{ number_format($totalIva, 0, ',', '.') }}</td>
    </tr>
    @endif
    @if ($factura->inc > 0)
    <tr>
        <td class="left">INC:</td>
        <td class="right">${{ number_format((float)$factura->inc, 0, ',', '.') }}</td>
    </tr>
    @endif
    @if ($factura->descuento_global > 0)
    <tr>
        <td class="left">Descuento:</td>
        <td class="right">-${{ number_format((float)$factura->descuento_global, 0, ',', '.') }}</td>
    </tr>
    @endif
    <tr class="total-row">
        <td class="left">TOTAL:</td>
        <td class="right">${{ number_format((float)$factura->total, 0, ',', '.') }}</td>
    </tr>
</table>

<hr class="separator">

{{-- ===== FORMA DE PAGO ===== --}}
<p class="small"><span class="bold">Forma de pago:</span> {{ $factura->tipo_pago === 'contado' ? 'Contado' : 'Crédito' }}</p>
@foreach ($factura->pagos as $pago)
    <p class="small">{{ $pago->medioPago?->nombre ?? '—' }}: ${{ number_format((float)$pago->monto, 0, ',', '.') }}</p>
    @if ($pago->referencia)
        <p class="tiny">Ref: {{ $pago->referencia }}</p>
    @endif
@endforeach
@if ($factura->tipo_pago === 'credito' && $factura->fecha_vencimiento)
    <p class="small"><span class="bold">Vcto:</span> {{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}</p>
@endif

<hr class="separator">

{{-- ===== CUFE ===== --}}
@if ($factura->cufe)
    <div class="cufe-section">
        <span class="bold">CUFE:</span><br>
        {{ $factura->cufe }}
    </div>
@endif

<hr class="separator mt4">

<p class="gracias">¡Gracias por su compra!</p>
<p class="dian-text">Factura electrónica autorizada por la DIAN</p>

</body>
</html>
