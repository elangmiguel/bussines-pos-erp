<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
    import FlashMessage from '@/components/shared/FlashMessage.svelte';
    import StockAlertListener from '@/components/shared/StockAlertListener.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppLayout {breadcrumbs}>
    {@render children?.()}
</AppLayout>

<FlashMessage />
<StockAlertListener />
