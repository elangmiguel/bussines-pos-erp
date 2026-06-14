<?php

namespace App\Services;

use App\Models\Factura;
use App\Models\NotaCredito;

class DianService
{
    /**
     * Generate CUFE (Código Único de Factura Electrónica) per DIAN Annex 20.
     *
     * SHA-384 of:
     *   NumFac + FecFac + HorFac + ValFac
     *   + CodImp1 + ValImp1 + CodImp2 + ValImp2 + CodImp3 + ValImp3
     *   + ValTot + NitOFE + NumAdq + ClTec
     */
    public function generarCufe(Factura $factura): string
    {
        $factura->load(['resolucion', 'cliente.persona', 'cliente.empresa']);
        $resolucion = $factura->resolucion;

        $nitOFE  = $this->getNitEmpresa();
        $numAdq  = $this->getIdentificacionCliente($factura);
        $clTec   = $resolucion?->clave_tecnica ?? '';

        $numFac  = $factura->numero_completo;
        $fecFac  = $factura->fecha->format('Y-m-d');
        $horFac  = $factura->fecha->format('H:i:s') . '-05:00';
        $valFac  = number_format((float) $factura->subtotal, 2, '.', '');
        $valImp1 = number_format((float) ($factura->iva_5 + $factura->iva_19), 2, '.', '');
        $valImp2 = number_format((float) $factura->inc, 2, '.', '');
        $valImp3 = '0.00';
        $valTot  = number_format((float) $factura->total, 2, '.', '');

        // CodImp1 = 01 (IVA), CodImp2 = 04 (INC), CodImp3 = 03 (ICA)
        $cadena = "{$numFac}{$fecFac}{$horFac}{$valFac}01{$valImp1}04{$valImp2}03{$valImp3}{$valTot}{$nitOFE}{$numAdq}{$clTec}";

        return hash('sha384', $cadena);
    }

    /**
     * Generate QR data string per DIAN spec.
     */
    public function generarQrData(Factura $factura): string
    {
        return "https://catalogo-vpfe.dian.gov.co/document/searchqr?documentkey={$factura->cufe}";
    }

    /**
     * Generate UBL 2.1 XML for the invoice (skeleton).
     *
     * Full production implementation must be delegated to a certified
     * Operador Habilitado or a UBL 2.1 library that complies with DIAN Annex 20.
     */
    public function generarXml(Factura $factura): string
    {
        $factura->load([
            'cliente.persona',
            'cliente.empresa',
            'detalles.producto.tarifaIva',
            'pagos.medioPago',
            'resolucion',
        ]);

        return $this->buildUblXml($factura);
    }

    /**
     * Submit invoice to DIAN via Operador Habilitado (stub).
     *
     * TODO: Replace with actual HTTP call to the contracted Operador Habilitado.
     * Expected return shape: ['estado' => 'aceptada'|'rechazada'|'pendiente', 'mensaje' => string]
     *
     * @return array{estado: string, mensaje: string}
     */
    public function enviarDian(Factura $factura): array
    {
        return [
            'estado'  => 'pendiente',
            'mensaje' => 'Integración con Operador Habilitado pendiente de configuración',
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function getNitEmpresa(): string
    {
        return \App\Models\Configuracion::with('empresa')->first()?->empresa?->nit ?? '';
    }

    private function getIdentificacionCliente(Factura $factura): string
    {
        if ($factura->cliente->tipo === 'natural') {
            return $factura->cliente->persona?->numero_identificacion ?? '';
        }

        return $factura->cliente->empresa?->nit ?? '';
    }

    private function buildUblXml(Factura $factura): string
    {
        $fecha = $factura->fecha->format('Y-m-d');
        $hora  = $factura->fecha->format('H:i:s');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
  <cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>
  <cbc:CustomizationID>10</cbc:CustomizationID>
  <cbc:ProfileID>DIAN 2.1</cbc:ProfileID>
  <cbc:ProfileExecutionID>1</cbc:ProfileExecutionID>
  <cbc:ID>{$factura->numero_completo}</cbc:ID>
  <cbc:UUID schemeID="CUFE-SHA384" schemeName="CUFE-SHA384">{$factura->cufe}</cbc:UUID>
  <cbc:IssueDate>{$fecha}</cbc:IssueDate>
  <cbc:IssueTime>{$hora}-05:00</cbc:IssueTime>
  <cbc:InvoiceTypeCode listAgencyID="195" listAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" listID="0110">01</cbc:InvoiceTypeCode>
  <cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listAgencyID="6" listAgencyName="United Nations Economic Commission for Europe">COP</cbc:DocumentCurrencyCode>
  <!-- Full UBL 2.1 implementation required per DIAN Annex 20. -->
  <!-- Delegate to a DIAN-certified Operador Habilitado for production use. -->
</Invoice>
XML;
    }
}
