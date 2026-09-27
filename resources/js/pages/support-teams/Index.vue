<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";

import {
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleSlash2,
    Headphones,
    MoreHorizontal,
    Pencil,
    Plus,
    Search,
    ShieldCheck,
    TicketCheck,
    Trash2,
    UserRound,
    UsersRound,
} from "lucide-vue-next";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type User = {
    id: number;
    name?: string | null;
    first_name?: string | null;
    last_name?: string | null;
    email?: string | null;
};

type SupportTeam = {
    id: number;
    environment_id?: number;
    name: string;
    code: string;
    description?: string | null;
    leader_id?: number | null;
    leader?: User | null;
    active: boolean;
    users_count?: number;
    tickets_count?: number;
    created_at?: string | null;
    updated_at?: string | null;
};

type Environment = {
    id: number;
    name: string;
    code: string;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedTeams = {
    current_page: number;
    data: SupportTeam[];
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
    teams: PaginatedTeams;

    filters?: {
        search?: string | null;
        active?: string | null;
    };

    currentEnvironment: Environment | null;
}>();

/* -------------------------------------------------------------------------- */
/* State                                                                      */
/* -------------------------------------------------------------------------- */

const search = ref(props.filters?.search ?? "");

const filter = ref<"all" | "active" | "inactive">(
    props.filters?.active === "1"
        ? "active"
        : props.filters?.active === "0"
          ? "inactive"
          : "all",
);

const openMenu = ref<number | null>(null);

/* -------------------------------------------------------------------------- */
/* Data                                                                       */
/* -------------------------------------------------------------------------- */

const teamList = computed(() => props.teams?.data ?? []);

const activeCount = computed(
    () => teamList.value.filter((team) => team.active).length,
);

const inactiveCount = computed(
    () => teamList.value.filter((team) => !team.active).length,
);

const membersCount = computed(() =>
    teamList.value.reduce(
        (total, team) => total + Number(team.users_count ?? 0),
        0,
    ),
);

const ticketsCount = computed(() =>
    teamList.value.reduce(
        (total, team) => total + Number(team.tickets_count ?? 0),
        0,
    ),
);

/* -------------------------------------------------------------------------- */
/* Helpers                                                                    */
/* -------------------------------------------------------------------------- */

const getLeaderName = (team: SupportTeam) => {
    if (!team.leader) {
        return "Non défini";
    }

    if (team.leader.name) {
        return team.leader.name;
    }

    const fullName = [team.leader.first_name, team.leader.last_name]
        .filter(Boolean)
        .join(" ");

    return fullName || "Non défini";
};

const getInitials = (name: string) => {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
};

/* -------------------------------------------------------------------------- */
/* Filters                                                                    */
/* -------------------------------------------------------------------------- */

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

    router.get("/support-teams", params, {
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

/* -------------------------------------------------------------------------- */
/* Actions                                                                    */
/* -------------------------------------------------------------------------- */

const toggleActive = (team: SupportTeam) => {
    router.post(
        `/support-teams/${team.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                openMenu.value = null;
            },
        },
    );
};

const deleteTeam = (team: SupportTeam) => {
    if (!confirm(`Voulez-vous vraiment supprimer l'équipe "${team.name}" ?`)) {
        return;
    }

    router.delete(`/support-teams/${team.id}`, {
        preserveScroll: true,

        onFinish: () => {
            openMenu.value = null;
        },
    });
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
</script>

<template>
    <Head title="Équipes de support" />

    <div class="min-h-full bg-[#fafbfc]">
        <div class="mx-auto max-w-[1680px] space-y-6 p-5 md:p-7 lg:p-8">
            <!-- ====================================================== -->
            <!-- HEADER                                                 -->
            <!-- ====================================================== -->

            <section
                class="relative overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.04)]"
            >
                <!-- Background decoration -->

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
                            <Headphones class="size-8" />
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1
                                    class="text-3xl font-bold tracking-tight text-slate-950 md:text-[36px]"
                                >
                                    Équipes de support
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
                                Organisez les équipes responsables du traitement
                                des réclamations, leurs membres et leurs
                                responsables.
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
                        href="/support-teams/create"
                        class="inline-flex h-13 shrink-0 items-center justify-center gap-2 rounded-2xl bg-[#050817] px-6 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-emerald-600"
                    >
                        <Plus class="size-5" />

                        Nouvelle équipe
                    </Link>
                </div>
            </section>

            <!-- ====================================================== -->
            <!-- STATISTICS                                             -->
            <!-- ====================================================== -->

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <!-- Total -->

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Total équipes
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ teams.total }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Dans cet établissement
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
                        >
                            <UsersRound class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Active -->

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Actives
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
                            <ShieldCheck class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Members -->

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Membres
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ membersCount }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Sur la page actuelle
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600"
                        >
                            <UserRound class="size-6" />
                        </div>
                    </div>
                </div>

                <!-- Tickets -->

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Tickets liés
                            </p>

                            <p
                                class="mt-3 text-3xl font-bold tracking-tight text-slate-950"
                            >
                                {{ ticketsCount }}
                            </p>

                            <p class="mt-1 text-xs font-medium text-slate-400">
                                Sur la page actuelle
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                        >
                            <TicketCheck class="size-6" />
                        </div>
                    </div>
                </div>
            </section>

            <!-- ====================================================== -->
            <!-- LIST                                                   -->
            <!-- ====================================================== -->

            <section
                class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-[0_8px_30px_rgba(15,23,42,0.04)]"
            >
                <!-- Toolbar -->

                <div
                    class="flex flex-col gap-4 border-b border-slate-100 p-5 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- Search -->

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
                            placeholder="Rechercher par nom, code ou description..."
                            class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 pl-12 pr-24 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                        />

                        <button
                            v-if="search"
                            type="button"
                            class="absolute right-[76px] top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400 hover:text-slate-700"
                            @click="clearSearch"
                        >
                            Effacer
                        </button>

                        <button
                            type="submit"
                            class="absolute right-2 top-1/2 flex h-10 -translate-y-1/2 items-center justify-center rounded-xl bg-slate-950 px-4 text-xs font-bold text-white transition hover:bg-emerald-600"
                        >
                            Chercher
                        </button>
                    </form>

                    <!-- Filters -->

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl px-4 py-3 text-sm font-bold transition"
                            :class="
                                filter === 'all'
                                    ? 'bg-[#050817] text-white shadow-sm'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                            "
                            @click="setFilter('all')"
                        >
                            Toutes

                            <span class="ml-1 text-xs opacity-60">
                                {{ teams.total }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-4 py-3 text-sm font-bold transition"
                            :class="
                                filter === 'active'
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                            "
                            @click="setFilter('active')"
                        >
                            Actives
                        </button>

                        <button
                            type="button"
                            class="rounded-xl px-4 py-3 text-sm font-bold transition"
                            :class="
                                filter === 'inactive'
                                    ? 'bg-rose-100 text-rose-700'
                                    : 'bg-slate-100 text-slate-500 hover:bg-slate-200'
                            "
                            @click="setFilter('inactive')"
                        >
                            Inactives
                        </button>
                    </div>
                </div>

                <!-- ================================================== -->
                <!-- DESKTOP TABLE                                      -->
                <!-- ================================================== -->

                <div class="hidden overflow-x-auto lg:block">
                    <table class="w-full">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/60"
                            >
                                <th
                                    class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Équipe
                                </th>

                                <th
                                    class="px-6 py-4 text-left text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Responsable
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Membres
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Tickets
                                </th>

                                <th
                                    class="px-6 py-4 text-center text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Statut
                                </th>

                                <th class="w-20 px-6 py-4" />
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="team in teamList"
                                :key="team.id"
                                class="group transition hover:bg-slate-50/60"
                            >
                                <!-- TEAM -->

                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-[#050817] text-white shadow-sm"
                                        >
                                            <Headphones class="size-5" />
                                        </div>

                                        <div class="min-w-0">
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <Link
                                                    :href="`/support-teams/${team.id}/edit`"
                                                    class="font-bold text-slate-900 transition hover:text-emerald-600"
                                                >
                                                    {{ team.name }}
                                                </Link>

                                                <span
                                                    class="rounded-md bg-slate-100 px-2 py-1 font-mono text-[10px] font-bold text-slate-500"
                                                >
                                                    {{ team.code }}
                                                </span>
                                            </div>

                                            <p
                                                class="mt-1 max-w-md truncate text-xs text-slate-400"
                                            >
                                                {{
                                                    team.description ||
                                                    "Aucune description"
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- LEADER -->

                                <td class="px-6 py-5">
                                    <div
                                        v-if="team.leader"
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700"
                                        >
                                            {{
                                                getInitials(getLeaderName(team))
                                            }}
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-sm font-semibold text-slate-700"
                                            >
                                                {{ getLeaderName(team) }}
                                            </p>

                                            <p
                                                class="truncate text-xs text-slate-400"
                                            >
                                                {{ team.leader.email }}
                                            </p>
                                        </div>
                                    </div>

                                    <span v-else class="text-sm text-slate-400">
                                        Non défini
                                    </span>
                                </td>

                                <!-- MEMBERS -->

                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex min-w-10 items-center justify-center rounded-xl bg-sky-50 px-3 py-2 text-sm font-bold text-sky-700"
                                    >
                                        {{ team.users_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- TICKETS -->

                                <td class="px-6 py-5 text-center">
                                    <span
                                        class="inline-flex min-w-10 items-center justify-center rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-700"
                                    >
                                        {{ team.tickets_count ?? 0 }}
                                    </span>
                                </td>

                                <!-- STATUS -->

                                <td class="px-6 py-5 text-center">
                                    <span
                                        v-if="team.active"
                                        class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700"
                                    >
                                        <span
                                            class="size-2 rounded-full bg-emerald-500"
                                        />

                                        Active
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500"
                                    >
                                        <span
                                            class="size-2 rounded-full bg-slate-400"
                                        />

                                        Inactive
                                    </span>
                                </td>

                                <!-- ACTIONS -->

                                <td class="relative px-6 py-5">
                                    <button
                                        type="button"
                                        class="flex size-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                        @click="
                                            openMenu =
                                                openMenu === team.id
                                                    ? null
                                                    : team.id
                                        "
                                    >
                                        <MoreHorizontal class="size-5" />
                                    </button>

                                    <div
                                        v-if="openMenu === team.id"
                                        class="absolute right-6 top-14 z-30 w-52 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-xl"
                                    >
                                        <Link
                                            :href="`/support-teams/${team.id}/edit`"
                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                        >
                                            <Pencil class="size-4" />

                                            Modifier
                                        </Link>

                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                            @click="toggleActive(team)"
                                        >
                                            <CheckCircle2
                                                v-if="!team.active"
                                                class="size-4 text-emerald-500"
                                            />

                                            <CircleSlash2
                                                v-else
                                                class="size-4 text-amber-500"
                                            />

                                            {{
                                                team.active
                                                    ? "Désactiver"
                                                    : "Activer"
                                            }}
                                        </button>

                                        <div
                                            class="my-1 border-t border-slate-100"
                                        />

                                        <button
                                            type="button"
                                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50"
                                            @click="deleteTeam(team)"
                                        >
                                            <Trash2 class="size-4" />

                                            Supprimer
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ================================================== -->
                <!-- MOBILE                                             -->
                <!-- ================================================== -->

                <div class="grid gap-4 p-4 lg:hidden">
                    <article
                        v-for="team in teamList"
                        :key="team.id"
                        class="rounded-2xl border border-slate-200 p-5"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-[#050817] text-white"
                                >
                                    <Headphones class="size-5" />
                                </div>

                                <div>
                                    <p class="font-bold text-slate-900">
                                        {{ team.name }}
                                    </p>

                                    <p
                                        class="mt-0.5 font-mono text-[10px] font-bold text-slate-400"
                                    >
                                        {{ team.code }}
                                    </p>
                                </div>
                            </div>

                            <span
                                class="rounded-full px-3 py-1.5 text-xs font-bold"
                                :class="
                                    team.active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500'
                                "
                            >
                                {{ team.active ? "Active" : "Inactive" }}
                            </span>
                        </div>

                        <p class="mt-4 text-sm leading-6 text-slate-500">
                            {{ team.description || "Aucune description." }}
                        </p>

                        <div class="mt-5 grid grid-cols-3 gap-2">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Responsable
                                </p>

                                <p
                                    class="mt-1 truncate text-xs font-bold text-slate-700"
                                >
                                    {{ getLeaderName(team) }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-sky-50 p-3 text-center">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-sky-400"
                                >
                                    Membres
                                </p>

                                <p class="mt-1 font-bold text-sky-700">
                                    {{ team.users_count ?? 0 }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-amber-50 p-3 text-center">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-amber-400"
                                >
                                    Tickets
                                </p>

                                <p class="mt-1 font-bold text-amber-700">
                                    {{ team.tickets_count ?? 0 }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex gap-2 border-t border-slate-100 pt-4"
                        >
                            <Link
                                :href="`/support-teams/${team.id}/edit`"
                                class="flex flex-1 items-center justify-center gap-2 rounded-xl bg-slate-100 px-4 py-3 text-xs font-bold text-slate-700"
                            >
                                <Pencil class="size-4" />

                                Modifier
                            </Link>

                            <button
                                type="button"
                                class="flex items-center justify-center rounded-xl bg-rose-50 px-4 text-rose-600"
                                @click="deleteTeam(team)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </article>
                </div>

                <!-- ================================================== -->
                <!-- EMPTY STATE                                        -->
                <!-- ================================================== -->

                <div
                    v-if="teamList.length === 0"
                    class="flex flex-col items-center justify-center px-6 py-20 text-center"
                >
                    <div
                        class="flex size-16 items-center justify-center rounded-[20px] bg-slate-100 text-slate-400"
                    >
                        <Headphones class="size-8" />
                    </div>

                    <h3 class="mt-5 text-lg font-bold text-slate-900">
                        Aucune équipe trouvée
                    </h3>

                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Aucune équipe de support ne correspond actuellement aux
                        critères sélectionnés.
                    </p>

                    <Link
                        href="/support-teams/create"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-[#050817] px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald-600"
                    >
                        <Plus class="size-4" />

                        Créer une équipe
                    </Link>
                </div>

                <!-- ================================================== -->
                <!-- PAGINATION                                         -->
                <!-- ================================================== -->

                <div
                    v-if="teams.last_page > 1"
                    class="flex flex-col gap-4 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-sm text-slate-400">
                        Affichage

                        <span class="font-bold text-slate-700">
                            {{ teams.from ?? 0 }}
                        </span>

                        à

                        <span class="font-bold text-slate-700">
                            {{ teams.to ?? 0 }}
                        </span>

                        sur

                        <span class="font-bold text-slate-700">
                            {{ teams.total }}
                        </span>

                        équipes
                    </p>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            :disabled="!teams.prev_page_url"
                            class="flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30"
                            @click="visitPage(teams.prev_page_url)"
                        >
                            <ChevronLeft class="size-4" />
                        </button>

                        <div
                            class="flex h-10 items-center rounded-xl bg-slate-100 px-4 text-xs font-bold text-slate-600"
                        >
                            Page
                            {{ teams.current_page }}
                            /
                            {{ teams.last_page }}
                        </div>

                        <button
                            type="button"
                            :disabled="!teams.next_page_url"
                            class="flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-30"
                            @click="visitPage(teams.next_page_url)"
                        >
                            <ChevronRight class="size-4" />
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
