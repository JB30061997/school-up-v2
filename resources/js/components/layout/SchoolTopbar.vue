<script setup lang="ts">
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

import {
    Bell,
    Building2,
    CalendarRange,
    ChevronRight,
    ChevronsUpDown,
    GraduationCap,
    MapPin,
    School,
} from "@lucide/vue";

import { SidebarTrigger } from "@/components/ui/sidebar";
import type { BreadcrumbItem } from "@/types";

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type CurrentEnvironment = {
    id: number;
    name: string;
    code: string;

    // Nom commercial / marque de l'école
    app_name?: string | null;

    current_exercise?: string | null;

    logo?: string | null;
    logo_dark?: string | null;
};

type NotificationProps = {
    unread_count: number;

    latest: Array<{
        id: string;
        type: string;
        data: Record<string, unknown>;
        read: boolean;
        read_at?: string | null;
        created_at?: string | null;
    }>;
};

/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

const page = usePage();

/*
|--------------------------------------------------------------------------
| Current environment
|--------------------------------------------------------------------------
*/

const currentEnvironment = computed(() => {
    return page.props.currentEnvironment as CurrentEnvironment | null;
});

/*
|--------------------------------------------------------------------------
| Brand
|--------------------------------------------------------------------------
|
| Exemple :
|
| app_name = Al Jabr International School
| name     = AIS Bouskoura Primaire
|
*/

const schoolBrand = computed(() => {
    return (
        currentEnvironment.value?.app_name ||
        currentEnvironment.value?.name ||
        "School Up"
    );
});

const environmentName = computed(() => {
    if (!currentEnvironment.value) {
        return "Aucun établissement sélectionné";
    }

    return currentEnvironment.value.name;
});

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

const notifications = computed(() => {
    return (
        (page.props.notifications as NotificationProps | undefined) ?? {
            unread_count: 0,
            latest: [],
        }
    );
});

const unreadCount = computed(() => {
    return notifications.value.unread_count ?? 0;
});

const notificationLabel = computed(() => {
    if (unreadCount.value > 99) {
        return "99+";
    }

    return String(unreadCount.value);
});
</script>

<template>
    <header
        class="sticky top-0 z-30 flex h-[72px] shrink-0 items-center border-b border-slate-200/80 bg-white/95 px-4 shadow-[0_1px_3px_rgba(15,23,42,0.04)] backdrop-blur-xl md:px-6"
    >
        <!-- ============================================================= -->
        <!-- LEFT -->
        <!-- ============================================================= -->

        <div class="flex min-w-0 flex-1 items-center gap-3">
            <!-- SIDEBAR -->

            <SidebarTrigger
                class="-ml-1 size-9 shrink-0 rounded-xl text-slate-400 transition hover:bg-emerald-50 hover:text-emerald-700"
            />

            <!-- SEPARATOR -->

            <div
                class="hidden h-8 w-px shrink-0 bg-slate-200 sm:block"
            ></div>

            <!-- ========================================================= -->
            <!-- SCHOOL BRAND -->
            <!-- ========================================================= -->

            <div class="flex min-w-0 items-center gap-3">
                <!-- LOGO -->

                <div
                    class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-700 shadow-sm"
                >
                    <img
                        v-if="currentEnvironment?.logo"
                        :src="currentEnvironment.logo"
                        :alt="schoolBrand"
                        class="size-full object-contain p-1.5"
                    />

                    <GraduationCap
                        v-else
                        class="size-5"
                    />
                </div>

                <!-- BRAND / ENVIRONMENT -->

                <div class="min-w-0">
                    <!-- SCHOOL NAME -->

                    <div class="flex min-w-0 items-center gap-2">
                        <h1
                            class="truncate text-sm font-black tracking-tight text-slate-950 md:text-[15px]"
                        >
                            {{ schoolBrand }}
                        </h1>

                        <span
                            v-if="currentEnvironment"
                            class="hidden size-1 rounded-full bg-emerald-400 md:block"
                        ></span>

                        <span
                            v-if="currentEnvironment?.code"
                            class="hidden rounded-md bg-slate-100 px-1.5 py-0.5 font-mono text-[9px] font-bold uppercase tracking-wide text-slate-400 lg:inline-flex"
                        >
                            {{ currentEnvironment.code }}
                        </span>
                    </div>

                    <!-- ENVIRONMENT -->

                    <div
                        class="mt-0.5 flex min-w-0 items-center gap-1.5"
                    >
                        <MapPin
                            class="size-3 shrink-0 text-emerald-500"
                        />

                        <span
                            class="truncate text-[11px] font-medium text-slate-500"
                        >
                            {{ environmentName }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- RIGHT -->
        <!-- ============================================================= -->

        <div class="ml-3 flex shrink-0 items-center gap-2">
            <!-- ========================================================= -->
            <!-- SCHOOL YEAR -->
            <!-- ========================================================= -->

            <div
                v-if="currentEnvironment?.current_exercise"
                class="hidden h-10 items-center gap-2.5 rounded-xl border border-emerald-100 bg-emerald-50/70 px-3.5 lg:flex"
            >
                <div
                    class="flex size-7 items-center justify-center rounded-lg bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100"
                >
                    <CalendarRange class="size-3.5" />
                </div>

                <div class="leading-tight">
                    <p
                        class="text-[8px] font-black uppercase tracking-[0.14em] text-slate-400"
                    >
                        Année scolaire
                    </p>

                    <p
                        class="mt-0.5 text-[11px] font-black text-emerald-700"
                    >
                        {{ currentEnvironment.current_exercise }}
                    </p>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- ENVIRONMENT SWITCHER -->
            <!-- ========================================================= -->

            <Link
                href="/select-environment"
                class="group hidden h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50/50 hover:shadow-md sm:flex"
            >
                <Building2
                    class="size-4 text-slate-400 transition group-hover:text-emerald-600"
                />

                <span
                    class="hidden text-xs font-bold text-slate-600 xl:inline"
                >
                    Changer d'établissement
                </span>

                <ChevronsUpDown
                    class="size-3.5 text-slate-400 transition group-hover:text-emerald-600"
                />
            </Link>

            <!-- MOBILE ENVIRONMENT -->

            <Link
                href="/select-environment"
                class="flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 sm:hidden"
                title="Changer d'établissement"
            >
                <School class="size-4" />
            </Link>

            <!-- ========================================================= -->
            <!-- NOTIFICATIONS -->
            <!-- ========================================================= -->

            <Link
                href="/notifications"
                class="relative flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 hover:shadow-md"
                title="Notifications"
            >
                <Bell class="size-[18px]" />

                <span
                    v-if="unreadCount > 0"
                    class="absolute -right-1 -top-1 flex min-w-[18px] items-center justify-center rounded-full bg-emerald-600 px-1.5 py-0.5 text-[9px] font-black leading-none text-white shadow-sm ring-2 ring-white"
                >
                    {{ notificationLabel }}
                </span>
            </Link>
        </div>
    </header>
</template>