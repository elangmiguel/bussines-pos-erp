export interface TarifaIva {
  id: number
  nombre: string
  tipo: 'iva' | 'inc' | 'excluido' | 'exento'
  porcentaje: number
  activo: boolean
}

export interface UnidadMedida {
  id: number
  nombre: string
  abreviatura: string
}

export interface CategoriaProducto {
  id: number
  parent_id: number | null
  nombre: string
  descripcion: string | null
  activo: boolean
  children?: CategoriaProducto[]
}

export interface Persona {
  id: number
  tipo_identificacion: 'CC' | 'CE' | 'TI' | 'PAS' | 'NIT' | 'RC'
  numero_identificacion: string
  digito_verificacion: string | null
  nombres: string
  apellidos: string | null
  email: string | null
  telefono: string | null
  celular: string | null
  direccion: string | null
  ciudad: string | null
  departamento: string | null
  pais: string
  nombre_completo: string
}

export interface Empresa {
  id: number
  razon_social: string
  nit: string
  digito_verificacion: string
  regimen_tributario: 'responsable_iva' | 'no_responsable_iva'
  tipo_empresa: string
  email: string | null
  telefono: string | null
  direccion: string | null
  ciudad: string | null
  nit_completo: string
}

export interface Rol {
  id: number
  nombre: 'administrador' | 'vendedor' | 'cajero' | 'bodeguero'
  descripcion: string | null
  permisos: Record<string, boolean>
}

export interface User {
  id: number
  name: string
  email: string
  activo: boolean
  ultimo_acceso: string | null
  persona?: Persona
  rol?: Rol
  display_name: string
}

export interface MedioPago {
  id: number
  nombre: string
  tipo:
    | 'efectivo'
    | 'tarjeta_credito'
    | 'tarjeta_debito'
    | 'transferencia'
    | 'nequi'
    | 'daviplata'
    | 'cheque'
    | 'credito'
    | 'otro'
  activo: boolean
}

export interface Fondo {
  id: number
  nombre: string
  tipo: 'caja' | 'digital' | 'banco' | 'reserva' | 'otro'
  saldo_actual: number
  activo: boolean
  medio_pago?: MedioPago
}

export interface Producto {
  id: number
  codigo: string
  codigo_barras: string | null
  nombre: string
  descripcion: string | null
  categoria_id: number | null
  unidad_medida_id: number
  tarifa_iva_id: number
  precio_compra: number
  precio_venta: number
  precio_mayorista: number | null
  stock_actual: number
  stock_minimo: number
  stock_maximo: number | null
  activo: boolean
  categoria?: CategoriaProducto
  unidad_medida?: UnidadMedida
  tarifa_iva?: TarifaIva
  precio_con_iva: number
}

export interface Cliente {
  id: number
  tipo: 'natural' | 'juridico'
  tipo_cliente: 'regular' | 'frecuente' | 'corporativo'
  credito_activo: boolean
  limite_credito: number
  plazo_dias: number
  activo: boolean
  persona?: Persona
  empresa?: Empresa
  nombre: string
  identificacion: string
}

export interface Proveedor {
  id: number
  tipo: 'natural' | 'juridico'
  condiciones_pago: string | null
  plazo_dias: number
  activo: boolean
  persona?: Persona
  empresa?: Empresa
  nombre: string
}

export interface ResolucionDian {
  id: number
  numero_resolucion: string
  prefijo: string | null
  rango_desde: number
  rango_hasta: number
  numero_actual: number
  fecha_inicio: string
  fecha_fin: string
  clave_tecnica: string | null
  activo: boolean
}

export interface Factura {
  id: number
  numero: number
  prefijo: string | null
  numero_completo: string
  fecha: string
  tipo_pago: 'contado' | 'credito'
  subtotal: number
  iva_5: number
  iva_19: number
  total: number
  estado: 'borrador' | 'emitida' | 'anulada'
  estado_dian: 'pendiente' | 'aceptada' | 'rechazada'
  cufe: string | null
  cliente?: Cliente
  user?: User
}

export interface DetalleFactura {
  id: number
  factura_id: number
  producto_id: number
  descripcion: string
  cantidad: number
  precio_unitario: number
  descuento_pct: number
  subtotal: number
  iva: number
  total: number
  producto?: Producto
  tarifa_iva?: TarifaIva
}

export interface PagoFactura {
  id: number
  factura_id: number
  medio_pago_id: number
  monto: number
  referencia: string | null
  medio_pago?: MedioPago
}

export interface CuentaPorCobrar {
  id: number
  cliente_id: number
  factura_id: number
  monto_total: number
  monto_pagado: number
  fecha_vencimiento: string
  estado: 'pendiente' | 'parcial' | 'pagada' | 'vencida'
  saldo: number
  cliente?: Cliente
  factura?: Factura
}

export interface Caja {
  id: number
  nombre: string
  ubicacion: string | null
  activo: boolean
  turno_activo?: TurnoCaja | null
}

export interface TurnoCaja {
  id: number
  caja_id: number
  cajero_id: number
  saldo_inicial: number
  saldo_final: number | null
  apertura: string
  cierre: string | null
  estado: 'abierto' | 'cerrado'
  caja?: Caja
}

export interface OrdenCompra {
  id: number
  proveedor_id: number
  estado: 'borrador' | 'enviada' | 'recibida_parcial' | 'recibida' | 'cancelada'
  fecha: string
  subtotal: number
  iva: number
  total: number
  proveedor?: Proveedor
}

export interface Configuracion {
  id: number
  empresa_id: number
  logo: string | null
  moneda: string
  zona_horaria: string
  dias_vencimiento_cred: number
  prefijo_nota_credito: string
  prefijo_nota_debito: string
  empresa?: Empresa
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
  links: { url: string | null; label: string; active: boolean }[]
}
