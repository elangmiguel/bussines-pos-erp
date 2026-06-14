# DIAN Compliance & Colombian Tax Requirements

## Applicable Regulations

| Regulation | Description |
|---|---|
| Resolución 000042/2020 | Technical annex for electronic invoicing (Anexo Técnico 1.8) |
| Decreto 358/2020 | Electronic invoicing as mandatory obligation for mipymes |
| Estatuto Tributario Art. 437-499 | IVA rules |
| Resolución 000013/2021 | Electronic equivalent documents (POS ticket → factura) |

---

## Tax Rates (Tarifas IVA)

| Code | Name | Rate | Applicable to |
|---|---|---|---|
| `IVA_19` | IVA General | 19% | Most goods and services |
| `IVA_5` | IVA Reducido | 5% | Housing materials, agricultural inputs |
| `EXCLUIDO` | Excluido de IVA | 0% | Basic food, medicine, education |
| `EXENTO` | Exento de IVA | 0% | Exported goods |
| `INC` | Imp. Nacional al Consumo | 8% or 16% | Restaurants, vehicles, luxury goods |

---

## Identification Types (Colombian)

| Code | Name | Who |
|---|---|---|
| `CC` | Cédula de Ciudadanía | Colombian citizens |
| `CE` | Cédula de Extranjería | Foreign residents |
| `TI` | Tarjeta de Identidad | Minors |
| `PAS` | Pasaporte | Foreign persons without CE |
| `NIT` | Número de Identificación Tributaria | Legal entities and natural persons registered as taxpayers |
| `RC` | Registro Civil | Newborns |

---

## Electronic Invoice Requirements (Factura Electrónica de Venta)

### Mandatory fields per DIAN Annex 20

```
- Número y prefijo (from DIAN resolution)
- Fecha y hora de emisión
- CUFE (Código Único de Factura Electrónica)
- Código QR
- NIT del emisor + nombre + dirección + régimen tributario
- NIT/CC del receptor + nombre + dirección
- Descripción de items, cantidad, precio unitario, descuentos
- Base gravable por tarifa IVA
- IVA por tarifa
- Total
- Forma de pago (contado / crédito + fecha vencimiento)
- Medio de pago
- Firma digital del emisor (via Operador Habilitado)
```

### CUFE Generation (SHA-384)

CUFE = SHA384( NumFac + FecFac + HorFac + ValFac + CodImp1 + ValImp1 + CodImp2 + ValImp2 + CodImp3 + ValImp3 + ValTot + NitOFE + NumAdq + ClTec )

Where:
- `NumFac` = invoice number with prefix
- `FecFac` = date YYYY-MM-DD
- `HorFac` = time HH:MM:SS-05:00 (Colombia UTC-5)
- `ValFac` = subtotal formatted as `1234567.00`
- `CodImp1` = "01" (IVA), `ValImp1` = IVA amount
- `CodImp2` = "04" (INC), `ValImp2` = INC amount
- `CodImp3` = "03" (ICA — not applicable for most mipymes), `ValImp3` = "0.00"
- `ValTot` = total
- `NitOFE` = business NIT without check digit
- `NumAdq` = client identification number
- `ClTec` = technical key from DIAN resolution

### XML Format (UBL 2.1)

The system generates standard DIAN UBL 2.1 XML. Key namespaces:
```xml
xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
```

**Implementation note**: XML generation and digital signature should be delegated to a certified **Operador Habilitado** (e.g., Alegra, Siigo API, or a dedicated DIAN middleware). The system generates the payload; the operator handles signature and DIAN submission.

---

## Resolución de Facturación

Before issuing any electronic invoice, the business must:

1. Register with DIAN as electronic biller
2. Obtain a billing resolution with:
   - `numero_resolucion` — authorization number
   - `prefijo` — optional prefix (e.g., "FV", "FACT")
   - `rango_desde` / `rango_hasta` — authorized invoice number range
   - `fecha_inicio` / `fecha_fin` — validity period
   - `clave_tecnica` — used in CUFE calculation

The system tracks `numero_actual` and auto-increments per invoice. It warns when within 100 invoices of the end of range or 30 days of expiry.

---

## DIAN Reports for Mipymes

### Libro de Ventas (Sales Book)

Required for IVA declaration. Exported monthly or bi-monthly.

Columns:
```
Fecha | NIT Cliente | Nombre | No. Factura | Subtotal Grav. | Base IVA 5% | IVA 5% | Base IVA 19% | IVA 19% | Excluido | Total
```

### Libro de Compras (Purchase Book)

Required for IVA input credit.

Columns:
```
Fecha | NIT Proveedor | Nombre | No. Doc | Subtotal | Base IVA | IVA Descontable | Retefuente | Total
```

### Informe de IVA

Summary per period:
```
IVA Generado (ventas)
- IVA Descontable (compras)
= IVA a Pagar / Saldo a Favor
```

### Retención en la Fuente

Only applicable if the business is a **Agente Retenedor**. Most small mipymes are not. If applicable:
- Retefuente 2.5% on services
- Retefuente 3.5% on goods

### Renta Simplificada (Simplified Income Tax)

For mipymes in **régimen simple de tributación**:
- Report total annual income by quarter
- System generates total ingresos brutos per period

---

## Business Regimes

| Regime | When | IVA obligation |
|---|---|---|
| **Responsable de IVA** | Annual income > 3,500 UVT (~$166M COP 2024) or if billing other responsables | Must charge, declare, and pay IVA |
| **No Responsable de IVA** | Income below threshold, personal business | Does not charge IVA, simplified invoice |
| **Régimen Simple (SIMPLE)** | Optional for small businesses | Replaces income tax + ICA; pays quarterly |

The system's `configuracion.regimen_tributario` field drives IVA behavior:
- `responsable_iva = true` → apply IVA to all taxable products, show IVA on invoice
- `responsable_iva = false` → no IVA applied, simplified invoice format

---

## Credit Notes (Notas Crédito)

Required when:
- Total or partial return of goods
- Price corrections (downward)
- Invoice cancellation after DIAN acceptance

Must reference the original invoice's `numero_completo` and `cufe`. Has its own CUFE (CUFN).

## Debit Notes (Notas Débito)

Required when:
- Additional charges on an existing invoice
- Price corrections (upward)

---

## Implementation Checklist

- [ ] `DianService::generarCufe()` — SHA-384 per DIAN spec
- [ ] `DianService::generarXml()` — UBL 2.1 with all mandatory fields
- [ ] `DianService::generarQr()` — URL format per DIAN spec
- [ ] Resolución tracking with range and date expiry warnings
- [ ] PDF invoice with DIAN logo, CUFE, QR, and all mandatory fields
- [ ] 80mm thermal receipt format (simplified, with QR)
- [ ] `ReporteService::libroVentas()` — filterable by date range, exportable to Excel
- [ ] `ReporteService::libroCompras()` — exportable to Excel
- [ ] Integration endpoint for Operador Habilitado webhook (status updates: aceptada/rechazada)
- [ ] Soft-delete only on invoices (physical deletion prohibited by law — 5-year retention)
