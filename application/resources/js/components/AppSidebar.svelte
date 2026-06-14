<script lang="ts">
    import { page, Link, router } from '@inertiajs/svelte';
    import BarChart2 from 'lucide-svelte/icons/bar-chart-2';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import CreditCard from 'lucide-svelte/icons/credit-card';
    import FileText from 'lucide-svelte/icons/file-text';
    import LayoutDashboard from 'lucide-svelte/icons/layout-dashboard';
    import LogOut from 'lucide-svelte/icons/log-out';
    import Package from 'lucide-svelte/icons/package';
    import Settings from 'lucide-svelte/icons/settings';
    import ShoppingCart from 'lucide-svelte/icons/shopping-cart';
    import Truck from 'lucide-svelte/icons/truck';
    import Users from 'lucide-svelte/icons/users';
    import AppLogoIcon from '@/components/AppLogoIcon.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { getInitials } from '@/lib/initials';
    import { currentUrlState } from '@/lib/currentUrl';
    import { logout } from '@/routes';

    const { currentUrl, isCurrentOrParentUrl } = currentUrlState();

    const user = $derived($page.props.auth.user);
    const empresa = $derived($page.props.empresa?.empresa);
    const turnoActivo = $derived($page.props.turno_activo);

    const displayName = $derived(user?.display_name ?? user?.name ?? '');
    const userInitials = $derived(getInitials(displayName));

    function isActive(href: string): boolean {
        return isCurrentOrParentUrl(href, $currentUrl);
    }

    function isExact(href: string): boolean {
        return $currentUrl === href;
    }

    interface NavChild {
        title: string;
        href: string;
        badge?: boolean;
    }

    interface NavGroup {
        label: string;
        items: NavChild[];
    }

    const navGroups: NavGroup[] = [
        {
            label: 'VENTAS',
            items: [
                { title: 'Punto de Venta', href: '/pos', badge: true },
                { title: 'Facturación', href: '/facturacion' },
            ],
        },
        {
            label: 'INVENTARIO',
            items: [
                { title: 'Productos', href: '/inventario/productos' },
                { title: 'Categorías', href: '/inventario/categorias' },
            ],
        },
        {
            label: 'CLIENTES',
            items: [
                { title: 'Directorio', href: '/clientes' },
                { title: 'Cartera', href: '/cartera' },
            ],
        },
        {
            label: 'COMPRAS (WIP)',
            items: [
                { title: 'Proveedores', href: '/proveedores' },
                { title: 'Órdenes de Compra', href: '/compras/ordenes' },
            ],
        },
        {
            label: 'FINANZAS',
            items: [
                { title: 'Caja & Fondos', href: '/caja' },
                { title: 'Gastos', href: '/gastos' },
            ],
        },
        {
            label: 'REPORTES',
            items: [
                { title: 'Operativos', href: '/reportes' },
                { title: 'DIAN', href: '/reportes/dian/libro-ventas' },
            ],
        },
        {
            label: 'CONFIGURACIÓN',
            items: [
                { title: 'Empresa', href: '/settings/empresa' },
                { title: 'Usuarios', href: '/settings/usuarios' },
                { title: 'Resolución DIAN', href: '/settings/dian' },
                { title: 'Perfil', href: '/settings/profile' },
            ],
        },
    ];

    // Track collapsed groups — default all expanded
    let collapsed = $state<Record<string, boolean>>({});

    function toggleGroup(label: string) {
        collapsed[label] = !collapsed[label];
    }

    function isGroupOpen(label: string): boolean {
        return !collapsed[label];
    }

    function groupIcon(label: string) {
        switch (label) {
            case 'VENTAS': return ShoppingCart;
            case 'INVENTARIO': return Package;
            case 'CLIENTES': return Users;
            case 'COMPRAS': return Truck;
            case 'FINANZAS': return CreditCard;
            case 'REPORTES': return BarChart2;
            case 'CONFIGURACIÓN': return Settings;
            default: return FileText;
        }
    }

    function handleLogout() {
        router.flushAll();
    }
</script>

<Sidebar collapsible="icon" variant="inset">
    <!-- Header: Logo + App Name + Empresa -->
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg" asChild>
                    {#snippet children(props)}
                        <Link
                            {...props}
                            href="/dashboard"
                            class={props.class}
                        >
                            <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                                <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
                            </div>
                            <div class="ml-1 grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-semibold">BusinessCmd POS</span>
                                {#if empresa?.razon_social}
                                    <span class="truncate text-xs text-sidebar-foreground/60">{empresa.razon_social}</span>
                                {/if}
                            </div>
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <!-- Navigation -->
    <SidebarContent class="gap-0 overflow-y-auto px-2 py-1">
        <!-- Dashboard top-level link -->
        <div class="mb-1">
            <Link
                href="/dashboard"
                class="flex items-center gap-2 rounded-md px-2 py-2 text-sm font-medium transition-colors {isExact('/dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground'}"
            >
                <LayoutDashboard class="size-4 shrink-0" />
                <span class="group-data-[collapsible=icon]:hidden">Dashboard</span>
            </Link>
        </div>

        <div class="group-data-[collapsible=icon]:hidden">
            <div class="my-1 h-px bg-sidebar-border/60"></div>
        </div>

        <!-- Nav Groups -->
        {#each navGroups as group (group.label)}
            {@const GroupIcon = groupIcon(group.label)}
            {@const open = isGroupOpen(group.label)}
            {@const groupActive = group.items.some((item) => isActive(item.href))}

            <div class="mb-0.5">
                <!-- Group trigger -->
                <button
                    type="button"
                    class="flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-left text-xs font-semibold uppercase tracking-wider transition-colors group-data-[collapsible=icon]:justify-center {groupActive ? 'text-sidebar-accent-foreground' : 'text-sidebar-foreground/50 hover:text-sidebar-foreground/80'}"
                    onclick={() => toggleGroup(group.label)}
                    title={group.label}
                >
                    <GroupIcon class="size-3.5 shrink-0 group-data-[collapsible=icon]:size-4 {groupActive ? 'text-sidebar-accent-foreground' : ''}" />
                    <span class="flex-1 group-data-[collapsible=icon]:hidden">{group.label}</span>
                    {#if open}
                        <ChevronDown class="size-3 shrink-0 group-data-[collapsible=icon]:hidden" />
                    {:else}
                        <ChevronRight class="size-3 shrink-0 group-data-[collapsible=icon]:hidden" />
                    {/if}
                </button>

                <!-- Group items -->
                {#if open}
                    <div class="mt-0.5 space-y-0.5 group-data-[collapsible=icon]:hidden">
                        {#each group.items as item (item.href)}
                            {@const active = isActive(item.href)}
                            <Link
                                href={item.href}
                                class="flex items-center gap-2 rounded-md py-1.5 pl-6 pr-2 text-sm transition-colors {active ? 'bg-sidebar-accent font-medium text-sidebar-accent-foreground' : 'text-sidebar-foreground/80 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground'}"
                            >
                                <span class="flex-1">{item.title}</span>
                                {#if item.badge && item.href === '/pos' && turnoActivo}
                                    <span class="size-2 rounded-full bg-green-500" title="Turno activo"></span>
                                {/if}
                            </Link>
                        {/each}
                    </div>
                {/if}
            </div>
        {/each}
    </SidebarContent>

    <!-- Footer: User info + Logout -->
    <SidebarFooter class="border-t border-sidebar-border/60 px-2 py-3">
        <div class="flex items-center gap-2 rounded-md px-2 py-2">
            <!-- Avatar initials -->
            <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sidebar-primary text-xs font-semibold text-sidebar-primary-foreground">
                {userInitials}
            </div>
            <div class="min-w-0 flex-1 group-data-[collapsible=icon]:hidden">
                <p class="truncate text-sm font-medium text-sidebar-foreground">{displayName}</p>
                <p class="truncate text-xs text-sidebar-foreground/60">{user?.email ?? ''}</p>
            </div>
            <Link
                href={logout()}
                as="button"
                class="shrink-0 rounded-md p-1.5 text-sidebar-foreground/60 transition-colors hover:bg-sidebar-accent hover:text-sidebar-accent-foreground group-data-[collapsible=icon]:mx-auto"
                onclick={handleLogout}
                title="Cerrar sesión"
            >
                <LogOut class="size-4" />
            </Link>
        </div>
    </SidebarFooter>
</Sidebar>
