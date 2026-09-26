<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    AtSign,
    ChevronDown,
    Eye,
    EyeOff,
    KeyRound,
    LockKeyhole,
    Mail,
    Pencil,
    Phone,
    Save,
    ShieldCheck,
    UserRound,
} from "lucide-vue-next";
import { computed, ref, watch } from "vue";

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

type UserRole = {
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

    role?: UserRole | null;
    current_role?: UserRole | null;
    roles?: UserRole[];

    environment?: Environment | null;
    environments?: Environment[];

    environment_id?: number | null;
};

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = withDefaults(
    defineProps<{
        user?: UserItem | null;
        roles?: Role[];
        environments?: Environment[];
        currentEnvironmentId?: number | null;
    }>(),
    {
        user: null,
        roles: () => [],
        environments: () => [],
        currentEnvironmentId: null,
    },
);

/*
|--------------------------------------------------------------------------
| Safe User
|--------------------------------------------------------------------------
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

            role: null,
            current_role: null,
            roles: [],

            environment: null,
            environments: [],
            environment_id: null,
        }
    );
});

/*
|--------------------------------------------------------------------------
| Current Role
|--------------------------------------------------------------------------
*/

const currentRole = computed<UserRole | null>(() => {
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
| Password Visibility
|--------------------------------------------------------------------------
*/

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    first_name: "",
    last_name: "",
    username: "",
    email: "",
    phone: "",

    active: true,

    role_id: "",
    environment_id: "",

    password: "",
    password_confirmation: "",
});

/*
|--------------------------------------------------------------------------
| Fill Form
|--------------------------------------------------------------------------
*/

const fillForm = (user: UserItem | null | undefined) => {
    if (!user) {
        return;
    }

    form.first_name = user.first_name ?? "";
    form.last_name = user.last_name ?? "";
    form.username = user.username ?? "";
    form.email = user.email ?? "";
    form.phone = user.phone ?? "";

    form.active = user.active === true || user.active === 1;

    const role = user.current_role ?? user.role ?? user.roles?.[0] ?? null;

    form.role_id = role ? String(role.id) : "";

    const environmentId =
        user.environment_id ??
        user.environment?.id ??
        user.environments?.[0]?.id ??
        props.currentEnvironmentId ??
        null;

    form.environment_id = environmentId ? String(environmentId) : "";

    /*
     * On ne remplit jamais le mot de passe existant.
     */
    form.password = "";
    form.password_confirmation = "";
};

/*
|--------------------------------------------------------------------------
| Sync Props -> Form
|--------------------------------------------------------------------------
|
| immediate = true :
| si user existe déjà pendant SSR, le formulaire est rempli immédiatement.
|
| Si les props changent ensuite, le formulaire est synchronisé.
|
*/

watch(
    () => props.user,
    (user) => {
        fillForm(user);
    },
    {
        immediate: true,
        deep: true,
    },
);

/*
|--------------------------------------------------------------------------
| User Display
|--------------------------------------------------------------------------
*/

const fullName = computed(() => {
    const composed = [form.first_name, form.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return composed || safeUser.value.name || "Utilisateur";
});

const initials = computed(() => {
    const first =
        form.first_name?.trim()?.charAt(0) ||
        safeUser.value.name?.trim()?.charAt(0) ||
        "U";

    const last = form.last_name?.trim()?.charAt(0) || "";

    return `${first}${last}`.toUpperCase();
});

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    const userId = safeUser.value.id;

    if (!userId) {
        console.error(
            "Impossible de modifier l'utilisateur : identifiant utilisateur absent.",
        );

        return;
    }

    form.put(`/users/${userId}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Modifier - ${fullName}`" />

    <div class="min-h-full bg-slate-50/50">
        <!-- ============================================================ -->
        <!-- HEADER -->
        <!-- ============================================================ -->
        <div class="border-b border-slate-200/80 bg-white">
            <div
                class="flex flex-col gap-4 px-5 py-5 lg:flex-row lg:items-center lg:justify-between lg:px-7"
            >
                <div class="flex min-w-0 items-center gap-4">
                    <!-- BACK -->
                    <Link
                        :href="safeUser.id ? `/users/${safeUser.id}` : '/users'"
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>

                    <!-- ICON -->
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-sm shadow-emerald-200"
                    >
                        <Pencil class="size-5" />
                    </div>

                    <!-- TITLE -->
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1
                                class="truncate text-xl font-bold tracking-tight text-slate-950"
                            >
                                Modifier l'utilisateur
                            </h1>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold"
                                :class="
                                    form.active
                                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100'
                                        : 'bg-slate-100 text-slate-500 ring-1 ring-slate-200'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        form.active
                                            ? 'bg-emerald-500'
                                            : 'bg-slate-400'
                                    "
                                />

                                {{ form.active ? "Actif" : "Inactif" }}
                            </span>
                        </div>

                        <p class="mt-1 truncate text-sm text-slate-500">
                            {{ fullName }}
                        </p>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex items-center gap-2">
                    <Link
                        :href="safeUser.id ? `/users/${safeUser.id}` : '/users'"
                        class="inline-flex h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900"
                    >
                        Annuler
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing || !safeUser.id"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
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

        <!-- ============================================================ -->
        <!-- CONTENT -->
        <!-- ============================================================ -->
        <form
            class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_330px] lg:p-7"
            @submit.prevent="submit"
        >
            <!-- ======================================================== -->
            <!-- LEFT -->
            <!-- ======================================================== -->
            <div class="space-y-5">
                <!-- ==================================================== -->
                <!-- INFORMATIONS PERSONNELLES -->
                <!-- ==================================================== -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
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
                                    Informations personnelles
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Identité et coordonnées de l'utilisateur
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <!-- FIRST NAME -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Prénom
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                v-model="form.first_name"
                                type="text"
                                autocomplete="given-name"
                                placeholder="Prénom"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />

                            <p
                                v-if="form.errors.first_name"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.first_name }}
                            </p>
                        </div>

                        <!-- LAST NAME -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Nom
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                v-model="form.last_name"
                                type="text"
                                autocomplete="family-name"
                                placeholder="Nom"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />

                            <p
                                v-if="form.errors.last_name"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.last_name }}
                            </p>
                        </div>

                        <!-- EMAIL -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Adresse e-mail
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">
                                <Mail
                                    class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="nom@ecole.com"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>

                            <p
                                v-if="form.errors.email"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <!-- PHONE -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Téléphone
                            </label>

                            <div class="relative">
                                <Phone
                                    class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.phone"
                                    type="text"
                                    autocomplete="tel"
                                    placeholder="+212 6..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>

                            <p
                                v-if="form.errors.phone"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.phone }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- ==================================================== -->
                <!-- COMPTE -->
                <!-- ==================================================== -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-teal-50 text-teal-600"
                            >
                                <AtSign class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Compte utilisateur
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Identifiant de connexion et état du compte
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <!-- USERNAME -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Nom d'utilisateur
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">
                                <AtSign
                                    class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.username"
                                    type="text"
                                    autocomplete="username"
                                    placeholder="Identifiant"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>

                            <p
                                v-if="form.errors.username"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.username }}
                            </p>
                        </div>

                        <!-- STATUS -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Statut du compte
                            </label>

                            <button
                                type="button"
                                class="flex h-11 w-full items-center justify-between rounded-xl border px-3.5 transition"
                                :class="
                                    form.active
                                        ? 'border-emerald-200 bg-emerald-50/70'
                                        : 'border-slate-200 bg-slate-50'
                                "
                                @click="form.active = !form.active"
                            >
                                <span class="flex items-center gap-2.5">
                                    <span
                                        class="size-2 rounded-full"
                                        :class="
                                            form.active
                                                ? 'bg-emerald-500'
                                                : 'bg-slate-400'
                                        "
                                    />

                                    <span
                                        class="text-sm font-semibold"
                                        :class="
                                            form.active
                                                ? 'text-emerald-700'
                                                : 'text-slate-600'
                                        "
                                    >
                                        {{
                                            form.active
                                                ? "Compte actif"
                                                : "Compte inactif"
                                        }}
                                    </span>
                                </span>

                                <span
                                    class="relative h-6 w-11 shrink-0 rounded-full transition-colors"
                                    :class="
                                        form.active
                                            ? 'bg-emerald-500'
                                            : 'bg-slate-300'
                                    "
                                >
                                    <span
                                        class="absolute top-1 size-4 rounded-full bg-white shadow-sm transition-all duration-200"
                                        :class="
                                            form.active ? 'left-6' : 'left-1'
                                        "
                                    />
                                </span>
                            </button>

                            <p
                                v-if="form.errors.active"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.active }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- ==================================================== -->
                <!-- PASSWORD -->
                <!-- ==================================================== -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600"
                            >
                                <KeyRound class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold text-slate-900">
                                    Sécurité
                                </h2>

                                <p class="mt-0.5 text-xs text-slate-400">
                                    Modifier le mot de passe de l'utilisateur
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- INFO -->
                    <div
                        class="mx-5 mt-5 rounded-xl border border-amber-100 bg-amber-50/60 px-4 py-3"
                    >
                        <p class="text-xs leading-5 text-amber-800">
                            Laissez les deux champs vides si vous souhaitez
                            conserver le mot de passe actuel.
                        </p>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <!-- PASSWORD -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Nouveau mot de passe
                            </label>

                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-11 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 flex size-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                    @click="showPassword = !showPassword"
                                >
                                    <EyeOff
                                        v-if="showPassword"
                                        class="size-4"
                                    />

                                    <Eye v-else class="size-4" />
                                </button>
                            </div>

                            <p
                                v-if="form.errors.password"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <!-- PASSWORD CONFIRMATION -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Confirmer le mot de passe
                            </label>

                            <div class="relative">
                                <LockKeyhole
                                    class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.password_confirmation"
                                    :type="
                                        showPasswordConfirmation
                                            ? 'text'
                                            : 'password'
                                    "
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-11 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 flex size-7 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                    @click="
                                        showPasswordConfirmation =
                                            !showPasswordConfirmation
                                    "
                                >
                                    <EyeOff
                                        v-if="showPasswordConfirmation"
                                        class="size-4"
                                    />

                                    <Eye v-else class="size-4" />
                                </button>
                            </div>

                            <p
                                v-if="form.errors.password_confirmation"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ======================================================== -->
            <!-- RIGHT -->
            <!-- ======================================================== -->
            <aside class="space-y-5">
                <!-- ==================================================== -->
                <!-- USER PREVIEW -->
                <!-- ==================================================== -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <!-- GREEN COVER -->
                    <div
                        class="h-20 bg-gradient-to-br from-emerald-500 via-green-500 to-teal-500"
                    />

                    <div class="-mt-9 px-5 pb-5">
                        <!-- AVATAR -->
                        <div
                            class="flex size-18 h-18 w-18 items-center justify-center overflow-hidden rounded-2xl border-4 border-white bg-emerald-50 text-lg font-black text-emerald-700 shadow-sm"
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

                        <div class="mt-3">
                            <h3
                                class="truncate text-base font-bold text-slate-900"
                            >
                                {{ fullName }}
                            </h3>

                            <p class="mt-1 truncate text-xs text-slate-400">
                                {{
                                    form.email ||
                                    "Adresse e-mail non renseignée"
                                }}
                            </p>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold"
                                :class="
                                    form.active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-500'
                                "
                            >
                                <span
                                    class="size-1.5 rounded-full"
                                    :class="
                                        form.active
                                            ? 'bg-emerald-500'
                                            : 'bg-slate-400'
                                    "
                                />

                                {{ form.active ? "Actif" : "Inactif" }}
                            </span>

                            <span
                                v-if="currentRole"
                                class="inline-flex items-center gap-1.5 rounded-full bg-teal-50 px-2.5 py-1 text-[11px] font-bold text-teal-700"
                            >
                                <ShieldCheck class="size-3" />

                                {{ currentRole.name }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- ==================================================== -->
                <!-- ROLE & ACCESS -->
                <!-- ==================================================== -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-4 py-4">
                        <div class="flex items-center gap-2.5">
                            <div
                                class="flex size-8 items-center justify-center rounded-lg bg-teal-50 text-teal-600"
                            >
                                <ShieldCheck class="size-4" />
                            </div>

                            <div>
                                <h3 class="text-sm font-bold text-slate-900">
                                    Rôle & accès
                                </h3>

                                <p class="text-[11px] text-slate-400">
                                    Autorisations de l'utilisateur
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4 p-4">
                        <!-- ENVIRONMENT -->
                        <div v-if="environments.length > 0">
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Établissement
                            </label>

                            <div class="relative">
                                <select
                                    v-model="form.environment_id"
                                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option value="">
                                        Sélectionner un établissement
                                    </option>

                                    <option
                                        v-for="environment in environments"
                                        :key="environment.id"
                                        :value="String(environment.id)"
                                    >
                                        {{ environment.name }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>

                            <p
                                v-if="form.errors.environment_id"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.environment_id }}
                            </p>
                        </div>

                        <!-- FALLBACK CURRENT ENVIRONMENT -->
                        <div
                            v-else-if="currentEnvironment"
                            class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5"
                        >
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Établissement
                            </p>

                            <p class="mt-1.5 text-sm font-bold text-slate-800">
                                {{ currentEnvironment.name }}
                            </p>
                        </div>

                        <!-- ROLE -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Rôle
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">
                                <select
                                    v-model="form.role_id"
                                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option value="">
                                        Sélectionner un rôle
                                    </option>

                                    <option
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="String(role.id)"
                                    >
                                        {{ role.name }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>

                            <p
                                v-if="form.errors.role_id"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.role_id }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- ==================================================== -->
                <!-- HELP -->
                <!-- ==================================================== -->
                <section
                    class="rounded-2xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-green-50/50 p-4"
                >
                    <div class="flex gap-3">
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm ring-1 ring-emerald-100"
                        >
                            <ShieldCheck class="size-4" />
                        </div>

                        <div>
                            <p class="text-xs font-bold text-emerald-900">
                                Gestion des autorisations
                            </p>

                            <p
                                class="mt-1 text-xs leading-5 text-emerald-800/80"
                            >
                                Le rôle attribué détermine les fonctionnalités
                                auxquelles cet utilisateur peut accéder dans
                                l'établissement sélectionné.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- MOBILE SAVE -->
                <button
                    type="submit"
                    :disabled="form.processing || !safeUser.id"
                    class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50 lg:hidden"
                >
                    <Save class="size-4" />

                    {{
                        form.processing
                            ? "Enregistrement..."
                            : "Enregistrer les modifications"
                    }}
                </button>
            </aside>
        </form>
    </div>
</template>
