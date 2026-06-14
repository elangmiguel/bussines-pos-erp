/** Format a number as Colombian Peso */
export function formatCOP(amount: number): string {
  return new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

/** Format a number as COP without currency symbol */
export function formatNum(amount: number): string {
  return new Intl.NumberFormat('es-CO', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount)
}

/** Calculate IVA amount given a base and percentage */
export function calcularIva(base: number, porcentaje: number): number {
  return Math.round(base * (porcentaje / 100))
}

/** Calculate price with IVA included */
export function precioConIva(precio: number, porcentaje: number): number {
  return Math.round(precio * (1 + porcentaje / 100))
}

/** Parse a COP-formatted string to number */
export function parseCOP(value: string): number {
  return Number(value.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0
}

/** Format date as dd/MM/yyyy (Colombian standard) */
export function formatFecha(dateStr: string): string {
  const d = new Date(dateStr)
  return new Intl.DateTimeFormat('es-CO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  }).format(d)
}

/** Format datetime as dd/MM/yyyy HH:mm */
export function formatFechaHora(dateStr: string): string {
  const d = new Date(dateStr)
  return new Intl.DateTimeFormat('es-CO', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(d)
}
