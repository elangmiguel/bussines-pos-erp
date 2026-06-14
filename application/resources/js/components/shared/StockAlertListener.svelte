<script lang="ts">
    import { onDestroy, onMount } from 'svelte';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import X from 'lucide-svelte/icons/x';

    interface StockAlerta {
        id: string;
        producto_id: number;
        codigo: string;
        nombre: string;
        stock_actual: number;
        stock_minimo: number;
        timestamp: number;
    }

    let alertas = $state<StockAlerta[]>([]);
    let channel: ReturnType<typeof window.Echo.channel> | null = null;

    onMount(() => {
        // Lazy-import Echo to avoid SSR issues
        import('@/lib/echo').then(() => {
            channel = window.Echo.channel('stock-alertas');

            channel.listen('.stock.bajo', (data: Omit<StockAlerta, 'id' | 'timestamp'>) => {
                const id = `${data.producto_id}-${Date.now()}`;

                // Avoid duplicate alerts for same product within 30 seconds
                const existingIndex = alertas.findIndex((a) => a.producto_id === data.producto_id);
                if (existingIndex >= 0) {
                    alertas.splice(existingIndex, 1);
                }

                alertas.push({ ...data, id, timestamp: Date.now() });

                // Auto-dismiss after 10 seconds
                setTimeout(() => {
                    const idx = alertas.findIndex((a) => a.id === id);
                    if (idx >= 0) alertas.splice(idx, 1);
                }, 10000);
            });
        });
    });

    onDestroy(() => {
        if (channel) {
            window.Echo.leaveChannel('stock-alertas');
        }
    });

    function dismiss(id: string) {
        const idx = alertas.findIndex((a) => a.id === id);
        if (idx >= 0) alertas.splice(idx, 1);
    }
</script>

{#if alertas.length > 0}
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2" role="alert" aria-live="polite">
        {#each alertas as alerta (alerta.id)}
            <div
                class="flex w-80 items-start gap-3 rounded-lg border border-orange-300 bg-orange-50 p-3 shadow-lg dark:border-orange-700 dark:bg-orange-900/30"
            >
                <div class="mt-0.5 shrink-0">
                    <AlertTriangle class="size-4 text-orange-600 dark:text-orange-400" />
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-orange-900 dark:text-orange-200">
                        Stock bajo: {alerta.nombre}
                    </p>
                    <p class="mt-0.5 text-xs text-orange-700 dark:text-orange-300">
                        [{alerta.codigo}] Stock actual: <strong>{alerta.stock_actual}</strong> · Mínimo: {alerta.stock_minimo}
                    </p>
                    <a
                        href="/inventario/productos/{alerta.producto_id}"
                        class="mt-1 inline-block text-xs font-medium text-orange-700 underline hover:text-orange-900 dark:text-orange-400 dark:hover:text-orange-200"
                    >
                        Ver producto →
                    </a>
                </div>
                <button
                    type="button"
                    onclick={() => dismiss(alerta.id)}
                    class="shrink-0 rounded p-0.5 text-orange-500 hover:bg-orange-100 hover:text-orange-700 dark:hover:bg-orange-800"
                    aria-label="Cerrar"
                >
                    <X class="size-3.5" />
                </button>
            </div>
        {/each}
    </div>
{/if}
