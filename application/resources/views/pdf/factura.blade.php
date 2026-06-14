<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura {{ $factura->numero_completo }}</title>
    <style>
        @page {
            margin: 15mm 12mm 15mm 12mm;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: top;
            padding: 4px;
        }
        .logo-cell {
            width: 120px;
        }
        .logo-cell img {
            max-width: 110px;
            max-height: 70px;
        }
        .empresa-cell {
            padding-left: 10px;
        }
        .empresa-nombre {
            font-size: 13pt;
            font-weight: bold;
            color: #1a1a1a;
        }
        .empresa-info {
            font-size: 8.5pt;
            color: #444;
            margin-top: 2px;
        }
        .factura-title-cell {
            text-align: right;
            width: 200px;
        }
        .factura-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1a1a1a;
            border: 2px solid #1a1a1a;
            padding: 6px 10px;
            display: inline-block;
            text-align: center;
        }
        .factura-meta {
            font-size: 8.5pt;
            color: #444;
            margin-top: 4px;
            text-align: right;
        }
        .factura-numero {
            font-size: 11pt;
            font-weight: bold;
            color: #1a1a1a;
            text-align: right;
            margin-top: 3px;
        }
        .divider {
            border: none;
            border-top: 1.5px solid #1a1a1a;
            margin: 8px 0;
        }
        .divider-thin {
            border: none;
            border-top: 0.5px solid #aaa;
            margin: 6px 0;
        }
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #555;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-grid td {
            padding: 2px 4px;
            font-size: 9pt;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 140px;
        }
        .bill-to-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .bill-to-table td {
            padding: 3px 6px;
            font-size: 9pt;
            vertical-align: top;
        }
        .bill-to-box {
            border: 1px solid #ccc;
            padding: 6px 8px;
            background: #fafafa;
            margin-bottom: 10px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        .items-table thead tr {
            background-color: #2c2c2c;
            color: #ffffff;
        }
        .items-table thead th {
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8pt;
        }
        .items-table thead th.right {
            text-align: right;
        }
        .items-table thead th.center {
            text-align: center;
        }
        .items-table tbody tr {
            border-bottom: 0.5px solid #ddd;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f7f7f7;
        }
        .items-table tbody td {
            padding: 4px 4px;
            vertical-align: top;
        }
        .items-table tbody td.right {
            text-align: right;
        }
        .items-table tbody td.center {
            text-align: center;
        }
        .totals-table {
            width: 220px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 10px;
        }
        .totals-table td {
            padding: 3px 6px;
        }
        .totals-table .label {
            color: #444;
        }
        .totals-table .amount {
            text-align: right;
            font-family: 'Courier New', monospace;
        }
        .totals-total {
            font-weight: bold;
            font-size: 10.5pt;
            border-top: 1.5px solid #1a1a1a;
            background-color: #f0f0f0;
        }
        .payment-box {
            border: 1px solid #ccc;
            padding: 6px 10px;
            margin-bottom: 10px;
            font-size: 9pt;
            background: #fafafa;
        }
        .payment-box table {
            width: 100%;
            border-collapse: collapse;
        }
        .payment-box td {
            padding: 2px 4px;
        }
        .cufe-box {
            border: 1px solid #bbb;
            padding: 5px 8px;
            margin-bottom: 8px;
            background: #f5f5f5;
        }
        .cufe-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #555;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .cufe-value {
            font-family: 'Courier New', monospace;
            font-size: 7pt;
            color: #333;
            word-break: break-all;
        }
        .resolution-box {
            font-size: 8pt;
            color: #555;
            border-top: 0.5px solid #ccc;
            padding-top: 5px;
            margin-bottom: 6px;
        }
        .footer {
            border-top: 1.5px solid #1a1a1a;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #666;
            text-align: center;
        }
        .badge-estado {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-emitida  { background: #d1fae5; color: #065f46; }
        .badge-anulada  { background: #fee2e2; color: #991b1b; }
        .badge-borrador { background: #e5e7eb; color: #374151; }
        .two-col {
            width: 100%;
            border-collapse: collapse;
        }
        .two-col td {
            vertical-align: top;
            width: 50%;
            padding-right: 10px;
        }
        .two-col td:last-child {
            padding-right: 0;
        }
        .block-label {
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #888;
            border-bottom: 0.5px solid #ccc;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }
        .block-value {
            font-size: 9pt;
            color: #1a1a1a;
        }
        .block-value-small {
            font-size: 8pt;
            color: #444;
        }
    </style>
</head>
<body>

{{-- ===== HEADER ===== --}}
<table class="header-table">
    <tr>
        <td class="logo-cell">
            @if (!empty($configuracion->logo))
                <img src="{{ public_path('storage/' . $configuracion->logo) }}" alt="Logo">
            @endif
        </td>
        <td class="empresa-cell">
            @if ($configuracion && $configuracion->empresa)
                <div class="empresa-nombre">{{ $configuracion->empresa->razon_social }}</div>
                <div class="empresa-info">
                    NIT: {{ $configuracion->empresa->nit }}-{{ $configuracion->empresa->digito_verificacion }}<br>
                    {{ $configuracion->empresa->direccion }}{{ $configuracion->empresa->ciudad ? ', ' . $configuracion->empresa->ciudad : '' }}<br>
                    @if ($configuracion->empresa->telefono)Tel: {{ $configuracion->empresa->telefono }}<br>@endif
                    @if ($configuracion->empresa->email){{ $configuracion->empresa->email }}<br>@endif
                    Régimen: {{ $configuracion->empresa->regimen_tributario ?? 'Responsable de IVA' }}
                </div>
            @endif
        </td>
        <td class="factura-title-cell">
            <div class="factura-title">FACTURA ELECTRÓNICA<br>DE VENTA</div>
            <div class="factura-numero">{{ $factura->numero_completo }}</div>
            <div class="factura-meta">
                Fecha: {{ \Carbon\Carbon::parse($factura->fecha)->format('d/m/Y H:i') }}<br>
                @if ($factura->estado === 'emitida')
                    <span style="color:#065f46;font-weight:bold;">EMITIDA</span>
                @elseif ($factura->estado === 'anulada')
                    <span style="color:#991b1b;font-weight:bold;">ANULADA</span>
                @else
                    <span style="color:#374151;">BORRADOR</span>
                @endif
            </div>
        </td>
    </tr>
</table>

{{-- ===== RESOLUCIÓN DIAN ===== --}}
@if ($factura->resolucion)
    <div class="resolution-box">
        Resolución DIAN N° <strong>{{ $factura->resolucion->numero_resolucion }}</strong>
        del {{ \Carbon\Carbon::parse($factura->resolucion->fecha_resolucion)->format('d/m/Y') }}
        &mdash; Vigencia: {{ \Carbon\Carbon::parse($factura->resolucion->fecha_inicio)->format('d/m/Y') }}
        al {{ \Carbon\Carbon::parse($factura->resolucion->fecha_fin)->format('d/m/Y') }}
        &mdash; Rango autorizado: {{ $factura->resolucion->prefijo }}{{ $factura->resolucion->rango_desde }}
        al {{ $factura->resolucion->prefijo }}{{ $factura->resolucion->rango_hasta }}
    </div>
@endif

<hr class="divider">

{{-- ===== DATOS CLIENTE ===== --}}
<div class="section-title">Datos del adquirente</div>
<div class="bill-to-box">
    <table class="two-col">
        <tr>
            <td>
                <div class="block-label">Nombre / Razón Social</div>
                <div class="block-value"><strong>{{ $factura->cliente?->nombre ?? 'Consumidor Final' }}</strong></div>
            </td>
            <td>
                <div class="block-label">NIT / Identificación</div>
                <div class="block-value">{{ $factura->cliente?->identificacion ?? '222222222222' }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding-top:5px;">
                <div class="block-label">Dirección</div>
                <div class="block-value-small">
                    @if ($factura->cliente?->tipo === 'natural' && $factura->cliente?->persona)
                        {{ $factura->cliente->persona->direccion ?? '—' }}
                    @elseif ($factura->cliente?->tipo === 'juridico' && $factura->cliente?->empresa)
                        {{ $factura->cliente->empresa->direccion ?? '—' }}
                    @else
                        —
                    @endif
                </div>
            </td>
            <td style="padding-top:5px;">
                <div class="block-label">Correo electrónico</div>
                <div class="block-value-small">
                    @if ($factura->cliente?->tipo === 'natural' && $factura->cliente?->persona)
                        {{ $factura->cliente->persona->email ?? '—' }}
                    @elseif ($factura->cliente?->tipo === 'juridico' && $factura->cliente?->empresa)
                        {{ $factura->cliente->empresa->email ?? '—' }}
                    @else
                        —
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- ===== CUFE TRUNCADO ===== --}}
@if ($factura->cufe)
    <div class="cufe-box">
        <div class="cufe-title">CUFE (Código Único de Factura Electrónica)</div>
        <div class="cufe-value">{{ substr($factura->cufe, 0, 80) }}{{ strlen($factura->cufe) > 80 ? '...' : '' }}</div>
    </div>
@endif

{{-- ===== TABLA DE ÍTEMS ===== --}}
<div class="section-title">Detalle de la factura</div>
<table class="items-table">
    <thead>
        <tr>
            <th style="width:22px;">#</th>
            <th>Descripción</th>
            <th class="center" style="width:50px;">Cant.</th>
            <th class="right" style="width:75px;">Precio Unit.</th>
            <th class="right" style="width:45px;">Desc%</th>
            <th class="right" style="width:75px;">Base Grav.</th>
            <th class="center" style="width:40px;">IVA%</th>
            <th class="right" style="width:60px;">IVA $</th>
            <th class="right" style="width:75px;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($factura->detalles as $i => $det)
            <tr>
                <td style="color:#888;">{{ $i + 1 }}</td>
                <td>
                    {{ $det->descripcion ?? $det->producto?->nombre ?? '—' }}
                    @if ($det->producto?->referencia)
                        <br><span style="font-size:7.5pt;color:#888;">Ref: {{ $det->producto->referencia }}</span>
                    @endif
                </td>
                <td class="center">{{ number_format((float) $det->cantidad, 2) }}</td>
                <td class="right">{{ number_format((float) $det->precio_unitario, 0, ',', '.') }}</td>
                <td class="right">{{ number_format((float) $det->descuento_pct, 1) }}%</td>
                <td class="right">{{ number_format((float) $det->subtotal, 0, ',', '.') }}</td>
                <td class="center">{{ $det->tarifaIva?->porcentaje ?? 0 }}%</td>
                <td class="right">{{ number_format((float) $det->iva, 0, ',', '.') }}</td>
                <td class="right"><strong>{{ number_format((float) $det->total, 0, ',', '.') }}</strong></td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- ===== TOTALES ===== --}}
<table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
    <tr>
        <td style="vertical-align:top;width:55%;">
            {{-- Desglose tributario --}}
            <div class="section-title">Resumen tributario</div>
            <table style="font-size:8.5pt;border-collapse:collapse;">
                <tr>
                    <td style="padding:2px 6px;color:#555;">Base excluida (IVA 0%):</td>
                    <td style="padding:2px 6px;text-align:right;font-family:'Courier New',monospace;">$ {{ number_format((float)$factura->base_iva_0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 6px;color:#555;">Base gravable 5%:</td>
                    <td style="padding:2px 6px;text-align:right;font-family:'Courier New',monospace;">$ {{ number_format((float)$factura->base_iva_5, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 6px;color:#555;">IVA 5%:</td>
                    <td style="padding:2px 6px;text-align:right;font-family:'Courier New',monospace;">$ {{ number_format((float)$factura->iva_5, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 6px;color:#555;">Base gravable 19%:</td>
                    <td style="padding:2px 6px;text-align:right;font-family:'Courier New',monospace;">$ {{ number_format((float)$factura->base_iva_19, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td style="padding:2px 6px;color:#555;">IVA 19%:</td>
                    <td style="padding:2px 6px;text-align:right;font-family:'Courier New',monospace;">$ {{ number_format((float)$factura->iva_19, 0, ',', '.') }}</td>
                </tr>
                @if ($factura->inc > 0)
                <tr>
                    <td style="padding:2px 6px;color:#555;">INC:</td>
                    <td style="padding:2px 6px;text-align:right;font-family:'Courier New',monospace;">$ {{ number_format((float)$factura->inc, 0, ',', '.') }}</td>
                </tr>
                @endif
            </table>
        </td>
        <td style="vertical-align:top;width:45%;">
            <table class="totals-table">
                <tr>
                    <td class="label">Subtotal:</td>
                    <td class="amount">$ {{ number_format((float)$factura->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">IVA (5% + 19%):</td>
                    <td class="amount">$ {{ number_format((float)($factura->iva_5 + $factura->iva_19), 0, ',', '.') }}</td>
                </tr>
                @if ($factura->inc > 0)
                <tr>
                    <td class="label">INC:</td>
                    <td class="amount">$ {{ number_format((float)$factura->inc, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if ($factura->descuento_global > 0)
                <tr>
                    <td class="label">Descuento global:</td>
                    <td class="amount" style="color:#c0392b;">- $ {{ number_format((float)$factura->descuento_global, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="totals-total">
                    <td class="label" style="font-size:11pt;">TOTAL:</td>
                    <td class="amount" style="font-size:11pt;">$ {{ number_format((float)$factura->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

{{-- ===== INFORMACIÓN DE PAGO ===== --}}
<div class="section-title">Información de pago</div>
<div class="payment-box">
    <table>
        <tr>
            <td style="font-weight:bold;width:140px;">Forma de pago:</td>
            <td>{{ $factura->tipo_pago === 'contado' ? 'Contado' : 'Crédito' }}</td>
            @if ($factura->fecha_vencimiento && $factura->tipo_pago === 'credito')
                <td style="font-weight:bold;width:140px;">Fecha vencimiento:</td>
                <td>{{ \Carbon\Carbon::parse($factura->fecha_vencimiento)->format('d/m/Y') }}</td>
            @endif
        </tr>
        @foreach ($factura->pagos as $pago)
        <tr>
            <td style="font-weight:bold;">Medio de pago:</td>
            <td>{{ $pago->medioPago?->nombre ?? '—' }}</td>
            <td style="font-weight:bold;">Monto:</td>
            <td>$ {{ number_format((float)$pago->monto, 0, ',', '.') }}</td>
        </tr>
        @if ($pago->referencia)
        <tr>
            <td></td>
            <td colspan="3" style="font-size:8pt;color:#666;">Ref: {{ $pago->referencia }}</td>
        </tr>
        @endif
        @endforeach
    </table>
</div>

@if ($factura->observaciones)
<div style="font-size:8.5pt;color:#555;margin-bottom:8px;border:0.5px solid #ddd;padding:5px 8px;">
    <strong>Observaciones:</strong> {{ $factura->observaciones }}
</div>
@endif

{{-- ===== FOOTER ===== --}}
<div class="footer">
    <p><strong>Este documento es una factura electrónica autorizada por la DIAN</strong></p>
    @if ($factura->cufe)
        <p style="margin-top:3px;">CUFE: <span style="font-family:'Courier New',monospace;font-size:7pt;word-break:break-all;">{{ $factura->cufe }}</span></p>
    @endif
    @if ($factura->qr_data)
        <p style="margin-top:3px;font-size:7pt;color:#888;">QR: {{ $factura->qr_data }}</p>
    @endif
</div>

</body>
</html>
