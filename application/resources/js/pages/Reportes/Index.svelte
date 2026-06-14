<script lang="ts">
    import {
        BarChart2,
        Calculator,
        CreditCard,
        FileSpreadsheet,
        FileText,
        Package,
        TrendingUp,
    } from 'lucide-svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AppLayout from '@/layouts/AppLayout.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card';
    import type { BreadcrumbItem } from '@/types';

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Reportes', href: '/reportes' },
    ];

    interface ReporteCard {
        titulo: string;
        descripcion: string;
        href: string;
        icon: typeof BarChart2;
        color: string;
    }

    const reportesOperativos: ReporteCard[] = [
        {
            titulo: 'Ventas',
            descripcion: 'Análisis de ventas por período, top productos vendidos y KPIs de facturación.',
            href: '/reportes/ventas',
            icon: BarChart2,
            color: 'text-blue-600',
        },
        {
            titulo: 'Inventario',
            descripcion: 'Estado actual del stock, alertas de bajo inventario y kardex de movimientos por producto.',
            href: '/reportes/inventario',
            icon: Package,
            color: 'text-emerald-600',
        },
        {
            titulo: 'Cartera',
            descripcion: 'Antigüedad de cartera, cuentas por cobrar pendientes y análisis de mora.',
            href: '/reportes/cartera',
            icon: CreditCard,
            color: 'text-amber-600',
        },
        {
            titulo: 'Rentabilidad',
            descripcion: 'Margen bruto por producto, utilidad del período e indicadores de rentabilidad.',
            href: '/reportes/rentabilidad',
            icon: TrendingUp,
            color: 'text-violet-600',
        },
    ];

    const reportesDian: ReporteCard[] = [
        {
            titulo: 'Libro de Ventas',
            descripcion: 'Registro de facturas emitidas con bases gravables, IVA generado e INC para declaración tributaria.',
            href: '/reportes/dian/libro-ventas',
            icon: FileText,
            color: 'text-rose-600',
        },
        {
            titulo: 'Libro de Compras',
            descripcion: 'Gastos y órdenes de compra con IVA descontable para la declaración bimestral/cuatrimestral.',
            href: '/reportes/dian/libro-compras',
            icon: FileSpreadsheet,
            color: 'text-orange-600',
        },
        {
            titulo: 'Resumen IVA',
            descripcion: 'Cuadro comparativo de IVA generado vs IVA descontable y saldo a pagar o favor.',
            href: '/reportes/dian/resumen-iva',
            icon: Calculator,
            color: 'text-teal-600',
        },
    ];
</script>

<AppHead title="Reportes" />

<AppLayout {breadcrumbs}>
    <div class="flex flex-col gap-8 p-6">
        <!-- Encabezado -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight">Reportes</h1>
            <p class="mt-1 text-muted-foreground">
                Consulta y exporta reportes operativos y fiscales de tu negocio.
            </p>
        </div>

        <!-- Reportes Operativos -->
        <section>
            <h2 class="mb-4 text-lg font-semibold">Operativos</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                {#each reportesOperativos as reporte (reporte.href)}
                    <Card class="group transition-shadow hover:shadow-md">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                                    <reporte.icon class="size-5 {reporte.color}" />
                                </div>
                                <CardTitle class="text-base">{reporte.titulo}</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <CardDescription class="text-sm leading-relaxed">
                                {reporte.descripcion}
                            </CardDescription>
                            <Button
                                href={reporte.href}
                                variant="outline"
                                size="sm"
                                class="w-full"
                            >
                                Abrir reporte
                            </Button>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        </section>

        <!-- Reportes DIAN / Fiscales -->
        <section>
            <h2 class="mb-1 text-lg font-semibold">DIAN / Fiscales</h2>
            <p class="mb-4 text-sm text-muted-foreground">
                Reportes para obligaciones tributarias. Exportables en CSV para presentación ante la DIAN.
            </p>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                {#each reportesDian as reporte (reporte.href)}
                    <Card class="group transition-shadow hover:shadow-md">
                        <CardHeader class="pb-3">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-muted">
                                    <reporte.icon class="size-5 {reporte.color}" />
                                </div>
                                <CardTitle class="text-base">{reporte.titulo}</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent class="flex flex-col gap-4">
                            <CardDescription class="text-sm leading-relaxed">
                                {reporte.descripcion}
                            </CardDescription>
                            <Button
                                href={reporte.href}
                                variant="outline"
                                size="sm"
                                class="w-full"
                            >
                                Abrir reporte
                            </Button>
                        </CardContent>
                    </Card>
                {/each}
            </div>
        </section>
    </div>
</AppLayout>
