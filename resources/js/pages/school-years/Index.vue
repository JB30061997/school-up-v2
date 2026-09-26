<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

import {
    Archive,
    ArrowRight,
    CalendarDays,
    Check,
    ChevronLeft,
    ChevronRight,
    CircleCheck,
    Clock3,
    GraduationCap,
    MoreHorizontal,
    Pencil,
    Plus,
    Search,
    Sparkles,
    Trash2,
} from "lucide-vue-next";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type SchoolYear = {
    id: number;
    environment_id: number;
    name: string;
    start_date: string;
    end_date: string;
    is_current: boolean;
    active: boolean;
    created_at?: string | null;
    updated_at?: string | null;
};

type Environment = {
    id: number;
    name: string;
    code: string;
    current_exercise?: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedSchoolYears = {
    current_page: number;
    data: SchoolYear[];
    from: number | null;
    last_page: number;
    links: PaginationLink[];
    next_page_url: string | null;
    prev_page_url: string | null;
    per_page: number;
    to: number | null;
    total: number;
};

const props = defineProps<{
    schoolYears: PaginatedSchoolYears;
    currentEnvironment: Environment | null;
    filters?: {
        search?: string | null;
        active?: string | boolean | null;
    };
}>();

/* -------------------------------------------------------------------------- */
/* State                                                                      */
/* -------------------------------------------------------------------------- */

const search = ref(props.filters?.search ?? "");
const filter = ref<"all" | "active" | "inactive">("all");
const openMenu = ref<number | null>(null);

/* -------------------------------------------------------------------------- */
/* Data                                                                       */
/* -------------------------------------------------------------------------- */

const years = computed(() => props.schoolYears?.data ?? []);

const currentYear = computed(
    () => years.value.find((year) => year.is_current) ?? null,
);

const activeCount = computed(
    () => years.value.filter((year) => year.active).length,
);

const inactiveCount = computed(
    () => years.value.filter((year) => !year.active).length,
);

const filteredYears = computed(() => {
    const query = search.value.trim().toLowerCase();

    return years.value.filter((year) => {
        const searchMatch =
            !query ||
            year.name.toLowerCase().includes(query) ||
            year.start_date?.toLowerCase().includes(query) ||
            year.end_date?.toLowerCase().includes(query);

        const filterMatch =
            filter.value === "all" ||
            (filter.value === "active" && year.active) ||
            (filter.value === "inactive" && !year.active);

        return searchMatch && filterMatch;
    });
});

/* -------------------------------------------------------------------------- */
/* Helpers                                                                    */
/* -------------------------------------------------------------------------- */

const formatDate = (date: string) => {
    if (!date) return "—";

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(new Date(date));
};

const getAcademicCode = (name: string) => {
    const parts = name.split("-");

    if (parts.length === 2) {
        return `${parts[0].slice(-2)} / ${parts[1].slice(-2)}`;
    }

    return name;
};

/* -------------------------------------------------------------------------- */
/* Actions                                                                    */
/* -------------------------------------------------------------------------- */

const setCurrent = (year: SchoolYear) => {
    if (year.is_current) return;

    if (!confirm(`Définir ${year.name} comme année scolaire courante ?`)) {
        return;
    }

    router.post(
        `/school-years/${year.id}/set-current`,
        {},
        {
            preserveScroll: true,
            onFinish: () => (openMenu.value = null),
        },
    );
};

const toggleActive = (year: SchoolYear) => {
    router.post(
        `/school-years/${year.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
            onFinish: () => (openMenu.value = null),
        },
    );
};

const deleteYear = (year: SchoolYear) => {
    if (year.is_current) {
        alert("L'année scolaire courante ne peut pas être supprimée.");
        return;
    }

    if (
        !confirm(
            `Voulez-vous vraiment supprimer l'année scolaire ${year.name} ?`,
        )
    ) {
        return;
    }

    router.delete(`/school-years/${year.id}`, {
        preserveScroll: true,
        onFinish: () => (openMenu.value = null),
    });
};

const visitPage = (url: string | null) => {
    if (!url) return;

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <Head title="Années scolaires" />

    <div class="min-h-full bg-[#fafbfc]">
        <div class="mx-auto max-w-[1680px] space-y-6 p-5 md:p-7 lg:p-8">
            <!-- ====================================================== -->
            <!-- HEADER                                                 -->
            <!-- ====================================================== -->

            <section
                class="relative overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.04)]"
            >
                <!-- decorative background -->

                <div
                    class="pointer-events-none absolute -right-20 -top-32 size-[430px] rounded-full bg-emerald-100/60 blur-[90px]"
                />

                <div
                    class="pointer-events-none absolute left-[40%] top-0 size-[300px] rounded-full bg-cyan-50 blur-[100px]"
                />

                <div
                    class="relative flex flex-col gap-7 p-7 md:p-9 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-start gap-5">
                        <div
                            class="flex size-16 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-[0_12px_30px_rgba(16,185,129,0.25)]"
                        >
                            <GraduationCap class="size-8" />
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1
                                    class="text-3xl font-bold tracking-tight text-slate-950 md:text-[36px]"
                                >
                                    Années scolaires
                                </h1>

                                <span
                                    class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-emerald-700"
                                >
                                    Référentiel
                                </span>
                            </div>

                            <p
                                class="mt-2 max-w-3xl text-sm leading-6 text-slate-500 md:text-[15px]"
                            >
                                Gérez les périodes scolaires de votre
                                établissement et définissez l'année utilisée
                                actuellement dans School Up.
                            </p>

                            <div
                                v-if="currentEnvironment"
                                class="mt-4 flex flex-wrap items-center gap-2"
                            >
                                <span class="text-sm text-slate-400">
                                    Établissement
                                </span>

                                <span
                                    class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700"
                                >
                                    {{ currentEnvironment.name }}
                                </span>

                                <span
                                    class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700"
                                >
                                    {{ currentEnvironment.code }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/school-years/create"
                        class="inline-flex h-13 shrink-0 items-center justify-center gap-2 rounded-2xl bg-[#050817] px-6 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-emerald-600"
                    >
                        <Plus class="size-5" />

                        Nouvelle année scolaire
                    </Link>
                </div>
            </section>

            <!-- ====================================================== -->
            <!-- CURRENT YEAR + STATS                                   -->
            <!-- ====================================================== -->

            <section class="grid gap-5 xl:grid-cols-[1.6fr_1fr]">
                <!-- CURRENT YEAR -->

                <div
                    v-if="currentYear"
                    class="relative overflow-hidden rounded-[26px] bg-[#071c19] p-7 text-white shadow-[0_15px_40px_rgba(6,78,59,0.15)]"
                >
                    <!-- decoration -->

                    <div
                        class="pointer-events-none absolute -right-16 -top-24 size-[300px] rounded-full bg-emerald-400/15 blur-[60px]"
                    />

                    <div
                        class="pointer-events-none absolute bottom-[-120px] left-[30%] size-[260px] rounded-full bg-cyan-400/10 blur-[70px]"
                    />

                    <div class="relative">
                        <div
                            class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between"
                        >
                            <div>
                                <div
                                    class="flex items-center gap-2 text-xs font-bold uppercase tracking-[0.14em] text-emerald-300"
                                >
                                    <Sparkles class="size-4" />
                                    Année scolaire actuelle
                                </div>

                                <h2
                                    class="mt-3 text-4xl font-bold tracking-tight md:text-5xl"
                                >
                                    {{ currentYear.name }}
                                </h2>

                                <p class="mt-2 text-sm text-emerald-100/60">
                                    Période actuellement utilisée par
                                    l'établissement
                                </p>
                            </div>

                            <div
                                class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-xs font-bold text-emerald-300"
                            >
                                <span
                                    class="size-2 animate-pulse rounded-full bg-emerald-400"
                                />

                                En cours
                            </div>
                        </div>

                        <!-- dates -->

                        <div class="mt-7 grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur"
                            >
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.15em] text-white/40"
                                >
                                    Date de début
                                </p>

                                <div class="mt-2 flex items-center gap-2.5">
                                    <CalendarDays
                                        class="size-5 text-emerald-300"
                                    />

                                    <span class="font-semibold">
                                        {{ formatDate(currentYear.start_date) }}
                                    </span>
                                </div>
                            </div>

                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.06] p-4 backdrop-blur"
                            >
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.15em] text-white/40"
                                >
                                    Date de fin
                                </p>

                                <div class="mt-2 flex items-center gap-2.5">
                                    <Clock3 class="size-5 text-cyan-300" />

                                    <span class="font-semibold">
                                        {{ formatDate(currentYear.end_date) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-5 flex items-center justify-between border-t border-white/10 pt-5"
                        >
                            <div
                                class="flex items-center gap-2 text-xs text-white/50"
                            >
                                <CircleCheck class="size-4 text-emerald-400" />

                                Active et définie comme courante
                            </div>

                            <Link
                                :href="`/school-years/${currentYear.id}/edit`"
                                class="inline-flex items-center gap-2 text-sm font-bold text-white transition hover:text-emerald-300"
                            >
                                Configurer

                                <ArrowRight class="size-4" />
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- NO CURRENT YEAR -->

                <div
                    v-else
                    class="flex min-h-[270px] flex-col justify-center rounded-[26px] border border-dashed border-slate-300 bg-white p-7"
                >
                    <div
                        class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-500"
                    >
                        <CalendarDays class="size-6" />
                    </div>

                    <h2 class="mt-4 text-xl font-bold text-slate-900">
                        Aucune année courante
                    </h2>

                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Définissez une année scolaire comme courante pour
                        l'utiliser dans votre établissement.
                    </p>
                </div>

                <!-- STATS -->

                <div class="grid grid-cols-2 gap-4">
                    <div
                        class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
                        >
                            <CalendarDays class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-3xl font-bold tracking-tight text-slate-950"
                        >
                            {{ schoolYears.total }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-400">
                            Années configurées
                        </p>
                    </div>

                    <div
                        class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <Check class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-3xl font-bold tracking-tight text-slate-950"
                        >
                            {{ activeCount }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-400">
                            Années actives
                        </p>
                    </div>

                    <div
                        class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                        >
                            <Archive class="size-5" />
                        </div>

                        <p
                            class="mt-5 text-3xl font-bold tracking-tight text-slate-950"
                        >
                            {{ inactiveCount }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-400">
                            Années archivées
                        </p>
                    </div>

                    <div
                        class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <Sparkles class="size-5" />
                        </div>

                        <p
                            class="mt-5 truncate text-lg font-bold tracking-tight text-slate-950"
                        >
                            {{ currentYear?.name ?? "—" }}
                        </p>

                        <p class="mt-1 text-xs font-medium text-slate-400">
                            Période actuelle
                        </p>
                    </div>
                </div>
            </section>

            <!-- ====================================================== -->
            <!-- SEARCH + FILTERS                                       -->
            <!-- ====================================================== -->

            <section
                class="flex flex-col gap-3 rounded-[22px] border border-slate-200 bg-white p-3 shadow-sm lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="relative w-full lg:max-w-xl">
                    <Search
                        class="absolute left-4 top-1/2 size-4.5 -translate-y-1/2 text-slate-400"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher une année scolaire..."
                        class="h-12 w-full rounded-xl border border-transparent bg-slate-50 pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-emerald-200 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                    />
                </div>

                <div class="flex items-center rounded-xl bg-slate-100 p-1">
                    <button
                        @click="filter = 'all'"
                        :class="[
                            'rounded-lg px-4 py-2 text-xs font-bold transition',
                            filter === 'all'
                                ? 'bg-white text-slate-950 shadow-sm'
                                : 'text-slate-500',
                        ]"
                    >
                        Toutes
                        <span class="ml-1 text-slate-400">
                            {{ years.length }}
                        </span>
                    </button>

                    <button
                        @click="filter = 'active'"
                        :class="[
                            'rounded-lg px-4 py-2 text-xs font-bold transition',
                            filter === 'active'
                                ? 'bg-white text-slate-950 shadow-sm'
                                : 'text-slate-500',
                        ]"
                    >
                        Actives
                        <span class="ml-1 text-slate-400">
                            {{ activeCount }}
                        </span>
                    </button>

                    <button
                        @click="filter = 'inactive'"
                        :class="[
                            'rounded-lg px-4 py-2 text-xs font-bold transition',
                            filter === 'inactive'
                                ? 'bg-white text-slate-950 shadow-sm'
                                : 'text-slate-500',
                        ]"
                    >
                        Archivées
                        <span class="ml-1 text-slate-400">
                            {{ inactiveCount }}
                        </span>
                    </button>
                </div>
            </section>

            <!-- ====================================================== -->
            <!-- YEARS                                                  -->
            <!-- ====================================================== -->

            <section>
                <div
                    class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-xl font-bold tracking-tight text-slate-950"
                        >
                            Historique des années scolaires
                        </h2>

                        <p class="mt-1 text-sm text-slate-400">
                            {{ filteredYears.length }}
                            année{{
                                filteredYears.length > 1 ? "s" : ""
                            }}
                            affichée{{ filteredYears.length > 1 ? "s" : "" }}
                        </p>
                    </div>
                </div>

                <!-- Empty -->

                <div
                    v-if="filteredYears.length === 0"
                    class="flex min-h-[320px] flex-col items-center justify-center rounded-[26px] border border-dashed border-slate-300 bg-white p-8 text-center"
                >
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                    >
                        <GraduationCap class="size-7" />
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                        Aucune année scolaire
                    </h3>

                    <p class="mt-2 max-w-sm text-sm leading-6 text-slate-500">
                        Aucune période ne correspond aux critères sélectionnés.
                    </p>
                </div>

                <!-- Cards -->

                <div v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="year in filteredYears"
                        :key="year.id"
                        :class="[
                            'group relative rounded-[24px] border bg-white p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_16px_40px_rgba(15,23,42,0.08)]',
                            year.is_current
                                ? 'border-emerald-300 shadow-[0_10px_30px_rgba(16,185,129,0.08)]'
                                : 'border-slate-200 shadow-sm',
                        ]"
                    >
                        <!-- top current bar -->

                        <div
                            v-if="year.is_current"
                            class="absolute inset-x-5 top-0 h-[3px] rounded-b-full bg-gradient-to-r from-emerald-400 to-cyan-400"
                        />

                        <!-- header -->

                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3.5">
                                <div
                                    :class="[
                                        'flex size-12 shrink-0 items-center justify-center rounded-2xl transition',
                                        year.is_current
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-slate-100 text-slate-500 group-hover:bg-emerald-50 group-hover:text-emerald-600',
                                    ]"
                                >
                                    <GraduationCap class="size-6" />
                                </div>

                                <div class="min-w-0">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <h3
                                            class="text-lg font-bold tracking-tight text-slate-950"
                                        >
                                            {{ year.name }}
                                        </h3>

                                        <span
                                            v-if="year.is_current"
                                            class="rounded-full bg-emerald-100 px-2.5 py-1 text-[9px] font-extrabold uppercase tracking-[0.1em] text-emerald-700"
                                        >
                                            Courante
                                        </span>
                                    </div>

                                    <p
                                        class="mt-1 text-xs font-medium text-slate-400"
                                    >
                                        Année #{{ year.id }}
                                        <span class="mx-1.5">•</span>
                                        {{ getAcademicCode(year.name) }}
                                    </p>
                                </div>
                            </div>

                            <!-- menu -->

                            <div class="relative">
                                <button
                                    type="button"
                                    @click="
                                        openMenu =
                                            openMenu === year.id
                                                ? null
                                                : year.id
                                    "
                                    class="flex size-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                >
                                    <MoreHorizontal class="size-5" />
                                </button>

                                <div
                                    v-if="openMenu === year.id"
                                    class="absolute right-0 top-10 z-40 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_18px_50px_rgba(15,23,42,0.15)]"
                                >
                                    <Link
                                        :href="`/school-years/${year.id}/edit`"
                                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                    >
                                        <Pencil class="size-4" />
                                        Modifier
                                    </Link>

                                    <button
                                        v-if="!year.is_current"
                                        type="button"
                                        @click="setCurrent(year)"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-emerald-700 transition hover:bg-emerald-50"
                                    >
                                        <Sparkles class="size-4" />
                                        Définir comme courante
                                    </button>

                                    <button
                                        type="button"
                                        @click="toggleActive(year)"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                                    >
                                        <Archive class="size-4" />

                                        {{
                                            year.active
                                                ? "Archiver"
                                                : "Réactiver"
                                        }}
                                    </button>

                                    <div
                                        v-if="!year.is_current"
                                        class="my-1 border-t border-slate-100"
                                    />

                                    <button
                                        v-if="!year.is_current"
                                        type="button"
                                        @click="deleteYear(year)"
                                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-red-600 transition hover:bg-red-50"
                                    >
                                        <Trash2 class="size-4" />
                                        Supprimer
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- period -->

                        <div
                            class="relative mt-5 overflow-hidden rounded-2xl border border-slate-100 bg-slate-50/80 px-4 py-4"
                        >
                            <div
                                class="absolute left-[28px] right-[28px] top-[29px] h-px bg-slate-200"
                            />

                            <div class="relative grid grid-cols-2 gap-4">
                                <div>
                                    <div
                                        class="mb-3 flex size-7 items-center justify-center rounded-lg border border-emerald-100 bg-white text-emerald-500 shadow-sm"
                                    >
                                        <CalendarDays class="size-3.5" />
                                    </div>

                                    <p
                                        class="text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400"
                                    >
                                        Début
                                    </p>

                                    <p
                                        class="mt-1 text-sm font-bold text-slate-700"
                                    >
                                        {{ formatDate(year.start_date) }}
                                    </p>
                                </div>

                                <div>
                                    <div
                                        class="mb-3 ml-auto flex size-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 shadow-sm"
                                    >
                                        <Clock3 class="size-3.5" />
                                    </div>

                                    <div class="text-right">
                                        <p
                                            class="text-[9px] font-bold uppercase tracking-[0.13em] text-slate-400"
                                        >
                                            Fin
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-bold text-slate-700"
                                        >
                                            {{ formatDate(year.end_date) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- footer -->

                        <div
                            class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4"
                        >
                            <span
                                v-if="year.active"
                                class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-bold text-emerald-700"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-emerald-500"
                                />

                                Active
                            </span>

                            <span
                                v-else
                                class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-[11px] font-bold text-slate-500"
                            >
                                <span
                                    class="size-1.5 rounded-full bg-slate-400"
                                />

                                Archivée
                            </span>

                            <Link
                                :href="`/school-years/${year.id}/edit`"
                                class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-xs font-bold text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700"
                            >
                                Gérer
                                <ArrowRight class="size-3.5" />
                            </Link>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ====================================================== -->
            <!-- PAGINATION                                             -->
            <!-- ====================================================== -->

            <section
                v-if="schoolYears.last_page > 1"
                class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-4"
            >
                <p class="text-xs text-slate-400">
                    {{ schoolYears.from ?? 0 }}
                    –
                    {{ schoolYears.to ?? 0 }}
                    sur
                    {{ schoolYears.total }}
                </p>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        :disabled="!schoolYears.prev_page_url"
                        @click="visitPage(schoolYears.prev_page_url)"
                        class="flex size-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30"
                    >
                        <ChevronLeft class="size-4" />
                    </button>

                    <span class="px-2 text-xs font-bold text-slate-600">
                        {{ schoolYears.current_page }}
                        /
                        {{ schoolYears.last_page }}
                    </span>

                    <button
                        type="button"
                        :disabled="!schoolYears.next_page_url"
                        @click="visitPage(schoolYears.next_page_url)"
                        class="flex size-9 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30"
                    >
                        <ChevronRight class="size-4" />
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
