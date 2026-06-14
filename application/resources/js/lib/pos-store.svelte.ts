import type { Cliente, MedioPago } from '@/types/models';

export interface CartItem {
    producto_id: number;
    codigo: string;
    nombre: string;
    cantidad: number;
    precio_unitario: number;
    descuento_pct: number;
    tarifa_porcentaje: number;
    tarifa_tipo: string;
    subtotal: number;
    iva: number;
    total: number;
}

export interface PagoItem {
    medio_pago_id: number;
    nombre: string;
    tipo: string;
    monto: number;
    referencia: string;
}

function calcularLinea(
    cantidad: number,
    precio_unitario: number,
    descuento_pct: number,
    tarifa_tipo: string,
    tarifa_porcentaje: number,
): { subtotal: number; iva: number; total: number } {
    const subtotal = Math.round(cantidad * precio_unitario * (1 - descuento_pct / 100) * 100) / 100;
    let iva = 0;
    if (tarifa_tipo === 'iva' || tarifa_tipo === 'inc') {
        iva = Math.round(subtotal * (tarifa_porcentaje / 100) * 100) / 100;
    }
    return { subtotal, iva, total: subtotal + iva };
}

function crearCarrito() {
    let items = $state<CartItem[]>([]);
    let cliente = $state<Cliente | null>(null);
    let tipoPago = $state<'contado' | 'credito'>('contado');
    let plazoDias = $state<number>(30);
    let descuentoGlobal = $state<number>(0);
    let pagos = $state<PagoItem[]>([]);
    let observaciones = $state<string>('');

    const subtotal = $derived(items.reduce((s, i) => s + i.subtotal, 0));
    const totalIva = $derived(items.reduce((s, i) => s + i.iva, 0));
    const total = $derived(subtotal + totalIva - descuentoGlobal);
    const totalPagado = $derived(pagos.reduce((s, p) => s + p.monto, 0));
    const cambio = $derived(Math.max(0, totalPagado - total));
    const faltante = $derived(Math.max(0, total - totalPagado));
    const puedeFacturar = $derived(items.length > 0 && cliente !== null);

    function agregarProducto(producto: {
        id: number;
        codigo: string;
        nombre: string;
        precio_venta: number;
        tarifa_iva: { tipo: string; porcentaje: number } | null;
    }): void {
        const tarifa_tipo = producto.tarifa_iva?.tipo ?? 'excluido';
        const tarifa_porcentaje = producto.tarifa_iva?.porcentaje ?? 0;

        const existingIndex = items.findIndex((i) => i.producto_id === producto.id);

        if (existingIndex >= 0) {
            const existing = items[existingIndex];
            const nuevaCantidad = existing.cantidad + 1;
            const calc = calcularLinea(
                nuevaCantidad,
                existing.precio_unitario,
                existing.descuento_pct,
                existing.tarifa_tipo,
                existing.tarifa_porcentaje,
            );
            items[existingIndex] = {
                ...existing,
                cantidad: nuevaCantidad,
                ...calc,
            };
        } else {
            const calc = calcularLinea(1, producto.precio_venta, 0, tarifa_tipo, tarifa_porcentaje);
            items.push({
                producto_id: producto.id,
                codigo: producto.codigo,
                nombre: producto.nombre,
                cantidad: 1,
                precio_unitario: producto.precio_venta,
                descuento_pct: 0,
                tarifa_tipo,
                tarifa_porcentaje,
                ...calc,
            });
        }
    }

    function quitarItem(index: number): void {
        items.splice(index, 1);
    }

    function actualizarCantidad(index: number, cantidad: number): void {
        if (cantidad <= 0) {
            items.splice(index, 1);
            return;
        }
        const item = items[index];
        const calc = calcularLinea(
            cantidad,
            item.precio_unitario,
            item.descuento_pct,
            item.tarifa_tipo,
            item.tarifa_porcentaje,
        );
        items[index] = { ...item, cantidad, ...calc };
    }

    function actualizarPrecio(index: number, precio: number): void {
        const item = items[index];
        const calc = calcularLinea(
            item.cantidad,
            precio,
            item.descuento_pct,
            item.tarifa_tipo,
            item.tarifa_porcentaje,
        );
        items[index] = { ...item, precio_unitario: precio, ...calc };
    }

    function actualizarDescuento(index: number, pct: number): void {
        const item = items[index];
        const calc = calcularLinea(
            item.cantidad,
            item.precio_unitario,
            pct,
            item.tarifa_tipo,
            item.tarifa_porcentaje,
        );
        items[index] = { ...item, descuento_pct: pct, ...calc };
    }

    function agregarPago(pago: PagoItem): void {
        const existingIndex = pagos.findIndex((p) => p.medio_pago_id === pago.medio_pago_id);
        if (existingIndex >= 0) {
            pagos[existingIndex] = pago;
        } else {
            pagos.push(pago);
        }
    }

    function quitarPago(index: number): void {
        pagos.splice(index, 1);
    }

    function limpiar(): void {
        items.splice(0, items.length);
        pagos.splice(0, pagos.length);
        cliente = null;
        tipoPago = 'contado';
        plazoDias = 30;
        descuentoGlobal = 0;
        observaciones = '';
    }

    return {
        get items() { return items; },
        get cliente() { return cliente; },
        set cliente(v: Cliente | null) { cliente = v; },
        get tipoPago() { return tipoPago; },
        set tipoPago(v: 'contado' | 'credito') { tipoPago = v; },
        get plazoDias() { return plazoDias; },
        set plazoDias(v: number) { plazoDias = v; },
        get descuentoGlobal() { return descuentoGlobal; },
        set descuentoGlobal(v: number) { descuentoGlobal = v; },
        get pagos() { return pagos; },
        get observaciones() { return observaciones; },
        set observaciones(v: string) { observaciones = v; },
        get subtotal() { return subtotal; },
        get totalIva() { return totalIva; },
        get total() { return total; },
        get totalPagado() { return totalPagado; },
        get cambio() { return cambio; },
        get faltante() { return faltante; },
        get puedeFacturar() { return puedeFacturar; },
        agregarProducto,
        quitarItem,
        actualizarCantidad,
        actualizarPrecio,
        actualizarDescuento,
        agregarPago,
        quitarPago,
        limpiar,
    };
}

export const carrito = crearCarrito();
