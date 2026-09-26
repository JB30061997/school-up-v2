<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import {
    AlertCircle,
    ArrowRight,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    CircleDot,
    Clock3,
    FileText,
    Filter,
    Inbox,
    LoaderCircle,
    Plus,
    Search,
    SlidersHorizontal,
    UserRound,
    X,
} from "@lucide/vue";
import { computed, ref, watch } from "vue";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type User = {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
};

type RequestType = {
    id: number;
    name: string;
    code: string;
};

type InternalRequest = {
    id: number;
    reference: string;
    subject: string;
    description: string;
    status: string;
    priority: string;

    submitted_at?: string | null;
    assigned_at?: string | null;
    started_at?: string | null;
    due_at?: string | null;
    approved_at?: string | null;
    rejected_at?: string | null;
    completed_at?: string | null;

    created_at: string;
    updated_at: string;

    type?: RequestType | null;
    creator?: User | null;
    assigned_to?: User | null;
    assignedTo?: User | null;
    approved_by?: User | null;
    approvedBy?: User | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedRequests = {
    data: InternalRequest[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
    prev_page_url?: string | null;
    next_page_url?: string | null;
};

type Filters = {
    search?: string | null;
    status?: string | null;
    priority?: string | null;
    request_type_id?: number | string | null;
};

const props = defineProps<{
    requests: PaginatedRequests;
    types: RequestType[];
    filters: Filters;
    statuses: Record<string, string>;
    priorities: Record<string, string>;
}>();

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "");
const priority = ref(props.filters.priority ?? "");
const requestTypeId = ref(
    props.filters.request_type_id ? String(props.filters.request_type_id) : "",
);

const filtersOpen = ref(false);

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const submitFilters = (): void => {
    router.get(
        "/requests",
        {
            search: search.value || undefined,
            status: status.value || undefined,
            priority: priority.value || undefined,
            request_type_id: requestTypeId.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        submitFilters();
    }, 400);
});

watch([status, priority, requestTypeId], () => {
    submitFilters();
});

const resetFilters = (): void => {
    search.value = "";
    status.value = "";
    priority.value = "";
    requestTypeId.value = "";

    router.get(
        "/requests",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

type TabKey = "ALL" | "NEW" | "IN_PROGRESS" | "COMPLETED";

const activeTab = computed<TabKey>(() => {
    if (status.value === "SUBMITTED" || status.value === "ASSIGNED") {
        return "NEW";
    }

    if (status.value === "IN_PROGRESS" || status.value === "APPROVED") {
        return "IN_PROGRESS";
    }

    if (
        status.value === "COMPLETED" ||
        status.value === "CANCELLED" ||
        status.value === "REJECTED"
    ) {
        return "COMPLETED";
    }

    return "ALL";
});

const setTab = (tab: TabKey): void => {
    if (tab === "ALL") {
        status.value = "";
        return;
    }

    if (tab === "NEW") {
        status.value = "SUBMITTED";
        return;
    }

    if (tab === "IN_PROGRESS") {
        status.value = "IN_PROGRESS";
        return;
    }

    status.value = "COMPLETED";
};

/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
|
| These stats represent the currently loaded Laravel page.
|--------------------------------------------------------------------------
*/

const pageRequests = computed(() => props.requests.data ?? []);

const submittedCount = computed(
    () =>
        pageRequests.value.filter(
            (item) => item.status === "SUBMITTED" || item.status === "ASSIGNED",
        ).length,
);

const inProgressCount = computed(
    () =>
        pageRequests.value.filter(
            (item) =>
                item.status === "IN_PROGRESS" || item.status === "APPROVED",
        ).length,
);

const completedCount = computed(
    () =>
        pageRequests.value.filter((item) => item.status === "COMPLETED").length,
);

const urgentCount = computed(
    () =>
        pageRequests.value.filter((item) => item.priority === "URGENT").length,
);

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const userName = (user?: User | null): string => {
    if (!user) {
        return "Non affecté";
    }

    const fullName = [user.first_name, user.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return fullName || user.name || "Utilisateur";
};

const initials = (user?: User | null): string => {
    if (!user) {
        return "—";
    }

    const value = userName(user);

    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
};

const assignedUser = (item: InternalRequest): User | null | undefined => {
    return item.assignedTo ?? item.assigned_to;
};

const formatDate = (value?: string | null): string => {
    if (!value) {
        return "—";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(new Date(value));
};

const formatDateTime = (value?: string | null): string => {
    if (!value) {
        return "—";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(value));
};

const statusLabel = (value: string): string => {
    return props.statuses[value] ?? value;
};

const priorityLabel = (value: string): string => {
    return props.priorities[value] ?? value;
};

const statusClass = (value: string): string => {
    switch (value) {
        case "DRAFT":
            return "border-slate-200 bg-slate-50 text-slate-600";

        case "SUBMITTED":
            return "border-amber-200 bg-amber-50 text-amber-700";

        case "ASSIGNED":
            return "border-sky-200 bg-sky-50 text-sky-700";

        case "IN_PROGRESS":
            return "border-blue-200 bg-blue-50 text-blue-700";

        case "APPROVED":
            return "border-emerald-200 bg-emerald-50 text-emerald-700";

        case "COMPLETED":
            return "border-green-200 bg-green-50 text-green-700";

        case "REJECTED":
            return "border-rose-200 bg-rose-50 text-rose-700";

        case "CANCELLED":
            return "border-slate-200 bg-slate-100 text-slate-500";

        default:
            return "border-slate-200 bg-slate-50 text-slate-600";
    }
};

const statusDotClass = (value: string): string => {
    switch (value) {
        case "SUBMITTED":
            return "bg-amber-500";

        case "ASSIGNED":
            return "bg-sky-500";

        case "IN_PROGRESS":
            return "bg-blue-500";

        case "APPROVED":
            return "bg-emerald-500";

        case "COMPLETED":
            return "bg-green-500";

        case "REJECTED":
            return "bg-rose-500";

        case "CANCELLED":
            return "bg-slate-400";

        default:
            return "bg-slate-400";
    }
};

const priorityClass = (value: string): string => {
    switch (value) {
        case "LOW":
            return "border-slate-200 bg-slate-50 text-slate-600";

        case "NORMAL":
            return "border-emerald-200 bg-emerald-50 text-emerald-700";

        case "HIGH":
            return "border-orange-200 bg-orange-50 text-orange-700";

        case "URGENT":
            return "border-red-200 bg-red-50 text-red-700";

        default:
            return "border-slate-200 bg-slate-50 text-slate-600";
    }
};

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value || status.value || priority.value || requestTypeId.value,
    );
});

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const visitPage = (url: string | null): void => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Demandes" />

    <div class="min-h-full bg-slate-50/50">
        <!-- ========================================================= -->
        <!-- PAGE HEADER -->
        <!-- ========================================================= -->

        <div class="border-b border-slate-200 bg-white">
            <div
                class="flex flex-col gap-5 px-6 py-6 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="min-w-0">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-sm shadow-emerald-200"
                        >
                            <ClipboardList class="size-5" />
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h1
                                    class="text-2xl font-black tracking-tight text-slate-950"
                                >
                                    Gestion des demandes
                                </h1>

                                <span
                                    class="hidden rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 sm:inline-flex"
                                >
                                    Back office
                                </span>
                            </div>

                            <p class="mt-1 text-sm text-slate-500">
                                Centralisez, affectez et suivez les demandes
                                internes de l'établissement.
                            </p>
                        </div>
                    </div>
                </div>

                <Link
                    href="/requests/create"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700"
                >
                    <Plus class="size-4" />

                    Nouvelle demande
                </Link>
            </div>
        </div>

        <div class="space-y-5 p-4 sm:p-6">
            <!-- ===================================================== -->
            <!-- KPI -->
            <!-- ===================================================== -->

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <!-- Total -->

                <div
                    class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                Total demandes
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight text-slate-950"
                            >
                                {{ requests.total }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Toutes les demandes
                            </p>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
                        >
                            <Inbox class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- New -->

                <div
                    class="group rounded-2xl border border-amber-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                Nouvelles
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight text-slate-950"
                            >
                                {{ submittedCount }}
                            </p>

                            <p class="mt-1 text-xs text-amber-600">
                                À prendre en charge
                            </p>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                        >
                            <CircleDot class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- Progress -->

                <div
                    class="group rounded-2xl border border-blue-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                En cours
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight text-slate-950"
                            >
                                {{ inProgressCount }}
                            </p>

                            <p class="mt-1 text-xs text-blue-600">
                                En traitement
                            </p>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <LoaderCircle class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- Completed -->

                <div
                    class="group rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p
                                class="text-xs font-bold uppercase tracking-wider text-slate-400"
                            >
                                Terminées
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight text-slate-950"
                            >
                                {{ completedCount }}
                            </p>

                            <p class="mt-1 text-xs text-emerald-600">
                                Demandes finalisées
                            </p>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <CheckCircle2 class="size-5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- MAIN CARD -->
            <!-- ===================================================== -->

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- TABS -->

                <div class="border-b border-slate-200 px-4 pt-4 sm:px-5">
                    <div
                        class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
                    >
                        <div class="flex min-w-0 gap-1 overflow-x-auto">
                            <button
                                type="button"
                                class="relative whitespace-nowrap rounded-t-lg px-4 pb-3 pt-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'ALL'
                                        ? 'text-emerald-700'
                                        : 'text-slate-500 hover:text-slate-900'
                                "
                                @click="setTab('ALL')"
                            >
                                Toutes

                                <span
                                    v-if="activeTab === 'ALL'"
                                    class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-emerald-500"
                                />
                            </button>

                            <button
                                type="button"
                                class="relative whitespace-nowrap rounded-t-lg px-4 pb-3 pt-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'NEW'
                                        ? 'text-emerald-700'
                                        : 'text-slate-500 hover:text-slate-900'
                                "
                                @click="setTab('NEW')"
                            >
                                Nouvelles

                                <span
                                    v-if="activeTab === 'NEW'"
                                    class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-emerald-500"
                                />
                            </button>

                            <button
                                type="button"
                                class="relative whitespace-nowrap rounded-t-lg px-4 pb-3 pt-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'IN_PROGRESS'
                                        ? 'text-emerald-700'
                                        : 'text-slate-500 hover:text-slate-900'
                                "
                                @click="setTab('IN_PROGRESS')"
                            >
                                En cours

                                <span
                                    v-if="activeTab === 'IN_PROGRESS'"
                                    class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-emerald-500"
                                />
                            </button>

                            <button
                                type="button"
                                class="relative whitespace-nowrap rounded-t-lg px-4 pb-3 pt-2 text-sm font-semibold transition"
                                :class="
                                    activeTab === 'COMPLETED'
                                        ? 'text-emerald-700'
                                        : 'text-slate-500 hover:text-slate-900'
                                "
                                @click="setTab('COMPLETED')"
                            >
                                Traitées

                                <span
                                    v-if="activeTab === 'COMPLETED'"
                                    class="absolute inset-x-2 bottom-0 h-0.5 rounded-full bg-emerald-500"
                                />
                            </button>
                        </div>

                        <div class="flex items-center gap-2 pb-3">
                            <span
                                v-if="urgentCount > 0"
                                class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-bold text-red-600"
                            >
                                <AlertCircle class="size-3.5" />

                                {{ urgentCount }} urgente(s)
                            </span>
                        </div>
                    </div>
                </div>

                <!-- SEARCH / FILTERS -->

                <div
                    class="border-b border-slate-100 bg-slate-50/40 p-4 sm:p-5"
                >
                    <div
                        class="flex flex-col gap-3 xl:flex-row xl:items-center"
                    >
                        <div class="relative min-w-0 flex-1">
                            <Search
                                class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Rechercher par référence, objet ou description..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                            />
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50"
                            @click="filtersOpen = !filtersOpen"
                        >
                            <SlidersHorizontal class="size-4" />

                            Filtres

                            <span
                                v-if="hasActiveFilters"
                                class="size-2 rounded-full bg-emerald-500"
                            />
                        </button>

                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl px-3 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                            @click="resetFilters"
                        >
                            <X class="size-4" />

                            Réinitialiser
                        </button>
                    </div>

                    <div
                        v-if="filtersOpen"
                        class="mt-4 grid gap-3 border-t border-slate-200 pt-4 md:grid-cols-3"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-500"
                            >
                                Statut
                            </label>

                            <select
                                v-model="status"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-400"
                            >
                                <option value="">Tous les statuts</option>

                                <option
                                    v-for="(label, value) in statuses"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-500"
                            >
                                Priorité
                            </label>

                            <select
                                v-model="priority"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-400"
                            >
                                <option value="">Toutes les priorités</option>

                                <option
                                    v-for="(label, value) in priorities"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-xs font-bold text-slate-500"
                            >
                                Type de demande
                            </label>

                            <select
                                v-model="requestTypeId"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm outline-none focus:border-emerald-400"
                            >
                                <option value="">Tous les types</option>

                                <option
                                    v-for="type in types"
                                    :key="type.id"
                                    :value="String(type.id)"
                                >
                                    {{ type.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- TABLE -->
                <!-- ================================================= -->

                <div v-if="requests.data.length" class="overflow-x-auto">
                    <table class="w-full min-w-[1100px] border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 bg-white">
                                <th
                                    class="px-5 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Demande
                                </th>

                                <th
                                    class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Type
                                </th>

                                <th
                                    class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Demandeur
                                </th>

                                <th
                                    class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Affecté à
                                </th>

                                <th
                                    class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Priorité
                                </th>

                                <th
                                    class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Statut
                                </th>

                                <th
                                    class="px-4 py-3.5 text-left text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Échéance
                                </th>

                                <th class="w-16 px-5 py-3.5" />
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="item in requests.data"
                                :key="item.id"
                                class="group border-b border-slate-100 transition last:border-0 hover:bg-emerald-50/30"
                            >
                                <!-- Request -->

                                <td class="px-5 py-4">
                                    <Link
                                        :href="`/requests/${item.id}`"
                                        class="block"
                                    >
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="mt-0.5 flex size-9 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600 transition group-hover:border-emerald-200 group-hover:bg-emerald-100"
                                            >
                                                <FileText class="size-4" />
                                            </div>

                                            <div class="min-w-0">
                                                <div
                                                    class="flex items-center gap-2"
                                                >
                                                    <span
                                                        class="text-[11px] font-black uppercase tracking-wide text-emerald-600"
                                                    >
                                                        {{ item.reference }}
                                                    </span>
                                                </div>

                                                <p
                                                    class="mt-1 max-w-[310px] truncate text-sm font-bold text-slate-900"
                                                >
                                                    {{ item.subject }}
                                                </p>

                                                <p
                                                    class="mt-1 text-[11px] text-slate-400"
                                                >
                                                    {{
                                                        formatDateTime(
                                                            item.submitted_at ||
                                                                item.created_at,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                </td>

                                <!-- Type -->

                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex max-w-[180px] items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700"
                                    >
                                        {{ item.type?.name ?? "—" }}
                                    </span>
                                </td>

                                <!-- Creator -->

                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-100 to-green-50 text-[10px] font-black text-emerald-700 ring-1 ring-emerald-200"
                                        >
                                            {{ initials(item.creator) }}
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="max-w-[150px] truncate text-xs font-bold text-slate-800"
                                            >
                                                {{ userName(item.creator) }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-[10px] text-slate-400"
                                            >
                                                Demandeur
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Assigned -->

                                <td class="px-4 py-4">
                                    <div
                                        v-if="assignedUser(item)"
                                        class="flex items-center gap-2.5"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-black text-slate-600"
                                        >
                                            {{ initials(assignedUser(item)) }}
                                        </div>

                                        <span
                                            class="max-w-[140px] truncate text-xs font-semibold text-slate-700"
                                        >
                                            {{ userName(assignedUser(item)) }}
                                        </span>
                                    </div>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400"
                                    >
                                        <UserRound class="size-3.5" />

                                        Non affecté
                                    </span>
                                </td>

                                <!-- Priority -->

                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full border px-2.5 py-1 text-[11px] font-bold"
                                        :class="priorityClass(item.priority)"
                                    >
                                        {{ priorityLabel(item.priority) }}
                                    </span>
                                </td>

                                <!-- Status -->

                                <td class="px-4 py-4">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold"
                                        :class="statusClass(item.status)"
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="statusDotClass(item.status)"
                                        />

                                        {{ statusLabel(item.status) }}
                                    </span>
                                </td>

                                <!-- Due -->

                                <td class="px-4 py-4">
                                    <div
                                        class="flex items-center gap-1.5 text-xs text-slate-600"
                                    >
                                        <Clock3
                                            class="size-3.5 text-slate-400"
                                        />

                                        {{ formatDate(item.due_at) }}
                                    </div>
                                </td>

                                <!-- Action -->

                                <td class="px-5 py-4">
                                    <Link
                                        :href="`/requests/${item.id}`"
                                        class="flex size-8 items-center justify-center rounded-lg border border-transparent text-slate-400 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                                        title="Voir la demande"
                                    >
                                        <ArrowRight class="size-4" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ================================================= -->
                <!-- EMPTY -->
                <!-- ================================================= -->

                <div
                    v-else
                    class="flex min-h-[390px] flex-col items-center justify-center px-6 py-16 text-center"
                >
                    <div
                        class="flex size-16 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100"
                    >
                        <Inbox class="size-7" />
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-900">
                        Aucune demande trouvée
                    </h3>

                    <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                        Aucune demande ne correspond aux critères sélectionnés.
                    </p>

                    <button
                        v-if="hasActiveFilters"
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                        @click="resetFilters"
                    >
                        <Filter class="size-4" />

                        Effacer les filtres
                    </button>

                    <Link
                        v-else
                        href="/requests/create"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-700"
                    >
                        <Plus class="size-4" />

                        Créer une demande
                    </Link>
                </div>

                <!-- ================================================= -->
                <!-- PAGINATION -->
                <!-- ================================================= -->

                <div
                    v-if="requests.total > 0"
                    class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50/50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-xs font-medium text-slate-500">
                        Affichage de

                        <span class="font-bold text-slate-800">
                            {{ requests.from ?? 0 }}
                        </span>

                        à

                        <span class="font-bold text-slate-800">
                            {{ requests.to ?? 0 }}
                        </span>

                        sur

                        <span class="font-bold text-slate-800">
                            {{ requests.total }}
                        </span>

                        demandes
                    </p>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            :disabled="!requests.prev_page_url"
                            class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="visitPage(requests.prev_page_url ?? null)"
                        >
                            <ChevronLeft class="size-4" />
                        </button>

                        <div
                            class="flex h-9 items-center rounded-lg border border-slate-200 bg-white px-3 text-xs font-bold text-slate-700"
                        >
                            {{ requests.current_page }}

                            <span class="mx-1 text-slate-300"> / </span>

                            {{ requests.last_page }}
                        </div>

                        <button
                            type="button"
                            :disabled="!requests.next_page_url"
                            class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="visitPage(requests.next_page_url ?? null)"
                        >
                            <ChevronRight class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
