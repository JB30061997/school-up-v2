<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    AtSign,
    Check,
    ChevronDown,
    Eye,
    EyeOff,
    KeyRound,
    LockKeyhole,
    Mail,
    Phone,
    Save,
    ShieldCheck,
    UserPlus,
    UserRound,
} from "lucide-vue-next";
import { computed, ref } from "vue";

type Role = {
    id: number;
    name: string;
    code?: string | null;
    description?: string | null;
};

type Environment = {
    id: number;
    name: string;
};

const props = withDefaults(
    defineProps<{
        roles?: Role[];
        environments?: Environment[];
        currentEnvironmentId?: number | null;
    }>(),
    {
        roles: () => [],
        environments: () => [],
        currentEnvironmentId: null,
    },
);

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    first_name: "",
    last_name: "",
    username: "",
    email: "",
    phone: "",
    password: "",
    password_confirmation: "",
    active: true,
    role_id: "",
    environment_id: props.currentEnvironmentId
        ? String(props.currentEnvironmentId)
        : "",
});

const fullNamePreview = computed(() => {
    const value = `${form.first_name} ${form.last_name}`.trim();

    return value || "Nouvel utilisateur";
});

const initials = computed(() => {
    const first = form.first_name?.trim()?.charAt(0) || "";
    const last = form.last_name?.trim()?.charAt(0) || "";

    return `${first}${last}`.toUpperCase() || "NU";
});

const submit = () => {
    form.post("/users", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvel utilisateur" />

    <div class="min-h-full bg-slate-50/50">
        <!-- HEADER -->
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

                    <div
                        class="flex size-11 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-sm shadow-emerald-200"
                    >
                        <UserPlus class="size-5" />
                    </div>

                    <div>
                        <h1
                            class="text-xl font-bold tracking-tight text-slate-950"
                        >
                            Nouvel utilisateur
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Créer un nouveau compte et définir ses droits
                            d'accès.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/users"
                        class="inline-flex h-10 items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Annuler
                    </Link>

                    <button
                        type="button"
                        :disabled="form.processing"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        @click="submit"
                    >
                        <Save class="size-4" />

                        {{
                            form.processing ? "Création..." : "Créer le compte"
                        }}
                    </button>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <form
            class="grid gap-5 p-5 lg:grid-cols-[minmax(0,1fr)_330px] lg:p-7"
            @submit.prevent="submit"
        >
            <div class="space-y-5">
                <!-- IDENTITE -->
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
                                <p class="text-xs text-slate-400">
                                    Identité et coordonnées de l'utilisateur
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
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
                                placeholder="Ex. Jaouad"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />

                            <p
                                v-if="form.errors.first_name"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.first_name }}
                            </p>
                        </div>

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
                                placeholder="Ex. Braouz"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />

                            <p
                                v-if="form.errors.last_name"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.last_name }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Adresse e-mail
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">
                                <Mail
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.email"
                                    type="email"
                                    placeholder="nom@ecole.com"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>

                            <p
                                v-if="form.errors.email"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Téléphone
                            </label>

                            <div class="relative">
                                <Phone
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.phone"
                                    type="text"
                                    placeholder="+212..."
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />
                            </div>
                        </div>
                    </div>
                </section>

                <!-- COMPTE -->
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
                                    Informations du compte
                                </h2>
                                <p class="text-xs text-slate-400">
                                    Identifiant et statut du compte
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Nom d'utilisateur
                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                v-model="form.username"
                                type="text"
                                placeholder="Ex. jbraouz"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />

                            <p
                                v-if="form.errors.username"
                                class="mt-1.5 text-xs font-medium text-rose-500"
                            >
                                {{ form.errors.username }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Statut
                            </label>

                            <button
                                type="button"
                                class="flex h-11 w-full items-center justify-between rounded-xl border px-3.5 transition"
                                :class="
                                    form.active
                                        ? 'border-emerald-200 bg-emerald-50'
                                        : 'border-slate-200 bg-slate-50'
                                "
                                @click="form.active = !form.active"
                            >
                                <span class="flex items-center gap-2">
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
                                    class="relative h-6 w-11 rounded-full transition"
                                    :class="
                                        form.active
                                            ? 'bg-emerald-500'
                                            : 'bg-slate-300'
                                    "
                                >
                                    <span
                                        class="absolute top-1 size-4 rounded-full bg-white shadow-sm transition-all"
                                        :class="
                                            form.active ? 'left-6' : 'left-1'
                                        "
                                    />
                                </span>
                            </button>
                        </div>
                    </div>
                </section>

                <!-- PASSWORD -->
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
                                <p class="text-xs text-slate-400">
                                    Définissez le mot de passe initial
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Mot de passe
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">
                                <LockKeyhole
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-11 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700"
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

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Confirmation
                                <span class="text-rose-500">*</span>
                            </label>

                            <div class="relative">
                                <LockKeyhole
                                    class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.password_confirmation"
                                    :type="
                                        showPasswordConfirmation
                                            ? 'text'
                                            : 'password'
                                    "
                                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-11 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                />

                                <button
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
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
                        </div>
                    </div>
                </section>
            </div>

            <!-- RIGHT -->
            <aside class="space-y-5">
                <section
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm"
                >
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="flex size-20 items-center justify-center rounded-3xl bg-gradient-to-br from-emerald-100 to-green-50 text-xl font-black text-emerald-700 ring-1 ring-emerald-100"
                        >
                            {{ initials }}
                        </div>

                        <h3 class="mt-4 text-base font-bold text-slate-900">
                            {{ fullNamePreview }}
                        </h3>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ form.email || "Adresse e-mail" }}
                        </p>

                        <span
                            class="mt-3 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold"
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
                    </div>
                </section>

                <!-- ACCESS -->
                <section
                    class="rounded-2xl border border-slate-200/80 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 p-4">
                        <div class="flex items-center gap-2">
                            <ShieldCheck class="size-4 text-emerald-600" />

                            <h3 class="text-sm font-bold text-slate-900">
                                Accès & rôle
                            </h3>
                        </div>
                    </div>

                    <div class="space-y-4 p-4">
                        <div v-if="environments.length">
                            <label
                                class="mb-2 block text-xs font-bold text-slate-600"
                            >
                                Établissement
                            </label>

                            <div class="relative">
                                <select
                                    v-model="form.environment_id"
                                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 pr-9 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                                >
                                    <option value="">Sélectionner</option>

                                    <option
                                        v-for="environment in environments"
                                        :key="environment.id"
                                        :value="String(environment.id)"
                                    >
                                        {{ environment.name }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                />
                            </div>
                        </div>

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
                                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 pr-9 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
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
                                    class="pointer-events-none absolute right-3 top-1/2 size-4 -translate-y-1/2 text-slate-400"
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

                <div
                    class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4"
                >
                    <div class="flex gap-3">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600"
                        >
                            <Check class="size-4" />
                        </div>

                        <p class="text-xs leading-5 text-emerald-800">
                            Le rôle détermine les fonctionnalités et données
                            auxquelles cet utilisateur pourra accéder.
                        </p>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</template>
