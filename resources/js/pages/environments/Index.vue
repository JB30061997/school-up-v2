<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

import {
    Building2,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleSlash2,
    ExternalLink,
    GraduationCap,
    MoreHorizontal,
    Palette,
    Pencil,
    Plus,
    Search,
    Users,
} from "lucide-vue-next";

type Environment = {
    id: number;
    name: string;
    code: string;
    app_name?: string | null;
    url?: string | null;

    logo?: string | null;
    logo_url?: string | null;

    primary_color?: string | null;
    secondary_color?: string | null;
    sidebar_color?: string | null;
    sidebar_text_color?: string | null;
    accent_color?: string | null;

    current_exercise?: string | null;
    active: boolean;

    users_count?: number;
    active_users_count?: number;
    school_years_count?: number;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedEnvironments = {
    current_page: number;
    data: Environment[];
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
    environments: PaginatedEnvironments;

    filters?: {
        search?: string | null;
        active?: string | null;
    };
}>();

const search = ref(props.filters?.search ?? "");

const filter = ref<"all" | "active" | "inactive">(
    props.filters?.active === "1"
        ? "active"
        : props.filters?.active === "0"
          ? "inactive"
          : "all",
);

const openMenu = ref<number | null>(null);

const environmentList = computed(() => props.environments?.data ?? []);

const activeCount = computed(
    () =>
        environmentList.value.filter((environment) => environment.active)
            .length,
);

const inactiveCount = computed(
    () =>
        environmentList.value.filter((environment) => !environment.active)
            .length,
);

const totalUsers = computed(() =>
    environmentList.value.reduce(
        (total, environment) => total + Number(environment.users_count ?? 0),
        0,
    ),
);

const totalSchoolYears = computed(() =>
    environmentList.value.reduce(
        (total, environment) =>
            total + Number(environment.school_years_count ?? 0),
        0,
    ),
);

const applyFilters = () => {
    const params: Record<string, string> = {};

    if (search.value.trim()) {
        params.search = search.value.trim();
    }

    if (filter.value === "active") {
        params.active = "1";
    }

    if (filter.value === "inactive") {
        params.active = "0";
    }

    router.get("/environments", params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const setFilter = (value: "all" | "active" | "inactive") => {
    filter.value = value;
    applyFilters();
};

const clearSearch = () => {
    search.value = "";
    applyFilters();
};

const toggleActive = (environment: Environment) => {
    router.post(
        `/environments/${environment.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                openMenu.value = null;
            },
        },
    );
};

const visitPage = (url: string | null) => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveScroll: true,
        preserveState: true,
    });
};

const initials = (name: string) => {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
};
</script>

<template>
    <Head title="Environnements" />

    <div class="min-h-full bg-[#fafbfc]">
        <div class="mx-auto max-w-[1680px] space-y-6 p-5 md:p-7 lg:p-8">
            <!-- ================================================== -->
            <!-- HEADER                                             -->
            <!-- ================================================== -->

            <section
                class="relative overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.04)]"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-32 size-[430px] rounded-full bg-violet-100/70 blur-[90px]"
                />

                <div
                    class="pointer-events-none absolute left-[35%] top-0 size-[300px] rounded-full bg-blue-50 blur-[100px]"
                />

                <div
                    class="relative flex flex-col gap-7 p-7 md:p-9 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-start gap-5">
                        <div
                            class="flex size-16 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-violet-600 to-indigo-600 text-white shadow-[0_12px_30px_rgba(79,70,229,0.25)]"
                        >
                            <Building2 class="size-8" />
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1
                                    class="text-3xl font-bold tracking-tight text-slate-950 md:text-[36px]"
                                >
                                    Environnements
                                </h1>

                                <span
                                    class="rounded-full border border-violet-200 bg-violet-50 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-violet-700"
                                >
                                    Multi-établissements
                                </span>
                            </div>

                            <p
                                class="mt-2 max-w-3xl text-sm leading-6 text-slate-500 md:text-[15px]"
                            >
                                Gérez les établissements School Up, leurs accès,
                                leur identité visuelle et leur configuration.
                            </p>
                        </div>
                    </div>

                    <Link
                        href="/environments/create"
                        class="inline-flex h-13 shrink-0 items-center justify-center gap-2 rounded-2xl bg-[#050817] px-6 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-violet-600"
                    >
                        <Plus class="size-5" />

                        Nouvel environnement
                    </Link>
                </div>
            </section>

            <!-- ================================================== -->
            <!-- STATS                                              -->
            <!-- ================================================== -->

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400"
                            >
                                Environnements
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ environments.total }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Établissements configurés
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600"
                        >
                            <Building2 class="size-6" />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400"
                            >
                                Actifs
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ activeCount }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Sur la page actuelle
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                        >
                            <CheckCircle2 class="size-6" />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400"
                            >
                                Utilisateurs
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ totalUsers }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Affectations visibles
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600"
                        >
                            <Users class="size-6" />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400"
                            >
                                Années scolaires
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ totalSchoolYears }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Sur la page actuelle
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                        >
                            <GraduationCap class="size-6" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================== -->
            <!-- TOOLBAR                                            -->
            <!-- ================================================== -->

            <section
                class="flex flex-col gap-4 rounded-[22px] border border-slate-200 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between"
            >
                <form
                    class="relative w-full lg:max-w-xl"
                    @submit.prevent="applyFilters"
                >
                    <Search
                        class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher un établissement..."
                        class="h-13 w-full rounded-2xl border border-slate-200 bg-slate-50/50 pl-12 pr-24 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-50"
                    />

                    <button
                        v-if="search"
                        type="button"
                        class="absolute right-[75px] top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-400 hover:text-slate-700"
                        @click="clearSearch"
                    >
                        Effacer
                    </button>

                    <button
                        type="submit"
                        class="absolute right-2 top-1/2 h-9 -translate-y-1/2 rounded-xl bg-slate-950 px-4 text-xs font-bold text-white transition hover:bg-violet-600"
                    >
                        Chercher
                    </button>
                </form>

                <div
                    class="flex flex-wrap items-center rounded-xl bg-slate-100 p-1"
                >
                    <button
                        type="button"
                        class="rounded-lg px-4 py-2.5 text-xs font-bold transition"
                        :class="
                            filter === 'all'
                                ? 'bg-white text-slate-950 shadow-sm'
                                : 'text-slate-500'
                        "
                        @click="setFilter('all')"
                    >
                        Tous
                    </button>

                    <button
                        type="button"
                        class="rounded-lg px-4 py-2.5 text-xs font-bold transition"
                        :class="
                            filter === 'active'
                                ? 'bg-white text-emerald-700 shadow-sm'
                                : 'text-slate-500'
                        "
                        @click="setFilter('active')"
                    >
                        Actifs
                    </button>

                    <button
                        type="button"
                        class="rounded-lg px-4 py-2.5 text-xs font-bold transition"
                        :class="
                            filter === 'inactive'
                                ? 'bg-white text-rose-600 shadow-sm'
                                : 'text-slate-500'
                        "
                        @click="setFilter('inactive')"
                    >
                        Inactifs
                    </button>
                </div>
            </section>

            <!-- ================================================== -->
            <!-- CARDS                                              -->
            <!-- ================================================== -->

            <section>
                <div
                    v-if="environmentList.length"
                    class="grid gap-5 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="environment in environmentList"
                        :key="environment.id"
                        class="group relative overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_18px_45px_rgba(15,23,42,0.08)]"
                    >
                        <!-- COLOR BAR -->

                        <div
                            class="h-2 w-full"
                            :style="{
                                background: `linear-gradient(90deg, ${
                                    environment.primary_color || '#7c3aed'
                                }, ${
                                    environment.accent_color ||
                                    environment.secondary_color ||
                                    '#4f46e5'
                                })`,
                            }"
                        />

                        <div class="p-6">
                            <!-- TOP -->

                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-4">
                                    <div
                                        class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-slate-100 bg-slate-50"
                                    >
                                        <img
                                            v-if="environment.logo_url"
                                            :src="environment.logo_url"
                                            :alt="environment.name"
                                            class="max-h-10 max-w-10 object-contain"
                                        />

                                        <span
                                            v-else
                                            class="text-sm font-black text-slate-500"
                                        >
                                            {{ initials(environment.name) }}
                                        </span>
                                    </div>

                                    <div class="min-w-0">
                                        <h2
                                            class="truncate text-lg font-bold text-slate-950"
                                        >
                                            {{ environment.name }}
                                        </h2>

                                        <div
                                            class="mt-1.5 flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-md bg-slate-100 px-2.5 py-1 font-mono text-[10px] font-bold text-slate-500"
                                            >
                                                {{ environment.code }}
                                            </span>

                                            <span
                                                v-if="environment.active"
                                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700"
                                            >
                                                <span
                                                    class="size-1.5 rounded-full bg-emerald-500"
                                                />

                                                Actif
                                            </span>

                                            <span
                                                v-else
                                                class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-600"
                                            >
                                                <span
                                                    class="size-1.5 rounded-full bg-rose-400"
                                                />

                                                Inactif
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="relative">
                                    <button
                                        type="button"
                                        class="flex size-9 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                        @click="
                                            openMenu =
                                                openMenu === environment.id
                                                    ? null
                                                    : environment.id
                                        "
                                    >
                                        <MoreHorizontal class="size-5" />
                                    </button>

                                    <div
                                        v-if="openMenu === environment.id"
                                        class="absolute right-0 top-11 z-40 w-52 rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_18px_50px_rgba(15,23,42,0.15)]"
                                    >
                                        <Link
                                            :href="`/environments/${environment.id}/edit`"
                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                        >
                                            <Pencil class="size-4" />

                                            Modifier
                                        </Link>

                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                            @click="toggleActive(environment)"
                                        >
                                            <CircleSlash2
                                                v-if="environment.active"
                                                class="size-4 text-amber-500"
                                            />

                                            <CheckCircle2
                                                v-else
                                                class="size-4 text-emerald-500"
                                            />

                                            {{
                                                environment.active
                                                    ? "Désactiver"
                                                    : "Activer"
                                            }}
                                        </button>

                                        <a
                                            v-if="environment.url"
                                            :href="environment.url"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                                        >
                                            <ExternalLink class="size-4" />

                                            Ouvrir le site
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- APP -->

                            <div class="mt-6 rounded-2xl bg-slate-50 p-4">
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400"
                                        >
                                            Application
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-bold text-slate-700"
                                        >
                                            {{
                                                environment.app_name ||
                                                "School Up"
                                            }}
                                        </p>
                                    </div>

                                    <div
                                        v-if="environment.current_exercise"
                                        class="text-right"
                                    >
                                        <p
                                            class="text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400"
                                        >
                                            Exercice
                                        </p>

                                        <p
                                            class="mt-1 text-sm font-bold text-slate-700"
                                        >
                                            {{ environment.current_exercise }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- COUNTS -->

                            <div class="mt-4 grid grid-cols-2 gap-3">
                                <div
                                    class="rounded-2xl border border-slate-100 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <p
                                                class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                            >
                                                Utilisateurs
                                            </p>

                                            <p
                                                class="mt-1 text-2xl font-bold text-slate-950"
                                            >
                                                {{
                                                    environment.users_count ?? 0
                                                }}
                                            </p>
                                        </div>

                                        <div
                                            class="flex size-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"
                                        >
                                            <Users class="size-4" />
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-slate-100 p-4"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div>
                                            <p
                                                class="text-[9px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                            >
                                                Années
                                            </p>

                                            <p
                                                class="mt-1 text-2xl font-bold text-slate-950"
                                            >
                                                {{
                                                    environment.school_years_count ??
                                                    0
                                                }}
                                            </p>
                                        </div>

                                        <div
                                            class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                                        >
                                            <GraduationCap class="size-4" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BRANDING -->

                            <div
                                class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"
                            >
                                <div class="flex items-center gap-2">
                                    <Palette class="size-4 text-slate-400" />

                                    <span
                                        class="text-xs font-semibold text-slate-400"
                                    >
                                        Identité visuelle
                                    </span>
                                </div>

                                <div class="flex items-center -space-x-1">
                                    <span
                                        class="size-5 rounded-full border-2 border-white shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                environment.primary_color ||
                                                '#8B1E2D',
                                        }"
                                    />

                                    <span
                                        class="size-5 rounded-full border-2 border-white shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                environment.secondary_color ||
                                                '#641520',
                                        }"
                                    />

                                    <span
                                        class="size-5 rounded-full border-2 border-white shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                environment.sidebar_color ||
                                                '#111827',
                                        }"
                                    />

                                    <span
                                        class="size-5 rounded-full border-2 border-white shadow-sm"
                                        :style="{
                                            backgroundColor:
                                                environment.accent_color ||
                                                '#D4AF37',
                                        }"
                                    />
                                </div>
                            </div>

                            <Link
                                :href="`/environments/${environment.id}/edit`"
                                class="mt-5 flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 text-xs font-bold text-white transition hover:bg-violet-600"
                            >
                                <Pencil class="size-4" />

                                Gérer l'environnement
                            </Link>
                        </div>
                    </article>
                </div>

                <!-- EMPTY -->

                <div
                    v-else
                    class="flex min-h-[350px] flex-col items-center justify-center rounded-[26px] border border-dashed border-slate-300 bg-white p-8 text-center"
                >
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl bg-violet-50 text-violet-500"
                    >
                        <Building2 class="size-8" />
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                        Aucun environnement trouvé
                    </h3>

                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Aucun établissement ne correspond aux critères
                        sélectionnés.
                    </p>

                    <Link
                        href="/environments/create"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-bold text-white transition hover:bg-violet-600"
                    >
                        <Plus class="size-4" />

                        Créer un environnement
                    </Link>
                </div>
            </section>

            <!-- ================================================== -->
            <!-- PAGINATION                                         -->
            <!-- ================================================== -->

            <section
                v-if="environments.last_page > 1"
                class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs text-slate-400">
                    Affichage

                    <span class="font-bold text-slate-700">
                        {{ environments.from ?? 0 }}
                    </span>

                    à

                    <span class="font-bold text-slate-700">
                        {{ environments.to ?? 0 }}
                    </span>

                    sur

                    <span class="font-bold text-slate-700">
                        {{ environments.total }}
                    </span>
                </p>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        :disabled="!environments.prev_page_url"
                        class="flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30"
                        @click="visitPage(environments.prev_page_url)"
                    >
                        <ChevronLeft class="size-4" />
                    </button>

                    <div
                        class="flex h-10 items-center rounded-xl bg-slate-100 px-4 text-xs font-bold text-slate-600"
                    >
                        Page
                        {{ environments.current_page }}
                        /
                        {{ environments.last_page }}
                    </div>

                    <button
                        type="button"
                        :disabled="!environments.next_page_url"
                        class="flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30"
                        @click="visitPage(environments.next_page_url)"
                    >
                        <ChevronRight class="size-4" />
                    </button>
                </div>
            </section>
        </div>
    </div>
</template>
