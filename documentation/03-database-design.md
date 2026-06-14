# Database Design

**Engine**: PostgreSQL
**Encoding**: UTF-8
**Collation**: es-CO (Spanish Colombia)
**Monetary values**: `decimal(15,2)` in COP
**Timestamps**: `created_at`, `updated_at` on all tables; `deleted_at` on financial/master entities

---

## Entity Relationship Overview

```
personas ──────────────────────── usuarios ─── roles
    │                                 │
    ├── clientes                  colaboradores
    └── (representante) empresas      │
              │                   cajeros
              └── proveedores
                       │
                  proveedores_productos ── productos ── categorias_producto
                                              │              │
                                         tarifas_iva   unidades_medida
                                              │
                                    movimientos_inventario

cajas ── turnos_caja ── facturas ──── detalles_factura ── productos
                │           │
         medios_pago    pagos_factura
                │           │
            fondos      notas_credito / notas_debito
                │
          movimientos_fondo

ordenes_compra ── detalles_orden ── productos
       │
recepciones_mercancia ── detalles_recepcion

facturas ── cuentas_por_cobrar ── abonos_cartera
ordenes_compra ── cuentas_por_pagar

gastos ── categorias_gasto
resoluciones_dian
configuracion
```

---

## Tables

### `personas`
Individuals — base for clients, users, collaborators, and natural-person suppliers.

```sql
id                    BIGSERIAL PK
tipo_identificacion   ENUM('CC','CE','TI','PAS','NIT') NOT NULL
numero_identificacion VARCHAR(20) NOT NULL
digito_verificacion   CHAR(1)                            -- NIT only
nombres               VARCHAR(100) NOT NULL
apellidos             VARCHAR(100)                        -- NULL for NIT persons
email                 VARCHAR(150)
telefono              VARCHAR(20)
celular               VARCHAR(20)
direccion             TEXT
ciudad                VARCHAR(100)
departamento          VARCHAR(100)
pais                  CHAR(2) DEFAULT 'CO'
created_at, updated_at, deleted_at
UNIQUE(tipo_identificacion, numero_identificacion)
```

### `empresas`
Legal entities — for the business itself, corporate clients, and corporate suppliers.

```sql
id                    BIGSERIAL PK
razon_social          VARCHAR(200) NOT NULL
nit                   VARCHAR(15) NOT NULL UNIQUE
digito_verificacion   CHAR(1) NOT NULL
regimen_tributario    ENUM('responsable_iva','no_responsable_iva') NOT NULL
tipo_empresa          ENUM('propia','cliente','proveedor','mixta') NOT NULL
representante_id      FK → personas
email                 VARCHAR(150)
telefono              VARCHAR(20)
direccion             TEXT
ciudad                VARCHAR(100)
departamento          VARCHAR(100)
pais                  CHAR(2) DEFAULT 'CO'
activo                BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
```

### `roles`

```sql
id           BIGSERIAL PK
nombre       VARCHAR(50) NOT NULL UNIQUE  -- administrador, vendedor, cajero, bodeguero
descripcion  TEXT
permisos     JSONB NOT NULL DEFAULT '{}'  -- { "facturas.create": true, "reportes.dian": false }
created_at, updated_at
```

### `usuarios`

```sql
id                BIGSERIAL PK
persona_id        FK → personas NOT NULL
rol_id            FK → roles NOT NULL
email             VARCHAR(150) NOT NULL UNIQUE
password          VARCHAR(255) NOT NULL
activo            BOOLEAN DEFAULT TRUE
ultimo_acceso     TIMESTAMP
created_at, updated_at, deleted_at
```

### `colaboradores`

```sql
id                  BIGSERIAL PK
usuario_id          FK → usuarios NOT NULL UNIQUE
salario             DECIMAL(15,2)
turno               ENUM('manana','tarde','noche','completo')
fecha_contratacion  DATE
fecha_retiro        DATE
created_at, updated_at
```

### `cajeros`
Collaborators authorized to operate a cash register.

```sql
id              BIGSERIAL PK
colaborador_id  FK → colaboradores NOT NULL UNIQUE
codigo          VARCHAR(20) NOT NULL UNIQUE
activo          BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `clientes`

```sql
id              BIGSERIAL PK
tipo            ENUM('natural','juridico') NOT NULL
persona_id      FK → personas               -- natural persons
empresa_id      FK → empresas               -- legal entities
tipo_cliente    ENUM('regular','frecuente','corporativo') DEFAULT 'regular'
credito_activo  BOOLEAN DEFAULT FALSE
limite_credito  DECIMAL(15,2) DEFAULT 0
plazo_dias      SMALLINT DEFAULT 0
observaciones   TEXT
activo          BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
CHECK (persona_id IS NOT NULL OR empresa_id IS NOT NULL)
```

### `proveedores`

```sql
id                BIGSERIAL PK
tipo              ENUM('natural','juridico') NOT NULL
persona_id        FK → personas
empresa_id        FK → empresas
condiciones_pago  TEXT
plazo_dias        SMALLINT DEFAULT 0
activo            BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
CHECK (persona_id IS NOT NULL OR empresa_id IS NOT NULL)
```

### `categorias_producto`
Self-referential for hierarchical categories.

```sql
id          BIGSERIAL PK
parent_id   FK → categorias_producto (nullable)
nombre      VARCHAR(100) NOT NULL
descripcion TEXT
activo      BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `unidades_medida`

```sql
id         BIGSERIAL PK
nombre     VARCHAR(50) NOT NULL
abreviatura VARCHAR(10) NOT NULL UNIQUE  -- UN, KG, L, M, M2, CJ, etc.
created_at, updated_at
```

### `tarifas_iva`

```sql
id           BIGSERIAL PK
nombre       VARCHAR(50) NOT NULL     -- IVA 19%, IVA 5%, Excluido, Exento, INC
tipo         ENUM('iva','inc','excluido','exento') NOT NULL
porcentaje   DECIMAL(5,2) NOT NULL    -- 19.00, 5.00, 0.00
activo       BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `productos`

```sql
id               BIGSERIAL PK
codigo           VARCHAR(50) NOT NULL UNIQUE
codigo_barras    VARCHAR(50) UNIQUE
nombre           VARCHAR(200) NOT NULL
descripcion      TEXT
categoria_id     FK → categorias_producto
unidad_medida_id FK → unidades_medida NOT NULL
tarifa_iva_id    FK → tarifas_iva NOT NULL
precio_compra    DECIMAL(15,2) NOT NULL DEFAULT 0
precio_venta     DECIMAL(15,2) NOT NULL
precio_mayorista DECIMAL(15,2)
stock_actual     DECIMAL(15,3) NOT NULL DEFAULT 0
stock_minimo     DECIMAL(15,3) NOT NULL DEFAULT 0
stock_maximo     DECIMAL(15,3)
imagen           VARCHAR(255)
activo           BOOLEAN DEFAULT TRUE
created_at, updated_at, deleted_at
```

### `proveedores_productos`

```sql
id                BIGSERIAL PK
proveedor_id      FK → proveedores NOT NULL
producto_id       FK → productos NOT NULL
precio_compra     DECIMAL(15,2)
tiempo_entrega    SMALLINT              -- days
es_principal      BOOLEAN DEFAULT FALSE
created_at, updated_at
UNIQUE(proveedor_id, producto_id)
```

### `movimientos_inventario`
Full lifecycle tracking for every stock change.

```sql
id               BIGSERIAL PK
producto_id      FK → productos NOT NULL
tipo             ENUM('entrada_compra','salida_venta','ajuste_positivo',
                      'ajuste_negativo','devolucion_venta','devolucion_compra',
                      'traslado') NOT NULL
cantidad         DECIMAL(15,3) NOT NULL
stock_anterior   DECIMAL(15,3) NOT NULL
stock_nuevo      DECIMAL(15,3) NOT NULL
costo_unitario   DECIMAL(15,2)
referencia_tipo  VARCHAR(50)           -- 'factura', 'orden_compra', 'ajuste'
referencia_id    BIGINT
motivo           TEXT
usuario_id       FK → usuarios NOT NULL
created_at
```

### `medios_pago`

```sql
id          BIGSERIAL PK
nombre      VARCHAR(100) NOT NULL
tipo        ENUM('efectivo','tarjeta_credito','tarjeta_debito','transferencia',
                 'nequi','daviplata','cheque','credito','otro') NOT NULL
instrucciones TEXT                     -- account numbers, etc.
activo      BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `fondos`
Fund accounts that accumulate money by payment type.

```sql
id             BIGSERIAL PK
nombre         VARCHAR(100) NOT NULL
tipo           ENUM('caja','digital','banco','reserva','otro') NOT NULL
medio_pago_id  FK → medios_pago
saldo_actual   DECIMAL(15,2) NOT NULL DEFAULT 0
activo         BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `cajas`

```sql
id          BIGSERIAL PK
nombre      VARCHAR(100) NOT NULL
ubicacion   VARCHAR(200)
activo      BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `turnos_caja`

```sql
id              BIGSERIAL PK
caja_id         FK → cajas NOT NULL
cajero_id       FK → cajeros NOT NULL
saldo_inicial   DECIMAL(15,2) NOT NULL DEFAULT 0
saldo_final     DECIMAL(15,2)
apertura        TIMESTAMP NOT NULL
cierre          TIMESTAMP
estado          ENUM('abierto','cerrado') DEFAULT 'abierto'
observaciones   TEXT
created_at, updated_at
```

### `resoluciones_dian`

```sql
id                   BIGSERIAL PK
numero_resolucion    VARCHAR(50) NOT NULL
fecha_resolucion     DATE NOT NULL
fecha_inicio         DATE NOT NULL
fecha_fin            DATE NOT NULL
prefijo              VARCHAR(10)
rango_desde          INT NOT NULL
rango_hasta          INT NOT NULL
numero_actual        INT NOT NULL DEFAULT 0  -- auto-incremented per invoice
activo               BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `facturas`
Core sales document. DIAN-compliant.

```sql
id                  BIGSERIAL PK
numero              INT NOT NULL
prefijo             VARCHAR(10)
numero_completo     VARCHAR(30) NOT NULL UNIQUE   -- FV-001, etc.
resolucion_id       FK → resoluciones_dian NOT NULL
turno_caja_id       FK → turnos_caja NOT NULL
cliente_id          FK → clientes NOT NULL
usuario_id          FK → usuarios NOT NULL
fecha               TIMESTAMP NOT NULL
fecha_vencimiento   DATE                           -- for credit invoices
tipo                ENUM('venta','posventas') DEFAULT 'venta'
tipo_pago           ENUM('contado','credito') DEFAULT 'contado'
subtotal            DECIMAL(15,2) NOT NULL
descuento_global    DECIMAL(15,2) DEFAULT 0
base_iva_0          DECIMAL(15,2) DEFAULT 0
base_iva_5          DECIMAL(15,2) DEFAULT 0
base_iva_19         DECIMAL(15,2) DEFAULT 0
iva_5               DECIMAL(15,2) DEFAULT 0
iva_19              DECIMAL(15,2) DEFAULT 0
inc                 DECIMAL(15,2) DEFAULT 0
total               DECIMAL(15,2) NOT NULL
estado              ENUM('borrador','emitida','anulada') DEFAULT 'emitida'
cufe                VARCHAR(255)                   -- DIAN unique code
qr_data             TEXT                           -- QR content
xml_dian            TEXT                           -- full UBL 2.1 XML
estado_dian         ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente'
observaciones       TEXT
created_at, updated_at, deleted_at
```

### `detalles_factura`

```sql
id               BIGSERIAL PK
factura_id       FK → facturas NOT NULL
producto_id      FK → productos NOT NULL
descripcion      VARCHAR(300) NOT NULL
cantidad         DECIMAL(15,3) NOT NULL
precio_unitario  DECIMAL(15,2) NOT NULL
descuento_pct    DECIMAL(5,2) DEFAULT 0
tarifa_iva_id    FK → tarifas_iva NOT NULL
subtotal         DECIMAL(15,2) NOT NULL   -- cantidad * precio after discount
iva              DECIMAL(15,2) NOT NULL
total            DECIMAL(15,2) NOT NULL
created_at
```

### `pagos_factura`
Supports split payments.

```sql
id             BIGSERIAL PK
factura_id     FK → facturas NOT NULL
medio_pago_id  FK → medios_pago NOT NULL
monto          DECIMAL(15,2) NOT NULL
referencia     VARCHAR(100)              -- transaction ID, check number, etc.
created_at
```

### `notas_credito`

```sql
id               BIGSERIAL PK
numero           INT NOT NULL
numero_completo  VARCHAR(30) NOT NULL UNIQUE
factura_id       FK → facturas NOT NULL
usuario_id       FK → usuarios NOT NULL
fecha            TIMESTAMP NOT NULL
motivo           ENUM('devolucion','descuento','anulacion','error') NOT NULL
descripcion      TEXT
subtotal         DECIMAL(15,2) NOT NULL
iva              DECIMAL(15,2) NOT NULL
total            DECIMAL(15,2) NOT NULL
estado           ENUM('emitida','aplicada','anulada') DEFAULT 'emitida'
cufe             VARCHAR(255)
xml_dian         TEXT
estado_dian      ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente'
created_at, updated_at
```

### `notas_debito`

```sql
id               BIGSERIAL PK
numero           INT NOT NULL
numero_completo  VARCHAR(30) NOT NULL UNIQUE
factura_id       FK → facturas NOT NULL
usuario_id       FK → usuarios NOT NULL
fecha            TIMESTAMP NOT NULL
motivo           TEXT NOT NULL
subtotal         DECIMAL(15,2) NOT NULL
iva              DECIMAL(15,2) NOT NULL
total            DECIMAL(15,2) NOT NULL
estado           ENUM('emitida','aplicada','anulada') DEFAULT 'emitida'
cufe             VARCHAR(255)
xml_dian         TEXT
estado_dian      ENUM('pendiente','aceptada','rechazada') DEFAULT 'pendiente'
created_at, updated_at
```

### `cuentas_por_cobrar`

```sql
id                BIGSERIAL PK
cliente_id        FK → clientes NOT NULL
factura_id        FK → facturas NOT NULL UNIQUE
monto_total       DECIMAL(15,2) NOT NULL
monto_pagado      DECIMAL(15,2) NOT NULL DEFAULT 0
saldo             DECIMAL(15,2) GENERATED ALWAYS AS (monto_total - monto_pagado) STORED
fecha_vencimiento DATE NOT NULL
estado            ENUM('pendiente','parcial','pagada','vencida') DEFAULT 'pendiente'
created_at, updated_at
```

### `abonos_cartera`

```sql
id                   BIGSERIAL PK
cuenta_cobrar_id     FK → cuentas_por_cobrar NOT NULL
medio_pago_id        FK → medios_pago NOT NULL
monto                DECIMAL(15,2) NOT NULL
fecha                TIMESTAMP NOT NULL
usuario_id           FK → usuarios NOT NULL
observaciones        TEXT
created_at
```

### `ordenes_compra`

```sql
id              BIGSERIAL PK
proveedor_id    FK → proveedores NOT NULL
usuario_id      FK → usuarios NOT NULL
fecha           DATE NOT NULL
fecha_esperada  DATE
estado          ENUM('borrador','enviada','recibida_parcial','recibida','cancelada') DEFAULT 'borrador'
subtotal        DECIMAL(15,2) NOT NULL DEFAULT 0
iva             DECIMAL(15,2) NOT NULL DEFAULT 0
total           DECIMAL(15,2) NOT NULL DEFAULT 0
observaciones   TEXT
created_at, updated_at
```

### `detalles_orden_compra`

```sql
id               BIGSERIAL PK
orden_id         FK → ordenes_compra NOT NULL
producto_id      FK → productos NOT NULL
cantidad         DECIMAL(15,3) NOT NULL
precio_unitario  DECIMAL(15,2) NOT NULL
subtotal         DECIMAL(15,2) NOT NULL
created_at
```

### `recepciones_mercancia`

```sql
id           BIGSERIAL PK
orden_id     FK → ordenes_compra NOT NULL
usuario_id   FK → usuarios NOT NULL
fecha        TIMESTAMP NOT NULL
observaciones TEXT
estado       ENUM('completa','parcial','con_novedad') NOT NULL
created_at, updated_at
```

### `detalles_recepcion`

```sql
id                  BIGSERIAL PK
recepcion_id        FK → recepciones_mercancia NOT NULL
producto_id         FK → productos NOT NULL
cantidad_esperada   DECIMAL(15,3) NOT NULL
cantidad_recibida   DECIMAL(15,3) NOT NULL
precio_unitario     DECIMAL(15,2) NOT NULL
novedad             TEXT
created_at
```

### `cuentas_por_pagar`

```sql
id                BIGSERIAL PK
proveedor_id      FK → proveedores NOT NULL
orden_id          FK → ordenes_compra NOT NULL
monto_total       DECIMAL(15,2) NOT NULL
monto_pagado      DECIMAL(15,2) NOT NULL DEFAULT 0
saldo             DECIMAL(15,2) GENERATED ALWAYS AS (monto_total - monto_pagado) STORED
fecha_vencimiento DATE NOT NULL
estado            ENUM('pendiente','parcial','pagada','vencida') DEFAULT 'pendiente'
created_at, updated_at
```

### `categorias_gasto`

```sql
id          BIGSERIAL PK
nombre      VARCHAR(100) NOT NULL
descripcion TEXT
activo      BOOLEAN DEFAULT TRUE
created_at, updated_at
```

### `gastos`

```sql
id                BIGSERIAL PK
categoria_id      FK → categorias_gasto NOT NULL
descripcion       VARCHAR(300) NOT NULL
monto             DECIMAL(15,2) NOT NULL
iva               DECIMAL(15,2) DEFAULT 0
fecha             DATE NOT NULL
proveedor_id      FK → proveedores
medio_pago_id     FK → medios_pago NOT NULL
usuario_id        FK → usuarios NOT NULL
numero_documento  VARCHAR(50)            -- vendor invoice number
comprobante       VARCHAR(255)           -- file path
created_at, updated_at
```

### `movimientos_fondo`

```sql
id             BIGSERIAL PK
fondo_id       FK → fondos NOT NULL
tipo           ENUM('ingreso','egreso') NOT NULL
monto          DECIMAL(15,2) NOT NULL
descripcion    TEXT NOT NULL
referencia_tipo VARCHAR(50)             -- 'factura', 'gasto', 'traslado'
referencia_id  BIGINT
usuario_id     FK → usuarios NOT NULL
created_at
```

### `configuracion`

```sql
id                     BIGSERIAL PK
empresa_id             FK → empresas NOT NULL
logo                   VARCHAR(255)
moneda                 CHAR(3) DEFAULT 'COP'
zona_horaria           VARCHAR(50) DEFAULT 'America/Bogota'
impresora_defecto      VARCHAR(100)
dias_vencimiento_cred  SMALLINT DEFAULT 30
prefijo_nota_credito   VARCHAR(10) DEFAULT 'NC'
prefijo_nota_debito    VARCHAR(10) DEFAULT 'ND'
created_at, updated_at
```

---

## Migration Order

Dependencies must be created in this order:

1. `tarifas_iva`
2. `unidades_medida`
3. `categorias_producto`
4. `personas`
5. `empresas`
6. `roles`
7. `usuarios`
8. `colaboradores`
9. `cajeros`
10. `clientes`
11. `proveedores`
12. `productos`
13. `proveedores_productos`
14. `medios_pago`
15. `fondos`
16. `cajas`
17. `categorias_gasto`
18. `resoluciones_dian`
19. `configuracion`
20. `turnos_caja`
21. `facturas`
22. `detalles_factura`
23. `pagos_factura`
24. `notas_credito`
25. `notas_debito`
26. `cuentas_por_cobrar`
27. `abonos_cartera`
28. `ordenes_compra`
29. `detalles_orden_compra`
30. `recepciones_mercancia`
31. `detalles_recepcion`
32. `cuentas_por_pagar`
33. `gastos`
34. `movimientos_inventario`
35. `movimientos_fondo`
