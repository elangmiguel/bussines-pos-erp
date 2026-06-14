<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AlertTriangleIcon from 'lucide-svelte/icons/alert-triangle';
    import CheckCircleIcon from 'lucide-svelte/icons/check-circle';
    import XIcon from 'lucide-svelte/icons/x';
    import XCircleIcon from 'lucide-svelte/icons/x-circle';
    import { onDestroy } from 'svelte';

    interface FlashEntry {
        id: number;
        type: 'success' | 'error' | 'warning';
        message: string;
        visible: boolean;
    }

    let messages = $state<FlashEntry[]>([]);
    let nextId = 0;
    let timers: ReturnType<typeof setTimeout>[] = [];

    function addMessage(type: 'success' | 'error' | 'warning', message: string) {
        const id = ++nextId;
        messages.push({ id, type, message, visible: false });

        // Trigger slide-in on next tick
        requestAnimationFrame(() => {
            const idx = messages.findIndex((m) => m.id === id);
            if (idx !== -1) messages[idx].visible = true;
        });

        const timer = setTimeout(() => dismiss(id), 4000);
        timers.push(timer);
    }

    function dismiss(id: number) {
        const idx = messages.findIndex((m) => m.id === id);
        if (idx !== -1) {
            messages[idx].visible = false;
            setTimeout(() => {
                messages = messages.filter((m) => m.id !== id);
            }, 300);
        }
    }

    let prevFlash = { success: '', error: '', warning: '' };

    $effect(() => {
        const flash = $page.props.flash ?? {};
        const success = flash.success ?? '';
        const error = flash.error ?? '';
        const warning = flash.warning ?? '';

        if (success && success !== prevFlash.success) {
            addMessage('success', success);
        }
        if (error && error !== prevFlash.error) {
            addMessage('error', error);
        }
        if (warning && warning !== prevFlash.warning) {
            addMessage('warning', warning);
        }

        prevFlash = { success, error, warning };
    });

    onDestroy(() => {
        timers.forEach(clearTimeout);
    });

    function iconFor(type: 'success' | 'error' | 'warning') {
        if (type === 'success') return CheckCircleIcon;
        if (type === 'error') return XCircleIcon;
        return AlertTriangleIcon;
    }

    function bgFor(type: 'success' | 'error' | 'warning'): string {
        if (type === 'success') return 'bg-green-600 dark:bg-green-700';
        if (type === 'error') return 'bg-red-600 dark:bg-red-700';
        return 'bg-yellow-500 dark:bg-yellow-600';
    }
</script>

<div
    class="pointer-events-none fixed right-4 top-4 z-50 flex flex-col gap-2"
    aria-live="polite"
>
    {#each messages as msg (msg.id)}
        {@const Icon = iconFor(msg.type)}
        <div
            class="pointer-events-auto flex w-80 max-w-full items-start gap-3 rounded-lg px-4 py-3 text-white shadow-lg transition-all duration-300 {bgFor(msg.type)} {msg.visible ? 'translate-x-0 opacity-100' : 'translate-x-full opacity-0'}"
            role="alert"
        >
            <Icon class="mt-0.5 size-4 shrink-0" />
            <p class="flex-1 text-sm leading-snug">{msg.message}</p>
            <button
                type="button"
                class="shrink-0 rounded p-0.5 opacity-80 transition-opacity hover:opacity-100"
                onclick={() => dismiss(msg.id)}
                aria-label="Cerrar"
            >
                <XIcon class="size-3.5" />
            </button>
        </div>
    {/each}
</div>
