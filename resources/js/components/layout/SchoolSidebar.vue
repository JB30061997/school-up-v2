<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import {
    BookOpenCheck,
    Building2,
    CalendarDays,
    ChevronDown,
    ChevronRight,
    ClipboardList,
    GraduationCap,
    LayoutDashboard,
    LifeBuoy,
    MessageSquareWarning,
    School,
    Settings2,
    ShieldCheck,
    SlidersHorizontal,
    Tags,
    UserCog,
    Users,
} from "@lucide/vue";

import NavUser from "@/components/NavUser.vue";

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type NavigationItem = {
    title: string;
    href: string;
    icon: any;
    permission?: string;
};

type Environment = {
    id: number;
    name: string;
    code: string;
    app_name?: string | null;
    current_exercise?: string | null;
    logo?: string | null;
};

type AuthProps = {
    user: {
        id: number;
        name: string;
        first_name?: string | null;
        last_name?: string | null;
        email: string;
    } | null;

    role: {
        id: number;
        name: string;
        code: string;
    } | null;

    permissions: string[];
};

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const page = usePage();

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

const auth = computed<AuthProps>(() => {
    return page.props.auth as unknown as AuthProps;
});

/*
|--------------------------------------------------------------------------
| Environment
|--------------------------------------------------------------------------
*/

const currentEnvironment = computed<Environment | null>(() => {
    return (page.props.currentEnvironment ?? null) as Environment | null;
});

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
*/

const permissions = computed<string[]>(() => {
    return auth.value?.permissions ?? [];
});

const can = (permission?: string): boolean => {
    if (!permission) {
        return true;
    }

    return permissions.value.includes(permission);
};

/*
|--------------------------------------------------------------------------
| Active route
|--------------------------------------------------------------------------
*/

const isActive = (path: string): boolean => {
    if (path === "/dashboard") {
        return page.url === "/dashboard";
    }

    return page.url === path || page.url.startsWith(`${path}/`);
};

/*
|--------------------------------------------------------------------------
| Submenus
|--------------------------------------------------------------------------
*/

const reclamationsOpen = ref(
    page.url.startsWith("/tickets") ||
        page.url.startsWith("/ticket-categories"),
);

const referentielsOpen = ref(
    page.url.startsWith("/referentials") ||
        page.url.startsWith("/school-years") ||
        page.url.startsWith("/academic-structure") ||
        page.url.startsWith("/support-teams"),
);

const settingsOpen = ref(
    page.url.startsWith("/roles") ||
        page.url.startsWith("/environments") ||
        page.url.startsWith("/settings"),
);

/*
|--------------------------------------------------------------------------
| Navigation - Réclamations
|--------------------------------------------------------------------------
*/

const reclamationNavigation: NavigationItem[] = [
    {
        title: "Réclamations",
        href: "/tickets",
        icon: MessageSquareWarning,
        permission: "tickets.view",
    },
    {
        title: "Objets des réclamations",
        href: "/ticket-categories",
        icon: Tags,
        permission: "ticket-categories.view",
    },
];

/*
|--------------------------------------------------------------------------
| Navigation - Référentiels
|--------------------------------------------------------------------------
*/

const referentielNavigation: NavigationItem[] = [
    {
        title: "Configuration",
        href: "/referentials",
        icon: SlidersHorizontal,
    },
    {
        title: "Années scolaires",
        href: "/school-years",
        icon: GraduationCap,
        permission: "school-years.view",
    },
    {
        title: "Structure pédagogique",
        href: "/academic-structure",
        icon: School,
    },
    {
        title: "Équipes support",
        href: "/support-teams",
        icon: UserCog,
        permission: "support-teams.view",
    },
];

/*
|--------------------------------------------------------------------------
| Navigation - Paramètres
|--------------------------------------------------------------------------
*/

const settingsNavigation: NavigationItem[] = [
    {
        title: "Rôles & permissions",
        href: "/roles",
        icon: ShieldCheck,
        permission: "roles.view",
    },
    {
        title: "Environnements",
        href: "/environments",
        icon: Building2,
        permission: "environments.view",
    },
];

/*
|--------------------------------------------------------------------------
| Visible navigation
|--------------------------------------------------------------------------
*/

const visibleReclamationNavigation = computed(() => {
    return reclamationNavigation.filter((item) => can(item.permission));
});

const visibleReferentielNavigation = computed(() => {
    return referentielNavigation.filter((item) => can(item.permission));
});

const visibleSettingsNavigation = computed(() => {
    return settingsNavigation.filter((item) => can(item.permission));
});

/*
|--------------------------------------------------------------------------
| Branding
|--------------------------------------------------------------------------
*/

const appName = computed<string>(() => {
    return (
        currentEnvironment.value?.app_name ||
        currentEnvironment.value?.name ||
        "School Up"
    );
});

const environmentName = computed<string>(() => {
    return currentEnvironment.value?.name ?? "Gestion scolaire";
});

const environmentInitials = computed<string>(() => {
    const name = currentEnvironment.value?.name ?? "School Up";

    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
});

/*
|--------------------------------------------------------------------------
| Global active states
|--------------------------------------------------------------------------
*/

const reclamationsActive = computed(() => {
    return (
        page.url.startsWith("/tickets") ||
        page.url.startsWith("/ticket-categories")
    );
});

const referentielsActive = computed(() => {
    return (
        page.url.startsWith("/referentials") ||
        page.url.startsWith("/school-years") ||
        page.url.startsWith("/academic-structure") ||
        page.url.startsWith("/support-teams")
    );
});

const settingsActive = computed(() => {
    return (
        page.url.startsWith("/roles") ||
        page.url.startsWith("/environments") ||
        page.url.startsWith("/settings")
    );
});
</script>

<template>
    <Sidebar
        collapsible="icon"
        variant="inset"
        class="border-r border-slate-200/80 bg-white"
    >
        <!-- ========================================================= -->
        <!-- BRAND -->
        <!-- ========================================================= -->

        <SidebarHeader class="border-b border-slate-100 bg-white px-3 py-3">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="h-[62px] rounded-2xl px-2 hover:bg-emerald-50/50"
                    >
                        <Link href="/dashboard">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-100 via-green-50 to-white text-emerald-700 shadow-sm"
                            >
                                <img
                                    v-if="currentEnvironment?.logo"
                                    :src="currentEnvironment.logo"
                                    :alt="environmentName"
                                    class="size-full object-contain p-1.5"
                                />

                                <span
                                    v-else
                                    class="text-[13px] font-black tracking-tight"
                                >
                                    {{ environmentInitials }}
                                </span>
                            </div>

                            <div
                                class="grid min-w-0 flex-1 text-left leading-tight"
                            >
                                <span
                                    class="truncate text-[14px] font-extrabold tracking-tight text-slate-900"
                                >
                                    {{ appName }}
                                </span>

                                <span
                                    class="mt-1 truncate text-[10px] font-medium text-slate-400"
                                >
                                    {{ environmentName }}
                                </span>
                            </div>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- ========================================================= -->
        <!-- CONTENT -->
        <!-- ========================================================= -->

        <SidebarContent class="sidebar-modern-scroll bg-white px-3 py-4">
            <!-- ===================================================== -->
            <!-- VUE D'ENSEMBLE -->
            <!-- ===================================================== -->

            <section class="mb-6">
                <div
                    class="mb-2 flex items-center gap-2 px-2 group-data-[collapsible=icon]:hidden"
                >
                    <span
                        class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_0_4px_rgba(16,185,129,0.08)]"
                    />

                    <span
                        class="text-[9px] font-black uppercase tracking-[0.18em] text-slate-400"
                    >
                        Vue d'ensemble
                    </span>
                </div>

                <SidebarMenu>
                    <SidebarMenuItem v-if="can('dashboard.view')">
                        <SidebarMenuButton
                            as-child
                            :is-active="isActive('/dashboard')"
                            tooltip="Tableau de bord"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-emerald-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-emerald-50 data-[active=true]:via-green-50/70 data-[active=true]:to-white data-[active=true]:shadow-sm"
                        >
                            <Link href="/dashboard">
                                <span
                                    v-if="isActive('/dashboard')"
                                    class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-gradient-to-b from-emerald-400 to-green-600"
                                />

                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-500 ring-1 ring-slate-200/80 transition-all duration-200 group-hover:bg-emerald-50 group-hover:text-emerald-600 group-data-[active=true]:bg-gradient-to-br group-data-[active=true]:from-emerald-500 group-data-[active=true]:to-green-600 group-data-[active=true]:text-white group-data-[active=true]:ring-emerald-300"
                                >
                                    <LayoutDashboard class="size-4" />
                                </div>

                                <span
                                    class="font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-emerald-900"
                                >
                                    Tableau de bord
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </section>

            <!-- ===================================================== -->
            <!-- SERVICES SCOLAIRES -->
            <!-- ===================================================== -->

            <section class="mb-6">
                <div
                    class="mb-2 flex items-center gap-2 px-2 group-data-[collapsible=icon]:hidden"
                >
                    <span
                        class="size-1.5 rounded-full bg-green-500 shadow-[0_0_0_4px_rgba(34,197,94,0.08)]"
                    />

                    <span
                        class="text-[9px] font-black uppercase tracking-[0.18em] text-slate-400"
                    >
                        Services scolaires
                    </span>
                </div>

                <SidebarMenu class="space-y-1">
                    <!-- RENDEZ-VOUS -->

                    <SidebarMenuItem v-if="can('appointments.view')">
                        <SidebarMenuButton
                            as-child
                            :is-active="isActive('/appointments')"
                            tooltip="Rendez-vous"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-emerald-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-emerald-50 data-[active=true]:to-white"
                        >
                            <Link href="/appointments">
                                <span
                                    v-if="isActive('/appointments')"
                                    class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-emerald-500"
                                />

                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 transition-all group-data-[active=true]:bg-emerald-500 group-data-[active=true]:text-white"
                                >
                                    <CalendarDays class="size-4" />
                                </div>

                                <span
                                    class="min-w-0 flex-1 truncate font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-emerald-900"
                                >
                                    Rendez-vous
                                </span>

                                <ChevronRight
                                    class="size-3.5 shrink-0 text-slate-300 transition-transform group-hover:translate-x-0.5"
                                />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- DEMANDES -->

                    <SidebarMenuItem v-if="can('requests.view')">
                        <SidebarMenuButton
                            as-child
                            :is-active="isActive('/requests')"
                            tooltip="Demandes"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-green-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-green-50 data-[active=true]:to-white"
                        >
                            <Link href="/requests">
                                <span
                                    v-if="isActive('/requests')"
                                    class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-green-500"
                                />

                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100 transition-all group-data-[active=true]:bg-green-500 group-data-[active=true]:text-white"
                                >
                                    <ClipboardList class="size-4" />
                                </div>

                                <span
                                    class="min-w-0 flex-1 truncate font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-green-900"
                                >
                                    Demandes
                                </span>

                                <ChevronRight
                                    class="size-3.5 shrink-0 text-slate-300 transition-transform group-hover:translate-x-0.5"
                                />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- RECLAMATIONS -->

                    <SidebarMenuItem v-if="visibleReclamationNavigation.length">
                        <SidebarMenuButton
                            :is-active="reclamationsActive"
                            tooltip="Réclamations"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-teal-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-teal-50 data-[active=true]:to-white"
                            @click="reclamationsOpen = !reclamationsOpen"
                        >
                            <span
                                v-if="reclamationsActive"
                                class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-teal-500"
                            />

                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-600 ring-1 ring-teal-100 transition-all group-data-[active=true]:bg-teal-500 group-data-[active=true]:text-white"
                            >
                                <MessageSquareWarning class="size-4" />
                            </div>

                            <span
                                class="min-w-0 flex-1 truncate text-left font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-teal-900"
                            >
                                Réclamations
                            </span>

                            <ChevronDown
                                class="size-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                                :class="{
                                    'rotate-180': reclamationsOpen,
                                }"
                            />
                        </SidebarMenuButton>

                        <div
                            v-if="reclamationsOpen"
                            class="ml-[18px] mt-1 space-y-0.5 border-l border-teal-100 pl-[18px] group-data-[collapsible=icon]:hidden"
                        >
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in visibleReclamationNavigation"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
                                        :is-active="isActive(item.href)"
                                        :tooltip="item.title"
                                        class="group h-9 rounded-lg px-2 transition-all hover:bg-teal-50 data-[active=true]:bg-teal-50"
                                    >
                                        <Link :href="item.href">
                                            <component
                                                :is="item.icon"
                                                class="size-3.5 shrink-0 text-teal-500"
                                            />

                                            <span
                                                class="truncate text-[12px] font-medium text-slate-600 group-data-[active=true]:font-bold group-data-[active=true]:text-teal-700"
                                            >
                                                {{ item.title }}
                                            </span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>
            </section>

            <!-- ===================================================== -->
            <!-- GESTION -->
            <!-- ===================================================== -->

            <section class="mb-6">
                <div
                    class="mb-2 flex items-center gap-2 px-2 group-data-[collapsible=icon]:hidden"
                >
                    <span
                        class="size-1.5 rounded-full bg-emerald-600 shadow-[0_0_0_4px_rgba(5,150,105,0.08)]"
                    />

                    <span
                        class="text-[9px] font-black uppercase tracking-[0.18em] text-slate-400"
                    >
                        Gestion
                    </span>
                </div>

                <SidebarMenu class="space-y-1">
                    <!-- USERS -->

                    <SidebarMenuItem v-if="can('users.view')">
                        <SidebarMenuButton
                            as-child
                            :is-active="isActive('/users')"
                            tooltip="Données des utilisateurs"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-emerald-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-emerald-50 data-[active=true]:to-white"
                        >
                            <Link href="/users">
                                <span
                                    v-if="isActive('/users')"
                                    class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-emerald-500"
                                />

                                <div
                                    class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100 transition-all group-data-[active=true]:bg-emerald-500 group-data-[active=true]:text-white"
                                >
                                    <Users class="size-4" />
                                </div>

                                <span
                                    class="min-w-0 flex-1 truncate font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-emerald-900"
                                >
                                    Données des utilisateurs
                                </span>

                                <ChevronRight
                                    class="size-3.5 shrink-0 text-slate-300"
                                />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <!-- REFERENTIELS -->

                    <SidebarMenuItem v-if="visibleReferentielNavigation.length">
                        <SidebarMenuButton
                            :is-active="referentielsActive"
                            tooltip="Référentiels"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-lime-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-lime-50 data-[active=true]:to-white"
                            @click="referentielsOpen = !referentielsOpen"
                        >
                            <span
                                v-if="referentielsActive"
                                class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-lime-500"
                            />

                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-lime-50 text-lime-700 ring-1 ring-lime-200 transition-all group-data-[active=true]:bg-lime-500 group-data-[active=true]:text-white"
                            >
                                <BookOpenCheck class="size-4" />
                            </div>

                            <span
                                class="min-w-0 flex-1 truncate text-left font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-lime-900"
                            >
                                Référentiels
                            </span>

                            <ChevronDown
                                class="size-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                                :class="{
                                    'rotate-180': referentielsOpen,
                                }"
                            />
                        </SidebarMenuButton>

                        <div
                            v-if="referentielsOpen"
                            class="ml-[18px] mt-1 space-y-0.5 border-l border-lime-100 pl-[18px] group-data-[collapsible=icon]:hidden"
                        >
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in visibleReferentielNavigation"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
                                        :is-active="isActive(item.href)"
                                        :tooltip="item.title"
                                        class="group h-9 rounded-lg px-2 transition-all hover:bg-lime-50 data-[active=true]:bg-lime-50"
                                    >
                                        <Link :href="item.href">
                                            <component
                                                :is="item.icon"
                                                class="size-3.5 shrink-0 text-lime-600"
                                            />

                                            <span
                                                class="truncate text-[12px] font-medium text-slate-600 group-data-[active=true]:font-bold group-data-[active=true]:text-lime-700"
                                            >
                                                {{ item.title }}
                                            </span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>
            </section>

            <!-- ===================================================== -->
            <!-- ADMINISTRATION -->
            <!-- ===================================================== -->

            <section class="mb-4">
                <div
                    class="mb-2 flex items-center gap-2 px-2 group-data-[collapsible=icon]:hidden"
                >
                    <span
                        class="size-1.5 rounded-full bg-green-700 shadow-[0_0_0_4px_rgba(21,128,61,0.08)]"
                    />

                    <span
                        class="text-[9px] font-black uppercase tracking-[0.18em] text-slate-400"
                    >
                        Administration
                    </span>
                </div>

                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            :is-active="settingsActive"
                            tooltip="Paramètres"
                            class="group relative h-11 overflow-hidden rounded-xl px-2 transition-all duration-200 hover:bg-emerald-50/70 data-[active=true]:bg-gradient-to-r data-[active=true]:from-emerald-50 data-[active=true]:to-white"
                            @click="settingsOpen = !settingsOpen"
                        >
                            <span
                                v-if="settingsActive"
                                class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-emerald-600"
                            />

                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 transition-all group-data-[active=true]:bg-emerald-600 group-data-[active=true]:text-white"
                            >
                                <Settings2 class="size-4" />
                            </div>

                            <span
                                class="min-w-0 flex-1 truncate text-left font-semibold text-slate-700 group-data-[active=true]:font-bold group-data-[active=true]:text-emerald-900"
                            >
                                Paramètres
                            </span>

                            <ChevronDown
                                class="size-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                                :class="{
                                    'rotate-180': settingsOpen,
                                }"
                            />
                        </SidebarMenuButton>

                        <div
                            v-if="settingsOpen"
                            class="ml-[18px] mt-1 space-y-0.5 border-l border-emerald-100 pl-[18px] group-data-[collapsible=icon]:hidden"
                        >
                            <SidebarMenu>
                                <SidebarMenuItem
                                    v-for="item in visibleSettingsNavigation"
                                    :key="item.title"
                                >
                                    <SidebarMenuButton
                                        as-child
                                        :is-active="isActive(item.href)"
                                        :tooltip="item.title"
                                        class="group h-9 rounded-lg px-2 transition-all hover:bg-emerald-50 data-[active=true]:bg-emerald-50"
                                    >
                                        <Link :href="item.href">
                                            <component
                                                :is="item.icon"
                                                class="size-3.5 shrink-0 text-emerald-600"
                                            />

                                            <span
                                                class="truncate text-[12px] font-medium text-slate-600 group-data-[active=true]:font-bold group-data-[active=true]:text-emerald-700"
                                            >
                                                {{ item.title }}
                                            </span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <!-- MON COMPTE -->

                                <SidebarMenuItem>
                                    <SidebarMenuButton
                                        as-child
                                        :is-active="
                                            page.url.startsWith(
                                                '/settings/profile',
                                            )
                                        "
                                        tooltip="Mon compte"
                                        class="group h-9 rounded-lg px-2 transition-all hover:bg-emerald-50 data-[active=true]:bg-emerald-50"
                                    >
                                        <Link href="/settings/profile">
                                            <UserCog
                                                class="size-3.5 shrink-0 text-emerald-600"
                                            />

                                            <span
                                                class="truncate text-[12px] font-medium text-slate-600 group-data-[active=true]:font-bold group-data-[active=true]:text-emerald-700"
                                            >
                                                Mon compte
                                            </span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </div>
                    </SidebarMenuItem>
                </SidebarMenu>
            </section>
        </SidebarContent>

        <!-- ========================================================= -->
        <!-- FOOTER -->
        <!-- ========================================================= -->

        <!-- <SidebarFooter class="border-t border-slate-100 bg-white p-2">
            <div
                class="flex items-center gap-2 px-3 py-1 text-[10px] text-slate-400 group-data-[collapsible=icon]:hidden"
            >
                <LifeBuoy class="size-3.5 text-emerald-500" />

                <span>School Up • v2</span>
            </div>

            <NavUser />
        </SidebarFooter> -->
    </Sidebar>
</template>

<style scoped>
/*
|--------------------------------------------------------------------------
| Modern scrollbar
|--------------------------------------------------------------------------
*/

.sidebar-modern-scroll {
    scrollbar-width: thin;
    scrollbar-color: rgb(203 213 225 / 0.55) transparent;
}

.sidebar-modern-scroll::-webkit-scrollbar {
    width: 4px;
}

.sidebar-modern-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-modern-scroll::-webkit-scrollbar-thumb {
    background: rgb(203 213 225 / 0.55);
    border-radius: 9999px;
}

.sidebar-modern-scroll::-webkit-scrollbar-thumb:hover {
    background: rgb(148 163 184 / 0.7);
}
</style>
