<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import {
    Check,
    ChevronLeft,
    ChevronRight,
    Edit3,
    Grid2X2,
    KeyRound,
    LockKeyhole,
    MoreHorizontal,
    Plus,
    Power,
    Search,
    Shield,
    ShieldCheck,
    Trash2,
    Users,
    X,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";

/* -------------------------------------------------------------------------- */
/* Types                                                                      */
/* -------------------------------------------------------------------------- */

type Role = {
    id: number;
    name: string;
    code?: string | null;
    description?: string | null;
    active: boolean | number;

    permissions_count?: number;
    user_environments_count?: number;

    created_at?: string | null;
    updated_at?: string | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type RolesPaginator = {
    current_page: number;
    data: Role[];
    first_page_url?: string;
    from: number | null;
    last_page: number;
    last_page_url?: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path?: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
};

type Filters = {
    search?: string | null;
    active?: string | null;
};

/* -------------------------------------------------------------------------- */
/* Props                                                                      */
/* -------------------------------------------------------------------------- */

const props = withDefaults(
    defineProps<{
        roles?: RolesPaginator | null;
        filters?: Filters | null;
    }>(),
    {
        roles: null,
        filters: null,
    },
);

/* -------------------------------------------------------------------------- */
/* Safe paginator                                                             */
/* -------------------------------------------------------------------------- */

const paginator = computed<RolesPaginator>(() => {
    return (
        props.roles ?? {
            current_page: 1,
            data: [],
            from: null,
            last_page: 1,
            links: [],
            next_page_url: null,
            per_page: 20,
            prev_page_url: null,
            to: null,
            total: 0,
        }
    );
});

const roleList = computed<Role[]>(() => {
    return Array.isArray(paginator.value.data) ? paginator.value.data : [];
});

/* -------------------------------------------------------------------------- */
/* Filters                                                                    */
/* -------------------------------------------------------------------------- */

const search = ref(props.filters?.search ?? "");

const status = ref<"all" | "active" | "inactive">(
    props.filters?.active === "1"
        ? "active"
        : props.filters?.active === "0"
          ? "inactive"
          : "all",
);

let searchTimer: ReturnType<typeof setTimeout> | null = null;

const applyFilters = () => {
    const params: Record<string, string> = {};

    const searchValue = search.value.trim();

    if (searchValue) {
        params.search = searchValue;
    }

    if (status.value === "active") {
        params.active = "1";
    }

    if (status.value === "inactive") {
        params.active = "0";
    }

    router.get("/roles", params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        applyFilters();
    }, 350);
});

watch(status, () => {
    applyFilters();
});

const resetFilters = () => {
    search.value = "";
    status.value = "all";

    router.get(
        "/roles",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

/* -------------------------------------------------------------------------- */
/* Statistics                                                                 */
/* -------------------------------------------------------------------------- */

const totalRoles = computed(() => {
    return Number(paginator.value.total ?? 0);
});

/*
 * IMPORTANT:
 * Le contrôleur pagine les rôles.
 * activeRoles/inactiveRoles calculés depuis roleList concernent donc
 * la page actuellement chargée.
 *
 * Pour l'affichage principal, totalRoles utilise paginator.total.
 */

const activeRolesCurrentPage = computed(() => {
    return roleList.value.filter((role) => {
        return role.active === true || Number(role.active) === 1;
    }).length;
});

const inactiveRolesCurrentPage = computed(() => {
    return roleList.value.filter((role) => {
        return !(role.active === true || Number(role.active) === 1);
    }).length;
});

const totalPermissionsCurrentPage = computed(() => {
    return roleList.value.reduce((total, role) => {
        return total + Number(role.permissions_count ?? 0);
    }, 0);
});

const systemRolesCurrentPage = computed(() => {
    const systemCodes = ["super_admin", "admin", "administrator", "superadmin"];

    return roleList.value.filter((role) => {
        return systemCodes.includes(String(role.code ?? "").toLowerCase());
    }).length;
});

/* -------------------------------------------------------------------------- */
/* Helpers                                                                    */
/* -------------------------------------------------------------------------- */

const isActive = (role: Role) => {
    return role.active === true || Number(role.active) === 1;
};

const isSystemRole = (role: Role) => {
    return ["super_admin", "admin", "administrator", "superadmin"].includes(
        String(role.code ?? "").toLowerCase(),
    );
};

const roleInitials = (role: Role) => {
    const words = String(role.name ?? "")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (!words.length) {
        return "R";
    }

    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase();
    }

    return `${words[0][0]}${words[1][0]}`.toUpperCase();
};

/* -------------------------------------------------------------------------- */
/* Actions                                                                    */
/* -------------------------------------------------------------------------- */

const openMenuId = ref<number | null>(null);

const toggleMenu = (id: number) => {
    openMenuId.value = openMenuId.value === id ? null : id;
};

const toggleActive = (role: Role) => {
    openMenuId.value = null;

    if (role.code === "super_admin" && isActive(role)) {
        return;
    }

    router.post(
        `/roles/${role.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const deleteRole = (role: Role) => {
    openMenuId.value = null;

    if (role.code === "super_admin") {
        return;
    }

    const confirmed = window.confirm(
        `Voulez-vous vraiment supprimer le rôle « ${role.name} » ?`,
    );

    if (!confirmed) {
        return;
    }

    router.delete(`/roles/${role.id}`, {
        preserveScroll: true,
    });
};

/* -------------------------------------------------------------------------- */
/* Pagination                                                                 */
/* -------------------------------------------------------------------------- */

const goTo = (url: string | null) => {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
};

const paginationNumbers = computed(() => {
    const current = paginator.value.current_page;
    const last = paginator.value.last_page;

    const pages: number[] = [];

    const start = Math.max(1, current - 2);
    const end = Math.min(last, current + 2);

    for (let page = start; page <= end; page++) {
        pages.push(page);
    }

    return pages;
});

const pageUrl = (page: number) => {
    const url = new URL(window.location.href);

    url.searchParams.set("page", String(page));

    return `${url.pathname}${url.search}`;
};
</script>

<template>
    <Head title="Gestion des rôles" />

    <div class="min-h-full bg-slate-50/40">
        <!-- ================================================================ -->
        <!-- HEADER                                                           -->
        <!-- ================================================================ -->

        <div class="border-b border-slate-200/80 bg-white">
            <div
                class="flex flex-col gap-5 px-5 py-6 lg:flex-row lg:items-center lg:justify-between lg:px-7"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20"
                    >
                        <ShieldCheck class="size-6" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="text-2xl font-black tracking-tight text-slate-950"
                            >
                                Gestion des rôles
                            </h1>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-700 ring-1 ring-emerald-100"
                            >
                                <KeyRound class="size-3" />

                                Access Control
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Gérez les niveaux d'accès et les autorisations des
                            utilisateurs de School Up.
                        </p>
                    </div>
                </div>

                <Link
                    href="/roles/create"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition hover:-translate-y-0.5 hover:bg-slate-800"
                >
                    <Plus class="size-4" />

                    Nouveau rôle
                </Link>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- CONTENT                                                          -->
        <!-- ================================================================ -->

        <div class="space-y-5 p-5 lg:p-7">
            <!-- ============================================================ -->
            <!-- STATS                                                        -->
            <!-- ============================================================ -->

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <!-- TOTAL -->

                <section
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div
                        class="absolute -right-5 -top-5 size-24 rounded-full bg-emerald-50"
                    />

                    <div class="relative flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400"
                            >
                                Rôles
                            </p>

                            <div class="mt-3 flex items-end gap-2">
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ totalRoles }}
                                </span>

                                <span
                                    class="mb-1 text-xs font-semibold text-slate-400"
                                >
                                    au total
                                </span>
                            </div>

                            <p class="mt-3 text-xs text-slate-500">
                                Niveaux d'autorisation configurés
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <Shield class="size-5" />
                        </div>
                    </div>
                </section>

                <!-- ACTIVE -->

                <section
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div
                        class="absolute -right-5 -top-5 size-24 rounded-full bg-teal-50"
                    />

                    <div class="relative flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400"
                            >
                                Rôles actifs
                            </p>

                            <div class="mt-3 flex items-end gap-2">
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ activeRolesCurrentPage }}
                                </span>

                                <span
                                    class="mb-1 inline-flex items-center gap-1 text-xs font-bold text-emerald-600"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-emerald-500"
                                    />

                                    Actifs
                                </span>
                            </div>

                            <p class="mt-3 text-xs text-slate-500">
                                Sur la page actuelle
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-teal-50 text-teal-600"
                        >
                            <Check class="size-5" />
                        </div>
                    </div>
                </section>

                <!-- PERMISSIONS -->

                <section
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div
                        class="absolute -right-5 -top-5 size-24 rounded-full bg-blue-50"
                    />

                    <div class="relative flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400"
                            >
                                Permissions
                            </p>

                            <div class="mt-3 flex items-end gap-2">
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ totalPermissionsCurrentPage }}
                                </span>

                                <span
                                    class="mb-1 text-xs font-semibold text-slate-400"
                                >
                                    affectations
                                </span>
                            </div>

                            <p class="mt-3 text-xs text-slate-500">
                                Sur les rôles affichés
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                        >
                            <KeyRound class="size-5" />
                        </div>
                    </div>
                </section>

                <!-- SYSTEM -->

                <section
                    class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div
                        class="absolute -right-5 -top-5 size-24 rounded-full bg-violet-50"
                    />

                    <div class="relative flex items-start justify-between">
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.18em] text-slate-400"
                            >
                                Rôles système
                            </p>

                            <div class="mt-3 flex items-end gap-2">
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ systemRolesCurrentPage }}
                                </span>

                                <span
                                    class="mb-1 text-xs font-semibold text-slate-400"
                                >
                                    protégés
                                </span>
                            </div>

                            <p class="mt-3 text-xs text-slate-500">
                                Rôles essentiels à la plateforme
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                        >
                            <LockKeyhole class="size-5" />
                        </div>
                    </div>
                </section>
            </div>

            <!-- ============================================================ -->
            <!-- FILTER BAR                                                   -->
            <!-- ============================================================ -->

            <section
                class="rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- SEARCH -->

                    <div class="relative w-full lg:max-w-md">
                        <Search
                            class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher un rôle, un code..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-10 pr-10 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/5"
                        />

                        <button
                            v-if="search"
                            type="button"
                            class="absolute right-3 top-1/2 flex size-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            @click="search = ''"
                        >
                            <X class="size-3.5" />
                        </button>
                    </div>

                    <!-- STATUS FILTER -->

                    <div class="flex rounded-xl bg-slate-100 p-1">
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-xs font-bold transition"
                            :class="
                                status === 'all'
                                    ? 'bg-white text-slate-900 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-800'
                            "
                            @click="status = 'all'"
                        >
                            Tous

                            <span class="ml-1 text-[10px] text-slate-400">
                                {{ totalRoles }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-xs font-bold transition"
                            :class="
                                status === 'active'
                                    ? 'bg-white text-emerald-700 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-800'
                            "
                            @click="status = 'active'"
                        >
                            Actifs
                        </button>

                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-xs font-bold transition"
                            :class="
                                status === 'inactive'
                                    ? 'bg-white text-rose-600 shadow-sm'
                                    : 'text-slate-500 hover:text-slate-800'
                            "
                            @click="status = 'inactive'"
                        >
                            Inactifs
                        </button>
                    </div>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- TITLE                                                        -->
            <!-- ============================================================ -->

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-black text-slate-950">
                        Rôles disponibles
                    </h2>

                    <p class="mt-0.5 text-xs text-slate-400">
                        <template v-if="paginator.from">
                            {{ paginator.from }}–{{ paginator.to }} sur
                            {{ paginator.total }} rôles
                        </template>

                        <template v-else> 0 rôle affiché </template>
                    </p>
                </div>

                <div
                    class="hidden items-center gap-2 text-xs font-semibold text-slate-400 sm:flex"
                >
                    <Grid2X2 class="size-4" />

                    Vue par rôle
                </div>
            </div>

            <!-- ============================================================ -->
            <!-- ROLES                                                        -->
            <!-- ============================================================ -->

            <div
                v-if="roleList.length"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="role in roleList"
                    :key="role.id"
                    class="group relative rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg hover:shadow-slate-200/50"
                >
                    <!-- TOP -->

                    <div class="flex items-start justify-between gap-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <!-- ICON -->

                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl font-black"
                                :class="
                                    isSystemRole(role)
                                        ? 'bg-violet-50 text-violet-600 ring-1 ring-violet-100'
                                        : isActive(role)
                                          ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100'
                                          : 'bg-slate-100 text-slate-500'
                                "
                            >
                                <LockKeyhole
                                    v-if="isSystemRole(role)"
                                    class="size-5"
                                />

                                <span v-else class="text-xs">
                                    {{ roleInitials(role) }}
                                </span>
                            </div>

                            <!-- NAME -->

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3
                                        class="truncate text-sm font-black text-slate-900"
                                    >
                                        {{ role.name }}
                                    </h3>

                                    <span
                                        v-if="isSystemRole(role)"
                                        class="rounded-full bg-violet-50 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider text-violet-600"
                                    >
                                        Système
                                    </span>
                                </div>

                                <p
                                    class="mt-1 truncate font-mono text-[11px] font-semibold text-slate-400"
                                >
                                    {{ role.code || "sans_code" }}
                                </p>
                            </div>
                        </div>

                        <!-- MENU -->

                        <div class="relative shrink-0">
                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-lg border border-transparent text-slate-400 transition hover:border-slate-200 hover:bg-slate-50 hover:text-slate-700"
                                @click.stop="toggleMenu(role.id)"
                            >
                                <MoreHorizontal class="size-4" />
                            </button>

                            <div
                                v-if="openMenuId === role.id"
                                class="absolute right-0 top-10 z-30 w-48 overflow-hidden rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl"
                            >
                                <Link
                                    :href="`/roles/${role.id}/edit`"
                                    class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                    @click="openMenuId = null"
                                >
                                    <Edit3 class="size-3.5" />

                                    Modifier
                                </Link>

                                <button
                                    v-if="
                                        !(
                                            role.code === 'super_admin' &&
                                            isActive(role)
                                        )
                                    "
                                    type="button"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-semibold text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                                    @click="toggleActive(role)"
                                >
                                    <Power class="size-3.5" />

                                    {{
                                        isActive(role)
                                            ? "Désactiver"
                                            : "Activer"
                                    }}
                                </button>

                                <div
                                    v-if="role.code !== 'super_admin'"
                                    class="my-1 border-t border-slate-100"
                                />

                                <button
                                    v-if="role.code !== 'super_admin'"
                                    type="button"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                    @click="deleteRole(role)"
                                >
                                    <Trash2 class="size-3.5" />

                                    Supprimer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DESCRIPTION -->

                    <p
                        class="mt-4 min-h-10 line-clamp-2 text-xs leading-5 text-slate-500"
                    >
                        {{
                            role.description ||
                            "Aucune description renseignée pour ce rôle."
                        }}
                    </p>

                    <!-- STATUS -->

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[10px] font-black"
                            :class="
                                isActive(role)
                                    ? 'bg-emerald-50 text-emerald-700'
                                    : 'bg-slate-100 text-slate-500'
                            "
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="
                                    isActive(role)
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-400'
                                "
                            />

                            {{ isActive(role) ? "Actif" : "Inactif" }}
                        </span>
                    </div>

                    <!-- SEPARATOR -->

                    <div class="my-4 border-t border-slate-100" />

                    <!-- FOOTER -->

                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-4">
                            <!-- PERMISSIONS -->

                            <div
                                class="flex items-center gap-1.5 text-xs font-semibold text-slate-500"
                            >
                                <KeyRound class="size-3.5 text-blue-500" />

                                <span>
                                    {{ role.permissions_count ?? 0 }}
                                </span>

                                <span class="hidden text-slate-400 sm:inline">
                                    permissions
                                </span>
                            </div>

                            <!-- USERS -->

                            <div
                                class="flex items-center gap-1.5 text-xs font-semibold text-slate-500"
                            >
                                <Users class="size-3.5 text-emerald-500" />

                                <span>
                                    {{ role.user_environments_count ?? 0 }}
                                </span>

                                <span class="hidden text-slate-400 sm:inline">
                                    utilisateurs
                                </span>
                            </div>
                        </div>

                        <Link
                            :href="`/roles/${role.id}/edit`"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 transition hover:text-emerald-900"
                        >
                            Gérer

                            <ChevronRight class="size-3.5" />
                        </Link>
                    </div>
                </article>
            </div>

            <!-- ============================================================ -->
            <!-- EMPTY STATE                                                  -->
            <!-- ============================================================ -->

            <div
                v-else
                class="flex min-h-[310px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 text-center"
            >
                <div
                    class="flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                >
                    <Shield class="size-6" />
                </div>

                <h3 class="mt-5 text-base font-black text-slate-900">
                    Aucun rôle trouvé
                </h3>

                <p class="mt-2 max-w-md text-sm leading-6 text-slate-500">
                    Aucun rôle ne correspond actuellement à votre recherche ou
                    aux filtres sélectionnés.
                </p>

                <button
                    v-if="search || status !== 'all'"
                    type="button"
                    class="mt-5 inline-flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    @click="resetFilters"
                >
                    <X class="size-3.5" />

                    Réinitialiser les filtres
                </button>
            </div>

            <!-- ============================================================ -->
            <!-- PAGINATION                                                   -->
            <!-- ============================================================ -->

            <section
                v-if="paginator.last_page > 1"
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white px-4 py-3 shadow-sm sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-xs font-semibold text-slate-500">
                    Affichage

                    <span class="font-black text-slate-800">
                        {{ paginator.from ?? 0 }}
                    </span>

                    à

                    <span class="font-black text-slate-800">
                        {{ paginator.to ?? 0 }}
                    </span>

                    sur

                    <span class="font-black text-slate-800">
                        {{ paginator.total }}
                    </span>

                    rôles
                </p>

                <div class="flex items-center gap-1">
                    <!-- PREVIOUS -->

                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!paginator.prev_page_url"
                        @click="goTo(paginator.prev_page_url)"
                    >
                        <ChevronLeft class="size-4" />
                    </button>

                    <!-- FIRST -->

                    <button
                        v-if="paginationNumbers[0] > 1"
                        type="button"
                        class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 transition hover:bg-slate-50"
                        @click="goTo(pageUrl(1))"
                    >
                        1
                    </button>

                    <span
                        v-if="paginationNumbers[0] > 2"
                        class="px-1 text-xs text-slate-400"
                    >
                        ...
                    </span>

                    <!-- NUMBERS -->

                    <button
                        v-for="page in paginationNumbers"
                        :key="page"
                        type="button"
                        class="flex size-9 items-center justify-center rounded-lg border text-xs font-black transition"
                        :class="
                            page === paginator.current_page
                                ? 'border-slate-950 bg-slate-950 text-white shadow-sm'
                                : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                        "
                        @click="goTo(pageUrl(page))"
                    >
                        {{ page }}
                    </button>

                    <!-- LAST -->

                    <span
                        v-if="
                            paginationNumbers[paginationNumbers.length - 1] <
                            paginator.last_page - 1
                        "
                        class="px-1 text-xs text-slate-400"
                    >
                        ...
                    </span>

                    <button
                        v-if="
                            paginationNumbers[paginationNumbers.length - 1] <
                            paginator.last_page
                        "
                        type="button"
                        class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 transition hover:bg-slate-50"
                        @click="goTo(pageUrl(paginator.last_page))"
                    >
                        {{ paginator.last_page }}
                    </button>

                    <!-- NEXT -->

                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
                        :disabled="!paginator.next_page_url"
                        @click="goTo(paginator.next_page_url)"
                    >
                        <ChevronRight class="size-4" />
                    </button>
                </div>
            </section>

            <!-- ============================================================ -->
            <!-- INFO                                                         -->
            <!-- ============================================================ -->

            <section
                class="flex items-start gap-4 rounded-2xl border border-emerald-100 bg-gradient-to-r from-emerald-50/80 to-white p-4"
            >
                <div
                    class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm"
                >
                    <LockKeyhole class="size-4" />
                </div>

                <div>
                    <h3 class="text-sm font-black text-slate-900">
                        Contrôle d'accès centralisé
                    </h3>

                    <p class="mt-1 text-xs leading-5 text-slate-500">
                        Les permissions déterminent les fonctionnalités
                        accessibles à chaque rôle. Vérifiez les autorisations
                        avant d'affecter un rôle à un utilisateur.
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>
