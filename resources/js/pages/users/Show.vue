<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import {
    Activity,
    ArrowLeft,
    AtSign,
    CalendarDays,
    CheckCircle2,
    Clock3,
    Edit3,
    Mail,
    Phone,
    ShieldCheck,
    UserRound,
} from "lucide-vue-next";
import { computed } from "vue";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type Role = {
    id: number;
    name: string;
    code?: string | null;
    description?: string | null;
};

type Environment = {
    id: number;
    name: string;
    code?: string | null;
};

type UserItem = {
    id: number;
    name?: string | null;
    first_name?: string | null;
    last_name?: string | null;
    username?: string | null;
    email?: string | null;
    phone?: string | null;
    active?: boolean | number;
    avatar?: string | null;
    last_login_at?: string | null;
    created_at?: string | null;
    updated_at?: string | null;

    role?: Role | null;
    current_role?: Role | null;
    roles?: Role[];

    environment?: Environment | null;
    environments?: Environment[];
};

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = withDefaults(
    defineProps<{
        user?: UserItem | null;
    }>(),
    {
        user: null,
    },
);

/*
|--------------------------------------------------------------------------
| Safe User
|--------------------------------------------------------------------------
|
| Important pour Inertia SSR :
| on utilise toujours safeUser au lieu de props.user directement.
|
*/

const safeUser = computed<UserItem>(() => {
    return (
        props.user ?? {
            id: 0,
            name: "",
            first_name: "",
            last_name: "",
            username: "",
            email: "",
            phone: "",
            active: false,
            avatar: null,
            last_login_at: null,
            created_at: null,
            updated_at: null,
            role: null,
            current_role: null,
            roles: [],
            environment: null,
            environments: [],
        }
    );
});

/*
|--------------------------------------------------------------------------
| User status
|--------------------------------------------------------------------------
*/

const isActive = computed(() => {
    return safeUser.value.active === true || safeUser.value.active === 1;
});

/*
|--------------------------------------------------------------------------
| Full name
|--------------------------------------------------------------------------
*/

const fullName = computed(() => {
    const composed = [safeUser.value.first_name, safeUser.value.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return composed || safeUser.value.name || "Utilisateur";
});

/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

const initials = computed(() => {
    const first =
        safeUser.value.first_name?.trim()?.charAt(0) ||
        safeUser.value.name?.trim()?.charAt(0) ||
        "U";

    const last = safeUser.value.last_name?.trim()?.charAt(0) || "";

    return `${first}${last}`.toUpperCase();
});

/*
|--------------------------------------------------------------------------
| Current Role
|--------------------------------------------------------------------------
*/

const currentRole = computed<Role | null>(() => {
    return (
        safeUser.value.current_role ??
        safeUser.value.role ??
        safeUser.value.roles?.[0] ??
        null
    );
});

/*
|--------------------------------------------------------------------------
| Current Environment
|--------------------------------------------------------------------------
*/

const currentEnvironment = computed<Environment | null>(() => {
    return (
        safeUser.value.environment ?? safeUser.value.environments?.[0] ?? null
    );
});

/*
|--------------------------------------------------------------------------
| Date formatter
|--------------------------------------------------------------------------
*/

const formatDate = (date?: string | null): string => {
    if (!date) {
        return "Non renseigné";
    }

    const parsed = new Date(date);

    if (Number.isNaN(parsed.getTime())) {
        return "Non renseigné";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(parsed);
};
</script>

<template>
    <Head :title="fullName" />

    <div class="min-h-full bg-slate-50/50">
        <!-- ============================================================ -->
        <!-- HEADER -->
        <!-- ============================================================ -->

        <div class="border-b border-slate-200/80 bg-white">
            <div
                class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between lg:px-7"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/users"
                        class="flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:bg-slate-50 hover:text-slate-900"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>

                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="text-xl font-bold tracking-tight text-slate-950"
                            >
                                Fiche utilisateur
                            </h1>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold"
                                :class="
                                    isActive
                                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                        : 'bg-slate-100 text-slate-500'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        isActive
                                            ? 'bg-emerald-500'
                                            : 'bg-slate-400'
                                    "
                                />

                                {{ isActive ? "Actif" : "Inactif" }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Consultez les informations et les accès de
                            l'utilisateur.
                        </p>
                    </div>
                </div>

                <Link
                    v-if="safeUser.id"
                    :href="`/users/${safeUser.id}/edit`"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    <Edit3 class="size-4" />

                    Modifier
                </Link>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- CONTENT -->
        <!-- ============================================================ -->

        <div class="grid gap-5 p-5 lg:grid-cols-[330px_minmax(0,1fr)] lg:p-7">
            <!-- ======================================================== -->
            <!-- LEFT -->
            <!-- ======================================================== -->

            <aside class="space-y-5">
                <!-- PROFILE -->

                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div
                        class="h-24 bg-gradient-to-br from-emerald-500 via-green-500 to-teal-500"
                    />

                    <div class="-mt-11 px-5 pb-5">
                        <div
                            class="flex h-22 w-22 items-center justify-center overflow-hidden rounded-3xl border-4 border-white bg-emerald-50 text-xl font-black text-emerald-700 shadow-sm"
                        >
                            <img
                                v-if="safeUser.avatar"
                                :src="safeUser.avatar"
                                :alt="fullName"
                                class="size-full object-cover"
                            />

                            <span v-else>
                                {{ initials }}
                            </span>
                        </div>

                        <h2 class="mt-4 text-lg font-black text-slate-900">
                            {{ fullName }}
                        </h2>

                        <p class="mt-1 text-sm text-slate-400">
                            {{
                                safeUser.email ||
                                "Adresse e-mail non renseignée"
                            }}
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                v-if="currentRole"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-teal-50 px-2.5 py-1.5 text-xs font-bold text-teal-700 ring-1 ring-teal-100"
                            >
                                <ShieldCheck class="size-3.5" />

                                {{ currentRole.name }}
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-bold"
                                :class="
                                    isActive
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500'
                                "
                            >
                                <CheckCircle2 class="size-3.5" />

                                {{
                                    isActive ? "Compte actif" : "Compte inactif"
                                }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- ACCOUNT -->

                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-4 py-3.5">
                        <h3 class="text-sm font-bold text-slate-900">Compte</h3>
                    </div>

                    <div class="space-y-4 p-4">
                        <!-- USERNAME -->

                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"
                            >
                                <AtSign class="size-4" />
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Identifiant
                                </p>

                                <p
                                    class="mt-1 truncate text-sm font-semibold text-slate-700"
                                >
                                    {{ safeUser.username || "Non renseigné" }}
                                </p>
                            </div>
                        </div>

                        <!-- LAST LOGIN -->

                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500"
                            >
                                <Clock3 class="size-4" />
                            </div>

                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Dernière connexion
                                </p>

                                <p
                                    class="mt-1 text-sm font-semibold text-slate-700"
                                >
                                    {{
                                        safeUser.last_login_at
                                            ? formatDate(safeUser.last_login_at)
                                            : "Jamais connecté"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>

            <!-- ======================================================== -->
            <!-- RIGHT -->
            <!-- ======================================================== -->

            <main class="space-y-5">
                <!-- ==================================================== -->
                <!-- GENERAL INFORMATION -->
                <!-- ==================================================== -->

                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                            >
                                <UserRound class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Informations générales
                                </h2>

                                <p class="text-xs text-slate-400">
                                    Informations personnelles et coordonnées
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="grid divide-y divide-slate-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0"
                    >
                        <!-- NAME -->

                        <div class="space-y-5 p-5">
                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Prénom
                                </p>

                                <p
                                    class="mt-1.5 text-sm font-bold text-slate-800"
                                >
                                    {{ safeUser.first_name || "—" }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Nom
                                </p>

                                <p
                                    class="mt-1.5 text-sm font-bold text-slate-800"
                                >
                                    {{ safeUser.last_name || "—" }}
                                </p>
                            </div>
                        </div>

                        <!-- CONTACT -->

                        <div class="space-y-5 p-5">
                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Adresse e-mail
                                </p>

                                <a
                                    v-if="safeUser.email"
                                    :href="`mailto:${safeUser.email}`"
                                    class="mt-1.5 flex items-center gap-2 text-sm font-semibold text-emerald-700 transition hover:text-emerald-800"
                                >
                                    <Mail class="size-4" />

                                    {{ safeUser.email }}
                                </a>

                                <p
                                    v-else
                                    class="mt-1.5 text-sm font-semibold text-slate-400"
                                >
                                    Non renseignée
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                                >
                                    Téléphone
                                </p>

                                <p
                                    class="mt-1.5 flex items-center gap-2 text-sm font-semibold text-slate-700"
                                >
                                    <Phone class="size-4 text-slate-400" />

                                    {{ safeUser.phone || "Non renseigné" }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ==================================================== -->
                <!-- ROLE & ACCESS -->
                <!-- ==================================================== -->

                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600"
                            >
                                <ShieldCheck class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Rôle & accès
                                </h2>

                                <p class="text-xs text-slate-400">
                                    Niveau d'autorisation de l'utilisateur
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 md:grid-cols-2">
                        <!-- ROLE -->

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50/70 p-4"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Rôle
                            </p>

                            <div
                                v-if="currentRole"
                                class="mt-3 flex items-center gap-3"
                            >
                                <div
                                    class="flex size-10 items-center justify-center rounded-xl bg-teal-100 text-teal-700"
                                >
                                    <ShieldCheck class="size-5" />
                                </div>

                                <div>
                                    <p class="text-sm font-bold text-slate-900">
                                        {{ currentRole.name }}
                                    </p>

                                    <p
                                        v-if="currentRole.code"
                                        class="mt-0.5 text-xs text-slate-400"
                                    >
                                        {{ currentRole.code }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-else
                                class="mt-3 text-sm font-medium text-slate-400"
                            >
                                Aucun rôle affecté
                            </p>
                        </div>

                        <!-- ENVIRONMENT -->

                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50/70 p-4"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400"
                            >
                                Établissement
                            </p>

                            <p class="mt-3 text-sm font-bold text-slate-800">
                                {{
                                    currentEnvironment?.name ?? "Non renseigné"
                                }}
                            </p>

                            <p
                                v-if="currentEnvironment?.code"
                                class="mt-1 text-xs text-slate-400"
                            >
                                {{ currentEnvironment.code }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- ==================================================== -->
                <!-- ACTIVITY -->
                <!-- ==================================================== -->

                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                            >
                                <Activity class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Activité du compte
                                </h2>

                                <p class="text-xs text-slate-400">
                                    Informations système du compte
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 p-5 sm:grid-cols-3">
                        <!-- CREATED -->

                        <div
                            class="rounded-xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <CalendarDays class="size-4 text-emerald-600" />

                            <p
                                class="mt-3 text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Création
                            </p>

                            <p
                                class="mt-1 text-xs font-bold leading-5 text-slate-700"
                            >
                                {{ formatDate(safeUser.created_at) }}
                            </p>
                        </div>

                        <!-- LOGIN -->

                        <div
                            class="rounded-xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <Clock3 class="size-4 text-blue-600" />

                            <p
                                class="mt-3 text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Dernière connexion
                            </p>

                            <p
                                class="mt-1 text-xs font-bold leading-5 text-slate-700"
                            >
                                {{
                                    safeUser.last_login_at
                                        ? formatDate(safeUser.last_login_at)
                                        : "Jamais"
                                }}
                            </p>
                        </div>

                        <!-- UPDATED -->

                        <div
                            class="rounded-xl border border-slate-100 bg-slate-50 p-4"
                        >
                            <Activity class="size-4 text-amber-600" />

                            <p
                                class="mt-3 text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Dernière modification
                            </p>

                            <p
                                class="mt-1 text-xs font-bold leading-5 text-slate-700"
                            >
                                {{ formatDate(safeUser.updated_at) }}
                            </p>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</template>
