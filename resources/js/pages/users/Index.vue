<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import {
    Activity,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    CircleUserRound,
    Eye,
    Filter,
    Mail,
    MoreHorizontal,
    Pencil,
    Plus,
    RefreshCcw,
    Search,
    ShieldCheck,
    SlidersHorizontal,
    UserCheck,
    UserRound,
    Users,
    UserX,
    X,
} from "@lucide/vue";
import { computed, ref, watch } from "vue";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type Role = {
    id: number;
    name: string;
    code: string;
};

type UserRole = {
    id: number;
    name: string;
    code: string;
};

type UserItem = {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    username?: string | null;
    email: string;
    phone?: string | null;
    avatar?: string | null;
    active: boolean | number;
    last_login_at?: string | null;

    role?: UserRole | null;

    roles?: UserRole[];

    current_role?: UserRole | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedUsers = {
    data: UserItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links?: PaginationLink[];
};

type Filters = {
    search?: string | null;
    role?: string | null;
    status?: string | null;
};

type Stats = {
    total?: number;
    active?: number;
    inactive?: number;
    roles?: number;
};

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = withDefaults(
    defineProps<{
        users: PaginatedUsers;
        roles?: Role[];
        filters?: Filters;
        stats?: Stats;
    }>(),
    {
        roles: () => [],
        filters: () => ({}),
        stats: () => ({}),
    },
);

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const search = ref(props.filters?.search ?? "");
const selectedRole = ref(props.filters?.role ?? "");
const selectedStatus = ref(props.filters?.status ?? "");

const filtersOpen = ref(false);
const openActionMenu = ref<number | null>(null);

let searchTimer: ReturnType<typeof setTimeout> | null = null;

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const userIsActive = (user: UserItem): boolean => {
    return user.active === true || user.active === 1;
};

const fullName = (user: UserItem): string => {
    const composed = [user.first_name, user.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return composed || user.name || "Utilisateur";
};

const initials = (user: UserItem): string => {
    const first =
        user.first_name?.trim()?.charAt(0) ??
        user.name?.trim()?.charAt(0) ??
        "U";

    const last =
        user.last_name?.trim()?.charAt(0) ??
        user.name?.trim()?.split(" ")?.[1]?.charAt(0) ??
        "";

    return `${first}${last}`.toUpperCase();
};

const userRole = (user: UserItem): UserRole | null => {
    if (user.current_role) {
        return user.current_role;
    }

    if (user.role) {
        return user.role;
    }

    if (user.roles && user.roles.length > 0) {
        return user.roles[0];
    }

    return null;
};

const roleName = (user: UserItem): string => {
    return userRole(user)?.name ?? "Non affecté";
};

const formatDate = (date?: string | null): string => {
    if (!date) {
        return "Jamais";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "—";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(parsed);
};

/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const totalUsers = computed(() => {
    return props.stats?.total ?? props.users.total ?? 0;
});

const activeUsers = computed(() => {
    if (props.stats?.active !== undefined) {
        return props.stats.active;
    }

    return props.users.data.filter(userIsActive).length;
});

const inactiveUsers = computed(() => {
    if (props.stats?.inactive !== undefined) {
        return props.stats.inactive;
    }

    return props.users.data.filter((user) => !userIsActive(user)).length;
});

const totalRoles = computed(() => {
    return props.stats?.roles ?? props.roles.length;
});

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

const hasFilters = computed(() => {
    return Boolean(
        search.value ||
            selectedRole.value ||
            selectedStatus.value,
    );
});

const activeFiltersCount = computed(() => {
    let count = 0;

    if (search.value) count++;
    if (selectedRole.value) count++;
    if (selectedStatus.value) count++;

    return count;
});

const applyFilters = (): void => {
    const params: Record<string, string> = {};

    if (search.value.trim()) {
        params.search = search.value.trim();
    }

    if (selectedRole.value) {
        params.role = selectedRole.value;
    }

    if (selectedStatus.value) {
        params.status = selectedStatus.value;
    }

    router.get("/users", params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const resetFilters = (): void => {
    search.value = "";
    selectedRole.value = "";
    selectedStatus.value = "";

    router.get(
        "/users",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const refresh = (): void => {
    router.reload({
        only: ["users", "stats"],
    });
};

/*
|--------------------------------------------------------------------------
| Search debounce
|--------------------------------------------------------------------------
*/

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        applyFilters();
    }, 450);
});

watch([selectedRole, selectedStatus], () => {
    applyFilters();
});

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const goToPage = (url: string | null): void => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

const toggleActionMenu = (userId: number): void => {
    openActionMenu.value =
        openActionMenu.value === userId ? null : userId;
};

const closeActionMenu = (): void => {
    openActionMenu.value = null;
};
</script>

<template>
    <Head title="Utilisateurs" />

    <div
        class="min-h-full bg-slate-50/40"
        @click="closeActionMenu"
    >
        <!-- ========================================================= -->
        <!-- PAGE HEADER -->
        <!-- ========================================================= -->

        <div
            class="border-b border-slate-200/70 bg-white"
        >
            <div class="px-5 py-5 lg:px-7">
                <div
                    class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-sm shadow-emerald-200"
                        >
                            <Users class="size-5" />
                        </div>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h1
                                    class="truncate text-xl font-bold tracking-tight text-slate-950"
                                >
                                    Données des utilisateurs
                                </h1>
                            </div>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Gérez les utilisateurs, leurs rôles et
                                leurs accès à l'établissement.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-wrap items-center gap-2"
                    >
                        <button
                            type="button"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                            @click="refresh"
                        >
                            <RefreshCcw class="size-4" />

                            <span class="hidden sm:inline">
                                Actualiser
                            </span>
                        </button>

                        <Link
                            href="/users/create"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                        >
                            <Plus class="size-4" />

                            Nouvel utilisateur
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- CONTENT -->
        <!-- ========================================================= -->

        <div class="space-y-5 p-5 lg:p-7">
            <!-- ===================================================== -->
            <!-- STATS -->
            <!-- ===================================================== -->

            <div
                class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
            >
                <!-- TOTAL -->

                <div
                    class="group rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Utilisateurs
                            </p>

                            <div
                                class="mt-2 flex items-end gap-2"
                            >
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ totalUsers }}
                                </span>

                                <span
                                    class="mb-1 text-xs text-slate-400"
                                >
                                    au total
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600"
                        >
                            <Users class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- ACTIVE -->

                <div
                    class="group rounded-2xl border border-emerald-100 bg-gradient-to-br from-white to-emerald-50/50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-600"
                            >
                                Actifs
                            </p>

                            <div
                                class="mt-2 flex items-end gap-2"
                            >
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ activeUsers }}
                                </span>

                                <span
                                    class="mb-1 text-xs text-slate-400"
                                >
                                    utilisateurs
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"
                        >
                            <UserCheck class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- INACTIVE -->

                <div
                    class="group rounded-2xl border border-amber-100 bg-gradient-to-br from-white to-amber-50/50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-amber-600"
                            >
                                Inactifs
                            </p>

                            <div
                                class="mt-2 flex items-end gap-2"
                            >
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ inactiveUsers }}
                                </span>

                                <span
                                    class="mb-1 text-xs text-slate-400"
                                >
                                    utilisateurs
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600"
                        >
                            <UserX class="size-5" />
                        </div>
                    </div>
                </div>

                <!-- ROLES -->

                <div
                    class="group rounded-2xl border border-teal-100 bg-gradient-to-br from-white to-teal-50/50 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-[11px] font-bold uppercase tracking-[0.12em] text-teal-600"
                            >
                                Rôles
                            </p>

                            <div
                                class="mt-2 flex items-end gap-2"
                            >
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ totalRoles }}
                                </span>

                                <span
                                    class="mb-1 text-xs text-slate-400"
                                >
                                    configurés
                                </span>
                            </div>
                        </div>

                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-teal-100 text-teal-600"
                        >
                            <ShieldCheck class="size-5" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===================================================== -->
            <!-- TABLE CARD -->
            <!-- ===================================================== -->

            <div
                class="overflow-visible rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            >
                <!-- ================================================= -->
                <!-- TOOLBAR -->
                <!-- ================================================= -->

                <div
                    class="border-b border-slate-100 p-4"
                >
                    <div
                        class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between"
                    >
                        <!-- SEARCH -->

                        <div
                            class="relative w-full xl:max-w-md"
                        >
                            <Search
                                class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model="search"
                                type="search"
                                placeholder="Rechercher par nom, email, identifiant..."
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pl-10 pr-10 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-100/50"
                            />

                            <button
                                v-if="search"
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 rounded-md p-1 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                @click="search = ''"
                            >
                                <X class="size-3.5" />
                            </button>
                        </div>

                        <!-- FILTERS -->

                        <div
                            class="flex flex-wrap items-center gap-2"
                        >
                            <!-- ROLE -->

                            <div class="relative hidden md:block">
                                <ShieldCheck
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <select
                                    v-model="selectedRole"
                                    class="h-10 min-w-[180px] appearance-none rounded-xl border border-slate-200 bg-white pl-9 pr-9 text-sm font-medium text-slate-600 outline-none transition hover:bg-slate-50 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100/50"
                                >
                                    <option value="">
                                        Tous les rôles
                                    </option>

                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="role.code"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400"
                                />
                            </div>

                            <!-- STATUS -->

                            <div class="relative hidden md:block">
                                <Activity
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <select
                                    v-model="selectedStatus"
                                    class="h-10 min-w-[150px] appearance-none rounded-xl border border-slate-200 bg-white pl-9 pr-9 text-sm font-medium text-slate-600 outline-none transition hover:bg-slate-50 focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100/50"
                                >
                                    <option value="">
                                        Tous les statuts
                                    </option>

                                    <option value="active">
                                        Actifs
                                    </option>

                                    <option value="inactive">
                                        Inactifs
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3 top-1/2 size-3.5 -translate-y-1/2 text-slate-400"
                                />
                            </div>

                            <!-- MOBILE FILTER -->

                            <button
                                type="button"
                                class="relative inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 md:hidden"
                                @click="filtersOpen = !filtersOpen"
                            >
                                <SlidersHorizontal class="size-4" />

                                Filtres

                                <span
                                    v-if="activeFiltersCount > 0"
                                    class="flex size-5 items-center justify-center rounded-full bg-emerald-500 text-[10px] font-bold text-white"
                                >
                                    {{ activeFiltersCount }}
                                </span>
                            </button>

                            <!-- RESET -->

                            <button
                                v-if="hasFilters"
                                type="button"
                                class="inline-flex h-10 items-center gap-1.5 rounded-xl px-3 text-xs font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                                @click="resetFilters"
                            >
                                <X class="size-3.5" />

                                Réinitialiser
                            </button>
                        </div>
                    </div>

                    <!-- MOBILE FILTERS -->

                    <div
                        v-if="filtersOpen"
                        class="mt-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 md:hidden"
                    >
                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold text-slate-500"
                            >
                                Rôle
                            </label>

                            <select
                                v-model="selectedRole"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none"
                            >
                                <option value="">
                                    Tous les rôles
                                </option>

                                <option
                                    v-for="role in roles"
                                    :key="role.id"
                                    :value="role.code"
                                >
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label
                                class="mb-1.5 block text-xs font-semibold text-slate-500"
                            >
                                Statut
                            </label>

                            <select
                                v-model="selectedStatus"
                                class="h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm outline-none"
                            >
                                <option value="">
                                    Tous les statuts
                                </option>

                                <option value="active">
                                    Actifs
                                </option>

                                <option value="inactive">
                                    Inactifs
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ================================================= -->
                <!-- TABLE -->
                <!-- ================================================= -->

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[1000px]">
                        <thead>
                            <tr
                                class="border-b border-slate-100 bg-slate-50/70"
                            >
                                <th
                                    class="w-[70px] px-5 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    #
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Utilisateur
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Identifiant
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Rôle
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Statut
                                </th>

                                <th
                                    class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Dernière connexion
                                </th>

                                <th
                                    class="w-[80px] px-5 py-3 text-right text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>

                        <tbody
                            class="divide-y divide-slate-100"
                        >
                            <tr
                                v-for="(user, index) in users.data"
                                :key="user.id"
                                class="group transition-colors hover:bg-emerald-50/30"
                            >
                                <!-- NUMBER -->

                                <td
                                    class="px-5 py-4 text-xs font-medium text-slate-400"
                                >
                                    {{
                                        (users.from ?? 1) +
                                        index
                                    }}
                                </td>

                                <!-- USER -->

                                <td class="px-4 py-3.5">
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-emerald-50 to-green-100 font-bold text-emerald-700 ring-1 ring-emerald-100"
                                        >
                                            <img
                                                v-if="user.avatar"
                                                :src="user.avatar"
                                                :alt="fullName(user)"
                                                class="size-full object-cover"
                                            />

                                            <span
                                                v-else
                                                class="text-xs"
                                            >
                                                {{ initials(user) }}
                                            </span>

                                            <span
                                                class="absolute bottom-0 right-0 size-2.5 rounded-full border-2 border-white"
                                                :class="
                                                    userIsActive(user)
                                                        ? 'bg-emerald-500'
                                                        : 'bg-slate-300'
                                                "
                                            />
                                        </div>

                                        <div class="min-w-0">
                                            <Link
                                                :href="`/users/${user.id}`"
                                                class="block truncate text-sm font-bold text-slate-800 transition hover:text-emerald-700"
                                            >
                                                {{ fullName(user) }}
                                            </Link>

                                            <div
                                                class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-400"
                                            >
                                                <Mail
                                                    class="size-3"
                                                />

                                                <span
                                                    class="max-w-[260px] truncate"
                                                >
                                                    {{ user.email }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- USERNAME -->

                                <td class="px-4 py-3.5">
                                    <span
                                        v-if="user.username"
                                        class="inline-flex rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                                    >
                                        {{ user.username }}
                                    </span>

                                    <span
                                        v-else
                                        class="text-sm text-slate-300"
                                    >
                                        —
                                    </span>
                                </td>

                                <!-- ROLE -->

                                <td class="px-4 py-3.5">
                                    <div
                                        v-if="userRole(user)"
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-teal-100 bg-teal-50 px-2.5 py-1.5 text-xs font-semibold text-teal-700"
                                    >
                                        <ShieldCheck
                                            class="size-3.5"
                                        />

                                        {{ roleName(user) }}
                                    </div>

                                    <div
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-medium text-slate-400"
                                    >
                                        <CircleUserRound
                                            class="size-3.5"
                                        />

                                        Non affecté
                                    </div>
                                </td>

                                <!-- STATUS -->

                                <td class="px-4 py-3.5">
                                    <span
                                        v-if="userIsActive(user)"
                                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-100"
                                    >
                                        <span
                                            class="size-1.5 rounded-full bg-emerald-500"
                                        />

                                        Actif
                                    </span>

                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 ring-1 ring-inset ring-slate-200"
                                    >
                                        <span
                                            class="size-1.5 rounded-full bg-slate-400"
                                        />

                                        Inactif
                                    </span>
                                </td>

                                <!-- LAST LOGIN -->

                                <td class="px-4 py-3.5">
                                    <div
                                        class="flex items-center gap-2"
                                    >
                                        <CheckCircle2
                                            v-if="
                                                user.last_login_at
                                            "
                                            class="size-3.5 text-emerald-500"
                                        />

                                        <span
                                            class="text-xs font-medium"
                                            :class="
                                                user.last_login_at
                                                    ? 'text-slate-600'
                                                    : 'text-slate-400'
                                            "
                                        >
                                            {{
                                                formatDate(
                                                    user.last_login_at,
                                                )
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- ACTIONS -->

                                <td
                                    class="relative px-5 py-3.5 text-right"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex size-8 items-center justify-center rounded-lg border border-transparent text-slate-400 transition hover:border-slate-200 hover:bg-white hover:text-slate-700 hover:shadow-sm"
                                        @click.stop="
                                            toggleActionMenu(
                                                user.id,
                                            )
                                        "
                                    >
                                        <MoreHorizontal
                                            class="size-4"
                                        />
                                    </button>

                                    <div
                                        v-if="
                                            openActionMenu ===
                                            user.id
                                        "
                                        class="absolute right-5 top-[48px] z-50 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 text-left shadow-xl shadow-slate-200/60"
                                        @click.stop
                                    >
                                        <Link
                                            :href="`/users/${user.id}`"
                                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                        >
                                            <Eye
                                                class="size-4 text-slate-400"
                                            />

                                            Voir le profil
                                        </Link>

                                        <Link
                                            :href="`/users/${user.id}/edit`"
                                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-emerald-50 hover:text-emerald-700"
                                        >
                                            <Pencil
                                                class="size-4 text-emerald-500"
                                            />

                                            Modifier
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <!-- EMPTY -->

                            <tr v-if="users.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-20"
                                >
                                    <div
                                        class="mx-auto flex max-w-sm flex-col items-center text-center"
                                    >
                                        <div
                                            class="flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                                        >
                                            <UserRound
                                                class="size-6"
                                            />
                                        </div>

                                        <h3
                                            class="mt-4 text-sm font-bold text-slate-800"
                                        >
                                            Aucun utilisateur trouvé
                                        </h3>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-400"
                                        >
                                            Aucun utilisateur ne
                                            correspond aux critères de
                                            recherche actuels.
                                        </p>

                                        <button
                                            v-if="hasFilters"
                                            type="button"
                                            class="mt-4 text-xs font-bold text-emerald-600 hover:text-emerald-700"
                                            @click="resetFilters"
                                        >
                                            Réinitialiser les filtres
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ================================================= -->
                <!-- FOOTER / PAGINATION -->
                <!-- ================================================= -->

                <div
                    class="flex flex-col gap-3 border-t border-slate-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="text-xs font-medium text-slate-400"
                    >
                        <template v-if="users.total > 0">
                            Affichage de

                            <span
                                class="font-bold text-slate-700"
                            >
                                {{ users.from ?? 0 }}
                            </span>

                            à

                            <span
                                class="font-bold text-slate-700"
                            >
                                {{ users.to ?? 0 }}
                            </span>

                            sur

                            <span
                                class="font-bold text-slate-700"
                            >
                                {{ users.total }}
                            </span>

                            utilisateurs
                        </template>

                        <template v-else>
                            Aucun résultat
                        </template>
                    </div>

                    <div
                        v-if="users.last_page > 1"
                        class="flex items-center gap-1"
                    >
                        <button
                            type="button"
                            :disabled="users.current_page <= 1"
                            class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="
                                goToPage(
                                    users.links?.[0]?.url ??
                                        null,
                                )
                            "
                        >
                            <ChevronLeft class="size-4" />
                        </button>

                        <template
                            v-for="link in users.links?.slice(
                                1,
                                -1,
                            )"
                            :key="link.label"
                        >
                            <button
                                v-if="
                                    !link.label.includes(
                                        '...',
                                    )
                                "
                                type="button"
                                class="flex size-9 items-center justify-center rounded-lg text-xs font-bold transition"
                                :class="
                                    link.active
                                        ? 'bg-slate-950 text-white shadow-sm'
                                        : 'border border-slate-200 bg-white text-slate-500 hover:bg-slate-50'
                                "
                                @click="
                                    goToPage(link.url)
                                "
                            >
                                {{ link.label }}
                            </button>

                            <span
                                v-else
                                class="flex size-9 items-center justify-center text-xs text-slate-400"
                            >
                                ...
                            </span>
                        </template>

                        <button
                            type="button"
                            :disabled="
                                users.current_page >=
                                users.last_page
                            "
                            class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                            @click="
                                goToPage(
                                    users.links?.[
                                        (users.links?.length ??
                                            1) - 1
                                    ]?.url ?? null,
                                )
                            "
                        >
                            <ChevronRight class="size-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>