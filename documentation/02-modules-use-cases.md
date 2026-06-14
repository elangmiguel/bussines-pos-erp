# Modules & Use Cases

## Module Map

```
businesscmd-pos/
├── Dashboard
├── POS (Punto de Venta)
├── Inventario
├── Clientes & Cartera
├── Proveedores & Compras
├── Facturación Electrónica
├── Caja & Fondos
├── Gastos
├── Reportes
└── Configuración
```

---

## 1. Dashboard

**Actors**: Administrador, Vendedor (limited view)

| Use Case | Description |
|---|---|
| Ver resumen del día | Total ventas, número de facturas, caja actual |
| Alertas de inventario | Productos con stock bajo o agotados |
| Cuentas por cobrar vencidas | Clientes con cartera vencida |
| Accesos rápidos | Botones a POS, nueva venta, nueva compra |
| Gráficas de tendencia | Ventas por semana/mes, top productos |

---

## 2. POS — Punto de Venta

**Actors**: Vendedor, Cajero

The primary daily interface. Designed for speed — keyboard and barcode scanner friendly.

| Use Case | Description |
|---|---|
| Abrir turno de caja | Cajero ingresa saldo inicial; caja queda abierta |
| Buscar producto | Por código de barras, código interno, o nombre |
| Agregar ítem al carrito | Con cantidad, precio, y descuento por línea |
| Seleccionar cliente | Buscar/crear cliente en el momento |
| Aplicar descuento global | Descuento sobre el total de la venta |
| Seleccionar medio de pago | Efectivo, tarjeta, Nequi, Daviplata, transferencia, mixto |
| Registrar pago mixto | Dividir pago entre múltiples medios |
| Generar factura | Crea factura con CUFE y QR, descuenta inventario |
| Imprimir/enviar recibo | Térmica (80mm) o PDF por email/WhatsApp |
| Aplicar venta a crédito | Si el cliente tiene cupo habilitado |
| Devolución rápida | Crear nota crédito desde factura anterior |
| Cerrar turno de caja | Cajero ingresa saldo final; genera cuadre |

---

## 3. Inventario

**Actors**: Bodeguero, Administrador

| Use Case | Description |
|---|---|
| Crear/editar producto | Código, barcode, categoría, unidad medida, IVA, precios, stock mín/máx |
| Ver trazabilidad de producto | Historial completo: compras, ventas, ajustes, devoluciones |
| Ajustar stock | Ajuste manual con motivo (daño, robo, conteo físico) |
| Ver productos con bajo stock | Filtrar por stock actual ≤ stock mínimo |
| Gestionar categorías | Árbol jerárquico de categorías |
| Gestionar unidades de medida | Unidades, kg, litros, metros, etc. |
| Importar productos | Desde archivo Excel/CSV |
| Exportar inventario | Valorizado o físico en Excel |
| Ver kardex de producto | Movimientos cronológicos de un producto |

---

## 4. Clientes & Cartera

**Actors**: Vendedor, Administrador

| Use Case | Description |
|---|---|
| Crear cliente natural | CC/CE/TI/PAS, nombres, contacto |
| Crear cliente jurídico | NIT, razón social, representante legal |
| Editar/desactivar cliente | Soft-delete; mantiene historial |
| Ver historial de compras | Todas las facturas del cliente |
| Habilitar crédito | Definir cupo y plazo de pago |
| Ver estado de cartera | Saldo pendiente, facturas vencidas, abonos |
| Registrar abono a cartera | Pago parcial o total de facturas en crédito |
| Generar paz y salvo | Certificado de cuenta al día |
| Reporte de antigüedad de cartera | Vencida 0-30, 31-60, 61-90, >90 días |

---

## 5. Proveedores & Compras

**Actors**: Bodeguero, Administrador

| Use Case | Description |
|---|---|
| Crear proveedor | Natural (CC/NIT) o jurídico (empresa) |
| Asociar productos a proveedor | Con precio de compra y tiempo de entrega |
| Crear orden de compra | Seleccionar proveedor, productos, cantidades |
| Enviar/aprobar orden | Estado: Borrador → Enviada → Recibida |
| Recibir mercancía | Registrar cantidades reales recibidas |
| Registrar discrepancias | Diferencia entre orden y recepción |
| Ver historial de compras por proveedor | Órdenes y recepciones |
| Gestionar cuentas por pagar | Seguimiento de pagos a proveedores |

---

## 6. Facturación Electrónica

**Actors**: Vendedor, Cajero, Administrador

Compliant with DIAN resolution 042 and subsequent technical annexes.

| Use Case | Description |
|---|---|
| Emitir factura de venta | Numeración secuencial con prefijo y resolución DIAN |
| Ver/descargar XML factura | Formato DIAN UBL 2.1 |
| Ver/descargar PDF factura | Con CUFE, QR y firma |
| Anular factura | Solo si no tiene abonos; genera nota de anulación |
| Emitir nota crédito | Devolución total/parcial, con referencia a factura original |
| Emitir nota débito | Cargo adicional sobre factura original |
| Reenviar factura | Por email al cliente |
| Ver estado DIAN | Aceptada, rechazada, pendiente |
| Consultar resolución vigente | Prefijo, rango, vencimiento |

---

## 7. Caja & Fondos

**Actors**: Cajero, Administrador

| Use Case | Description |
|---|---|
| Abrir caja (turno) | Registro de saldo inicial por fondo |
| Registrar movimiento de fondo | Ingreso o egreso con descripción |
| Hacer arqueo parcial | Conteo de caja en mitad del turno |
| Cerrar caja (turno) | Saldo final, diferencia, observaciones |
| Ver cuadre de caja | Comparativa ventas vs caja por medio de pago |
| Gestionar fondos | Caja efectivo, Nequi, Daviplata, banco, reserva |
| Transferir entre fondos | Ej: pasar de caja a banco |

---

## 8. Gastos

**Actors**: Administrador, Cajero

| Use Case | Description |
|---|---|
| Registrar gasto | Categoría, monto, proveedor opcional, comprobante |
| Gestionar categorías de gasto | Servicios, arriendo, nómina, insumos, etc. |
| Ver gastos por período | Filtrar por fecha, categoría, medio de pago |
| Exportar gastos | En Excel para declaraciones |

---

## 9. Reportes

**Actors**: Administrador

### Reportes DIAN (mipymes)

| Reporte | Descripción |
|---|---|
| Libro de ventas | Resumen de facturas por período (IVA desglosado) |
| Libro de compras | Compras con IVA descontable |
| Informe de IVA | IVA generado vs. IVA descontable por período |
| Retención en la fuente | Si aplica según régimen |
| Informe de ingresos | Base para declaración de renta simplificada |

### Reportes operativos

| Reporte | Descripción |
|---|---|
| Ventas por período | Por día/semana/mes, por vendedor, por producto |
| Top productos | Más vendidos por cantidad y por valor |
| Rotación de inventario | Días de inventario por producto |
| Kardex valorizado | Costo promedio ponderado |
| Antigüedad de cartera | Por cliente y por rango de días |
| Cuadres de caja | Histórico por turno y cajero |
| Rentabilidad por producto | Margen bruto |

---

## 10. Configuración

**Actors**: Administrador

| Use Case | Description |
|---|---|
| Configurar empresa | NIT, razón social, régimen, logo, datos DIAN |
| Gestionar resolución DIAN | Prefijo, rango, fechas de vigencia |
| Gestionar usuarios | Crear, editar roles, desactivar |
| Gestionar roles y permisos | Asignar módulos por rol |
| Configurar tarifas IVA | 0%, 5%, 19%, INC |
| Configurar medios de pago | Activar/desactivar, datos de cuenta |
| Gestionar cajas | Nombre, ubicación, estado |
| Parámetros del sistema | Moneda, zona horaria, impresora por defecto |
