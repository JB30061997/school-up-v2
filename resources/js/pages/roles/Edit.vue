<script setup lang="ts">
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Check,
    CheckCircle2,
    ChevronDown,
    KeyRound,
    LockKeyhole,
    Save,
    Search,
    ShieldCheck,
    X,
} from "lucide-vue-next";
import { computed, ref } from "vue";

/* ==========================================================================
   TYPES
   ========================================================================== */

type Permission = {
    id: number;
    name: string;
    code: string;
    description?: string | null;
    group?: string | null;
    module?: string | null;
};

type Role = {
    id: number;
    name: string;
    code: string;
    description?: string | null;
    active: boolean | number;

    permissions?: Permission[];

    created_at?: string | null;
    updated_at?: string | null;
};

type PermissionGroup = {
    name: string;
    permissions: Permission[];
};

/* ==========================================================================
   PROPS
   ========================================================================== */

const props = withDefaults(
    defineProps<{
        role?: Role | null;
        permissions?: Permission[];
    }>(),
    {
        role: null,
        permissions: () => [],
    },
);

/* ==========================================================================
   SAFE ROLE
   ========================================================================== */

const safeRole = computed<Role>(() => {
    return (
        props.role ?? {
            id: 0,
            name: "",
            code: "",
            description: "",
            active: true,
            permissions: [],
        }
    );
});

/* ==========================================================================
   INITIAL PERMISSIONS
   ========================================================================== */

const initialPermissionIds = computed<number[]>(() => {
    if (!Array.isArray(safeRole.value.permissions)) {
        return [];
    }

    return safeRole.value.permissions
        .map((permission) => Number(permission.id))
        .filter((id) => !Number.isNaN(id));
});

/* ==========================================================================
   FORM
   ========================================================================== */

const form = useForm({
    name: safeRole.value.name ?? "",
    code: safeRole.value.code ?? "",
    description: safeRole.value.description ?? "",

    active:
        safeRole.value.active === true || Number(safeRole.value.active) === 1,

    permissions: [...initialPermissionIds.value],
});

/* ==========================================================================
   SYSTEM ROLE
   ========================================================================== */

const isSystemRole = computed(() => {
    return ["super_admin", "admin"].includes(
        String(safeRole.value.code ?? "").toLowerCase(),
    );
});

/* ==========================================================================
   SEARCH
   ========================================================================== */

const search = ref("");

/* ==========================================================================
   PERMISSION GROUPS
   ========================================================================== */

const permissionGroups = computed<PermissionGroup[]>(() => {
    const groups = new Map<string, Permission[]>();

    props.permissions.forEach((permission) => {
        const groupName =
            permission.group ||
            permission.module ||
            getGroupFromCode(permission.code) ||
            "Général";

        if (!groups.has(groupName)) {
            groups.set(groupName, []);
        }

        groups.get(groupName)!.push(permission);
    });

    return Array.from(groups.entries()).map(([name, permissions]) => ({
        name,
        permissions,
    }));
});

/* ==========================================================================
   GROUP FROM CODE
   ========================================================================== */

function getGroupFromCode(code?: string | null) {
    if (!code) {
        return "Général";
    }

    const first = code.split(".")[0];

    const names: Record<string, string> = {
        users: "Utilisateurs",
        user: "Utilisateurs",

        roles: "Rôles & permissions",
        role: "Rôles & permissions",

        permissions: "Rôles & permissions",
        permission: "Rôles & permissions",

        appointments: "Rendez-vous",
        appointment: "Rendez-vous",

        requests: "Demandes",
        request: "Demandes",

        tickets: "Réclamations",
        ticket: "Réclamations",

        environments: "Établissements",
        environment: "Établissements",

        cycles: "Référentiels",
        cycle: "Référentiels",

        levels: "Référentiels",
        level: "Référentiels",

        classes: "Référentiels",
        class: "Référentiels",

        settings: "Administration",
        setting: "Administration",

        dashboard: "Tableau de bord",
    };

    return names[first] ?? formatGroupName(first);
}

function formatGroupName(value: string) {
    if (!value) {
        return "Général";
    }

    return value
        .replace(/[_-]/g, " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

/* ==========================================================================
   FILTERED GROUPS
   ========================================================================== */

const filteredGroups = computed<PermissionGroup[]>(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return permissionGroups.value;
    }

    return permissionGroups.value
        .map((group) => {
            const permissions = group.permissions.filter((permission) => {
                return [
                    permission.name,
                    permission.code,
                    permission.description,
                    group.name,
                ]
                    .filter(Boolean)
                    .some((value) =>
                        String(value).toLowerCase().includes(query),
                    );
            });

            return {
                name: group.name,
                permissions,
            };
        })
        .filter((group) => group.permissions.length > 0);
});

/* ==========================================================================
   PERMISSION HELPERS
   ========================================================================== */

const hasPermission = (id: number) => {
    return form.permissions.includes(id);
};

const togglePermission = (id: number) => {
    if (hasPermission(id)) {
        form.permissions = form.permissions.filter(
            (permissionId) => permissionId !== id,
        );

        return;
    }

    form.permissions = [...form.permissions, id];
};

const groupIsSelected = (permissions: Permission[]) => {
    if (!permissions.length) {
        return false;
    }

    return permissions.every((permission) => hasPermission(permission.id));
};

const groupIsPartial = (permissions: Permission[]) => {
    const selected = permissions.filter((permission) =>
        hasPermission(permission.id),
    ).length;

    return selected > 0 && selected < permissions.length;
};

const toggleGroup = (permissions: Permission[]) => {
    const ids = permissions.map((permission) => permission.id);

    if (groupIsSelected(permissions)) {
        form.permissions = form.permissions.filter((id) => !ids.includes(id));

        return;
    }

    const merged = new Set([...form.permissions, ...ids]);

    form.permissions = Array.from(merged);
};

/* ==========================================================================
   ALL PERMISSIONS
   ========================================================================== */

const allPermissionIds = computed(() => {
    return props.permissions.map((permission) => permission.id);
});

const allSelected = computed(() => {
    if (!allPermissionIds.value.length) {
        return false;
    }

    return allPermissionIds.value.every((id) => form.permissions.includes(id));
});

const toggleAll = () => {
    if (allSelected.value) {
        form.permissions = [];

        return;
    }

    form.permissions = [...allPermissionIds.value];
};

/* ==========================================================================
   STATS
   ========================================================================== */

const selectedCount = computed(() => {
    return form.permissions.length;
});

const totalPermissions = computed(() => {
    return props.permissions.length;
});

const selectionPercentage = computed(() => {
    if (!totalPermissions.value) {
        return 0;
    }

    return Math.round((selectedCount.value / totalPermissions.value) * 100);
});

/* ==========================================================================
   EXPANDED GROUPS
   ========================================================================== */

const collapsedGroups = ref<string[]>([]);

const isGroupOpen = (name: string) => {
    return !collapsedGroups.value.includes(name);
};

const toggleGroupOpen = (name: string) => {
    if (collapsedGroups.value.includes(name)) {
        collapsedGroups.value = collapsedGroups.value.filter(
            (group) => group !== name,
        );

        return;
    }

    collapsedGroups.value.push(name);
};

/* ==========================================================================
   SUBMIT
   ========================================================================== */

const submit = () => {
    if (!safeRole.value.id) {
        return;
    }

    form.put(`/roles/${safeRole.value.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Modifier ${safeRole.name || 'le rôle'}`" />

    <div class="min-h-full bg-slate-50/50">
        <!-- ============================================================= -->
        <!-- HEADER                                                        -->
        <!-- ============================================================= -->

        <div class="border-b border-slate-200/80 bg-white">
            <div
                class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between lg:px-7"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/roles"
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>

                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20"
                    >
                        <ShieldCheck class="size-5" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="text-xl font-black tracking-tight text-slate-950"
                            >
                                Modifier le rôle
                            </h1>

                            <span
                                v-if="isSystemRole"
                                class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider text-violet-600 ring-1 ring-violet-100"
                            >
                                <LockKeyhole class="size-3" />

                                Rôle système
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Configurez les informations et les autorisations de

                            <span class="font-bold text-slate-700">
                                {{ safeRole.name }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/roles"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50"
                    >
                        Annuler
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="submit"
                    >
                        <Save class="size-4" />

                        {{
                            form.processing
                                ? "Enregistrement..."
                                : "Enregistrer"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- PAGE                                                          -->
        <!-- ============================================================= -->

        <form class="p-5 lg:p-7" @submit.prevent="submit">
            <div class="grid gap-5 xl:grid-cols-[360px_minmax(0,1fr)]">
                <!-- ===================================================== -->
                <!-- LEFT                                                  -->
                <!-- ===================================================== -->

                <aside class="space-y-5">
                    <!-- ROLE INFORMATION -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div class="border-b border-slate-100 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                >
                                    <ShieldCheck class="size-4" />
                                </div>

                                <div>
                                    <h2
                                        class="text-sm font-black text-slate-900"
                                    >
                                        Informations du rôle
                                    </h2>

                                    <p class="mt-0.5 text-xs text-slate-400">
                                        Identité et état du rôle
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-5 p-5">
                            <!-- NAME -->

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-slate-700"
                                >
                                    Nom du rôle

                                    <span class="text-rose-500"> * </span>
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm font-semibold text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/5"
                                    placeholder="Ex : Responsable pédagogique"
                                />

                                <p
                                    v-if="form.errors.name"
                                    class="mt-1.5 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- CODE -->

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-slate-700"
                                >
                                    Code technique

                                    <span class="text-rose-500"> * </span>
                                </label>

                                <div class="relative">
                                    <KeyRound
                                        class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="form.code"
                                        type="text"
                                        :disabled="
                                            safeRole.code === 'super_admin'
                                        "
                                        class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-10 pr-3 font-mono text-xs font-bold text-slate-700 outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/5 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                        placeholder="responsable_pedagogique"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.code"
                                    class="mt-1.5 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.code }}
                                </p>

                                <p
                                    v-else-if="safeRole.code === 'super_admin'"
                                    class="mt-2 text-[11px] leading-4 text-slate-400"
                                >
                                    Le code du Super Admin est protégé.
                                </p>
                            </div>

                            <!-- DESCRIPTION -->

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold text-slate-700"
                                >
                                    Description
                                </label>

                                <textarea
                                    v-model="form.description"
                                    rows="5"
                                    class="w-full resize-none rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-6 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-500/5"
                                    placeholder="Décrivez les responsabilités de ce rôle..."
                                />

                                <p
                                    v-if="form.errors.description"
                                    class="mt-1.5 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.description }}
                                </p>
                            </div>

                            <!-- ACTIVE -->

                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-4"
                            >
                                <div
                                    class="flex items-center justify-between gap-4"
                                >
                                    <div>
                                        <p
                                            class="text-sm font-bold text-slate-800"
                                        >
                                            Rôle actif
                                        </p>

                                        <p
                                            class="mt-1 text-xs leading-5 text-slate-400"
                                        >
                                            Disponible lors de l'affectation des
                                            utilisateurs.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="relative h-6 w-11 shrink-0 rounded-full transition"
                                        :class="
                                            form.active
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-300'
                                        "
                                        @click="form.active = !form.active"
                                    >
                                        <span
                                            class="absolute top-1 size-4 rounded-full bg-white shadow-sm transition-all"
                                            :class="
                                                form.active
                                                    ? 'left-6'
                                                    : 'left-1'
                                            "
                                        />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- PERMISSION SUMMARY -->

                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                    >
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.15em] text-slate-400"
                        >
                            Niveau d'accès
                        </p>

                        <div class="mt-3 flex items-end justify-between">
                            <div>
                                <span
                                    class="text-3xl font-black tracking-tight text-slate-950"
                                >
                                    {{ selectedCount }}
                                </span>

                                <span
                                    class="ml-1 text-xs font-semibold text-slate-400"
                                >
                                    /
                                    {{ totalPermissions }}
                                </span>
                            </div>

                            <span
                                class="rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-black text-emerald-700"
                            >
                                {{ selectionPercentage }}%
                            </span>
                        </div>

                        <div
                            class="mt-4 h-2 overflow-hidden rounded-full bg-slate-100"
                        >
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-300"
                                :style="{
                                    width: selectionPercentage + '%',
                                }"
                            />
                        </div>

                        <p class="mt-3 text-xs leading-5 text-slate-400">
                            {{ selectedCount }}
                            autorisation(s) actuellement accordée(s) à ce rôle.
                        </p>
                    </section>
                </aside>

                <!-- ===================================================== -->
                <!-- RIGHT                                                 -->
                <!-- ===================================================== -->

                <main class="space-y-5">
                    <!-- PERMISSION HEADER -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                    >
                        <div class="border-b border-slate-100 px-5 py-4">
                            <div
                                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                    >
                                        <KeyRound class="size-5" />
                                    </div>

                                    <div>
                                        <h2
                                            class="text-sm font-black text-slate-900"
                                        >
                                            Permissions
                                        </h2>

                                        <p
                                            class="mt-0.5 text-xs text-slate-400"
                                        >
                                            Définissez précisément les
                                            fonctionnalités accessibles à ce
                                            rôle.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="inline-flex h-9 items-center justify-center gap-2 rounded-xl border px-3 text-xs font-bold transition"
                                    :class="
                                        allSelected
                                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
                                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
                                    "
                                    @click="toggleAll"
                                >
                                    <CheckCircle2 class="size-4" />

                                    {{
                                        allSelected
                                            ? "Tout désélectionner"
                                            : "Tout sélectionner"
                                    }}
                                </button>
                            </div>
                        </div>

                        <!-- SEARCH -->

                        <div class="border-b border-slate-100 p-4">
                            <div class="relative">
                                <Search
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Rechercher une permission..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pl-10 pr-10 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-500/5"
                                />

                                <button
                                    v-if="search"
                                    type="button"
                                    class="absolute right-3 top-1/2 flex size-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                    @click="search = ''"
                                >
                                    <X class="size-3.5" />
                                </button>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- PERMISSION GROUPS                                 -->
                        <!-- ================================================= -->

                        <div
                            v-if="filteredGroups.length"
                            class="divide-y divide-slate-100"
                        >
                            <section
                                v-for="group in filteredGroups"
                                :key="group.name"
                            >
                                <!-- GROUP HEADER -->

                                <div
                                    class="flex items-center justify-between gap-4 bg-slate-50/60 px-5 py-3.5"
                                >
                                    <button
                                        type="button"
                                        class="flex min-w-0 flex-1 items-center gap-3 text-left"
                                        @click="toggleGroupOpen(group.name)"
                                    >
                                        <div
                                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm ring-1 ring-slate-200"
                                        >
                                            <ShieldCheck class="size-4" />
                                        </div>

                                        <div class="min-w-0">
                                            <div
                                                class="flex items-center gap-2"
                                            >
                                                <h3
                                                    class="truncate text-xs font-black text-slate-800"
                                                >
                                                    {{ group.name }}
                                                </h3>

                                                <span
                                                    class="rounded-md bg-white px-1.5 py-0.5 text-[9px] font-black text-slate-400 ring-1 ring-slate-200"
                                                >
                                                    {{
                                                        group.permissions.length
                                                    }}
                                                </span>
                                            </div>

                                            <p
                                                class="mt-0.5 text-[10px] font-medium text-slate-400"
                                            >
                                                {{
                                                    group.permissions.filter(
                                                        (permission) =>
                                                            hasPermission(
                                                                permission.id,
                                                            ),
                                                    ).length
                                                }}
                                                sélectionnée(s)
                                            </p>
                                        </div>

                                        <ChevronDown
                                            class="ml-auto size-4 shrink-0 text-slate-400 transition-transform"
                                            :class="{
                                                '-rotate-90': !isGroupOpen(
                                                    group.name,
                                                ),
                                            }"
                                        />
                                    </button>

                                    <!-- GROUP CHECKBOX -->

                                    <button
                                        type="button"
                                        class="flex size-8 shrink-0 items-center justify-center rounded-lg border transition"
                                        :class="
                                            groupIsSelected(group.permissions)
                                                ? 'border-emerald-500 bg-emerald-500 text-white'
                                                : groupIsPartial(
                                                        group.permissions,
                                                    )
                                                  ? 'border-emerald-300 bg-emerald-50 text-emerald-600'
                                                  : 'border-slate-200 bg-white text-transparent hover:border-emerald-300'
                                        "
                                        @click="toggleGroup(group.permissions)"
                                    >
                                        <Check class="size-4" />
                                    </button>
                                </div>

                                <!-- PERMISSIONS -->

                                <div
                                    v-if="isGroupOpen(group.name)"
                                    class="grid gap-px bg-slate-100 md:grid-cols-2"
                                >
                                    <button
                                        v-for="permission in group.permissions"
                                        :key="permission.id"
                                        type="button"
                                        class="group flex min-h-[92px] items-start gap-3 bg-white p-4 text-left transition hover:bg-slate-50/70"
                                        @click="togglePermission(permission.id)"
                                    >
                                        <!-- CHECK -->

                                        <div
                                            class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-md border transition"
                                            :class="
                                                hasPermission(permission.id)
                                                    ? 'border-emerald-500 bg-emerald-500 text-white shadow-sm shadow-emerald-500/20'
                                                    : 'border-slate-300 bg-white text-transparent group-hover:border-emerald-300'
                                            "
                                        >
                                            <Check class="size-3" />
                                        </div>

                                        <!-- TEXT -->

                                        <div class="min-w-0 flex-1">
                                            <p
                                                class="text-xs font-black text-slate-800"
                                            >
                                                {{ permission.name }}
                                            </p>

                                            <p
                                                class="mt-1 font-mono text-[9px] font-semibold text-blue-500"
                                            >
                                                {{ permission.code }}
                                            </p>

                                            <p
                                                v-if="permission.description"
                                                class="mt-1.5 line-clamp-2 text-[11px] leading-4 text-slate-400"
                                            >
                                                {{ permission.description }}
                                            </p>
                                        </div>
                                    </button>
                                </div>
                            </section>
                        </div>

                        <!-- EMPTY -->

                        <div
                            v-else
                            class="flex min-h-[260px] flex-col items-center justify-center px-5 text-center"
                        >
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                            >
                                <KeyRound class="size-5" />
                            </div>

                            <h3 class="mt-4 text-sm font-black text-slate-800">
                                Aucune permission trouvée
                            </h3>

                            <p
                                class="mt-1 max-w-sm text-xs leading-5 text-slate-400"
                            >
                                Aucune permission ne correspond à votre
                                recherche.
                            </p>

                            <button
                                v-if="search"
                                type="button"
                                class="mt-4 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-600 shadow-sm"
                                @click="search = ''"
                            >
                                Réinitialiser
                            </button>
                        </div>
                    </section>

                    <!-- ================================================= -->
                    <!-- SECURITY MESSAGE                                  -->
                    <!-- ================================================= -->

                    <section
                        class="flex items-start gap-4 rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50/80 to-white p-4"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700"
                        >
                            <LockKeyhole class="size-4" />
                        </div>

                        <div>
                            <h3 class="text-sm font-black text-slate-900">
                                Vérification des autorisations
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Les modifications des permissions peuvent
                                modifier immédiatement l'accès des utilisateurs
                                associés à ce rôle.
                            </p>
                        </div>
                    </section>

                    <!-- ================================================= -->
                    <!-- BOTTOM ACTIONS                                    -->
                    <!-- ================================================= -->

                    <div
                        class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end"
                    >
                        <Link
                            href="/roles"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50"
                        >
                            Annuler
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save class="size-4" />

                            {{
                                form.processing
                                    ? "Enregistrement..."
                                    : "Enregistrer les modifications"
                            }}
                        </button>
                    </div>
                </main>
            </div>
        </form>
    </div>
</template>
