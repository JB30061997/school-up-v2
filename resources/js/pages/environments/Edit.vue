<script setup lang="ts">
import { ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

import {
    ArrowLeft,
    Building2,
    CheckCircle2,
    ExternalLink,
    GraduationCap,
    Image,
    Palette,
    Save,
    Trash2,
    Upload,
    Users,
} from "lucide-vue-next";

type SchoolYear = {
    id: number;
    name?: string;
    label?: string;
    code?: string;
};

type Environment = {
    id: number;
    name: string;
    code: string;
    app_name?: string | null;
    url?: string | null;

    logo?: string | null;
    logo_url?: string | null;

    logo_dark?: string | null;
    logo_dark_url?: string | null;

    favicon?: string | null;
    favicon_url?: string | null;

    primary_color?: string | null;
    secondary_color?: string | null;
    sidebar_color?: string | null;
    sidebar_text_color?: string | null;
    accent_color?: string | null;

    current_exercise?: string | null;
    active: boolean;

    users_count: number;
    active_users_count: number;
    school_years_count: number;

    current_school_year?: SchoolYear | null;
};

const props = defineProps<{
    environment: Environment;
}>();

const form = useForm({
    name: props.environment.name ?? "",
    code: props.environment.code ?? "",
    app_name: props.environment.app_name ?? "School Up",
    url: props.environment.url ?? "",

    current_exercise: props.environment.current_exercise ?? "",

    active: Boolean(props.environment.active),

    primary_color: props.environment.primary_color ?? "#8B1E2D",

    secondary_color: props.environment.secondary_color ?? "#641520",

    sidebar_color: props.environment.sidebar_color ?? "#111827",

    sidebar_text_color: props.environment.sidebar_text_color ?? "#FFFFFF",

    accent_color: props.environment.accent_color ?? "#D4AF37",

    logo: null as File | null,
    logo_dark: null as File | null,
    favicon: null as File | null,

    remove_logo: false,
    remove_logo_dark: false,
    remove_favicon: false,

    _method: "PUT",
});

const logoPreview = ref<string | null>(props.environment.logo_url ?? null);

const logoDarkPreview = ref<string | null>(
    props.environment.logo_dark_url ?? null,
);

const faviconPreview = ref<string | null>(
    props.environment.favicon_url ?? null,
);

const handleFile = (event: Event, field: "logo" | "logo_dark" | "favicon") => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    form[field] = file;

    if (!file) {
        return;
    }

    const preview = URL.createObjectURL(file);

    if (field === "logo") {
        logoPreview.value = preview;
        form.remove_logo = false;
    }

    if (field === "logo_dark") {
        logoDarkPreview.value = preview;
        form.remove_logo_dark = false;
    }

    if (field === "favicon") {
        faviconPreview.value = preview;
        form.remove_favicon = false;
    }
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreview.value = null;
};

const removeLogoDark = () => {
    form.logo_dark = null;
    form.remove_logo_dark = true;
    logoDarkPreview.value = null;
};

const removeFavicon = () => {
    form.favicon = null;
    form.remove_favicon = true;
    faviconPreview.value = null;
};

const submit = () => {
    form.post(`/environments/${props.environment.id}`, {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Modifier ${environment.name}`" />

    <div class="min-h-full bg-[#fafbfc]">
        <div class="mx-auto max-w-[1600px] space-y-6 p-5 md:p-7 lg:p-8">
            <!-- HEADER -->

            <section
                class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-32 size-[450px] rounded-full bg-violet-100/70 blur-[100px]"
                />

                <div
                    class="relative flex flex-col gap-6 p-7 md:p-9 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="flex items-start gap-5">
                        <Link
                            href="/environments"
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:text-violet-600"
                        >
                            <ArrowLeft class="size-5" />
                        </Link>

                        <div
                            class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-[20px] border border-slate-100 bg-white shadow-sm"
                        >
                            <img
                                v-if="logoPreview"
                                :src="logoPreview"
                                class="max-h-11 max-w-11 object-contain"
                            />

                            <Building2 v-else class="size-8 text-violet-600" />
                        </div>

                        <div>
                            <div class="flex flex-wrap items-center gap-3">
                                <h1
                                    class="text-3xl font-bold tracking-tight text-slate-950 md:text-[36px]"
                                >
                                    {{ environment.name }}
                                </h1>

                                <span
                                    class="rounded-lg bg-slate-100 px-3 py-1.5 font-mono text-[10px] font-bold text-slate-500"
                                >
                                    {{ environment.code }}
                                </span>

                                <span
                                    class="rounded-full px-3 py-1.5 text-[10px] font-bold"
                                    :class="
                                        form.active
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-rose-50 text-rose-600'
                                    "
                                >
                                    {{ form.active ? "Actif" : "Inactif" }}
                                </span>
                            </div>

                            <p class="mt-2 text-sm text-slate-500">
                                Configuration et identité de l'environnement.
                            </p>
                        </div>
                    </div>

                    <a
                        v-if="environment.url"
                        :href="environment.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 shadow-sm transition hover:border-violet-200 hover:text-violet-600"
                    >
                        <ExternalLink class="size-4" />

                        Ouvrir le site
                    </a>
                </div>
            </section>

            <!-- STATS -->

            <section class="grid gap-4 md:grid-cols-3">
                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Utilisateurs
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-950">
                                {{ environment.users_count ?? 0 }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ environment.active_users_count ?? 0 }}
                                actifs
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
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Années scolaires
                            </p>

                            <p class="mt-2 text-3xl font-bold text-slate-950">
                                {{ environment.school_years_count ?? 0 }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Configurées
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                        >
                            <GraduationCap class="size-6" />
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Exercice
                            </p>

                            <p class="mt-2 text-xl font-bold text-slate-950">
                                {{ form.current_exercise || "Non défini" }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                Exercice courant
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                        >
                            <CheckCircle2 class="size-6" />
                        </div>
                    </div>
                </div>
            </section>

            <form
                class="grid gap-6 xl:grid-cols-[1.45fr_0.8fr]"
                @submit.prevent="submit"
            >
                <!-- LEFT -->

                <div class="space-y-6">
                    <!-- GENERAL -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm md:p-7"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-violet-50 text-violet-600"
                            >
                                <Building2 class="size-6" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Informations générales
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Paramètres principaux de l'établissement.
                                </p>
                            </div>
                        </div>

                        <div class="mt-7 grid gap-5 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Nom
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm font-medium outline-none focus:border-violet-300 focus:ring-4 focus:ring-violet-50"
                                />

                                <p
                                    v-if="form.errors.name"
                                    class="mt-2 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Code
                                </label>

                                <input
                                    v-model="form.code"
                                    type="text"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 font-mono text-sm font-bold uppercase outline-none focus:border-violet-300 focus:ring-4 focus:ring-violet-50"
                                />

                                <p
                                    v-if="form.errors.code"
                                    class="mt-2 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Application
                                </label>

                                <input
                                    v-model="form.app_name"
                                    type="text"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm outline-none focus:border-violet-300 focus:ring-4 focus:ring-violet-50"
                                />
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Exercice courant
                                </label>

                                <input
                                    v-model="form.current_exercise"
                                    type="text"
                                    placeholder="2026-2027"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm outline-none focus:border-violet-300 focus:ring-4 focus:ring-violet-50"
                                />
                            </div>
                        </div>

                        <div class="mt-5">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                URL
                            </label>

                            <input
                                v-model="form.url"
                                type="url"
                                placeholder="https://..."
                                class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm outline-none focus:border-violet-300 focus:ring-4 focus:ring-violet-50"
                            />

                            <p
                                v-if="form.errors.url"
                                class="mt-2 text-xs font-semibold text-rose-500"
                            >
                                {{ form.errors.url }}
                            </p>
                        </div>
                    </section>

                    <!-- FILES -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm md:p-7"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600"
                            >
                                <Image class="size-6" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Identité graphique
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Logos et favicon.
                                </p>
                            </div>
                        </div>

                        <div class="mt-7 grid gap-5 md:grid-cols-3">
                            <!-- MAIN -->

                            <div
                                class="rounded-[22px] border border-slate-200 bg-slate-50 p-4"
                            >
                                <div
                                    class="flex h-28 items-center justify-center rounded-2xl bg-white"
                                >
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        class="max-h-16 max-w-[150px] object-contain"
                                    />

                                    <Image
                                        v-else
                                        class="size-7 text-slate-300"
                                    />
                                </div>

                                <p
                                    class="mt-4 text-center text-sm font-bold text-slate-700"
                                >
                                    Logo principal
                                </p>

                                <div class="mt-4 flex gap-2">
                                    <label
                                        class="flex h-10 flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl bg-slate-950 text-xs font-bold text-white"
                                    >
                                        <Upload class="size-3.5" />

                                        Modifier

                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="handleFile($event, 'logo')"
                                        />
                                    </label>

                                    <button
                                        v-if="logoPreview"
                                        type="button"
                                        class="flex size-10 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-500"
                                        @click="removeLogo"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- DARK -->

                            <div
                                class="rounded-[22px] border border-slate-800 bg-slate-950 p-4"
                            >
                                <div
                                    class="flex h-28 items-center justify-center rounded-2xl bg-slate-900"
                                >
                                    <img
                                        v-if="logoDarkPreview"
                                        :src="logoDarkPreview"
                                        class="max-h-16 max-w-[150px] object-contain"
                                    />

                                    <Image
                                        v-else
                                        class="size-7 text-slate-600"
                                    />
                                </div>

                                <p
                                    class="mt-4 text-center text-sm font-bold text-white"
                                >
                                    Logo sombre
                                </p>

                                <div class="mt-4 flex gap-2">
                                    <label
                                        class="flex h-10 flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl bg-white text-xs font-bold text-slate-800"
                                    >
                                        <Upload class="size-3.5" />

                                        Modifier

                                        <input
                                            type="file"
                                            accept="image/*"
                                            class="hidden"
                                            @change="
                                                handleFile($event, 'logo_dark')
                                            "
                                        />
                                    </label>

                                    <button
                                        v-if="logoDarkPreview"
                                        type="button"
                                        class="flex size-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-400"
                                        @click="removeLogoDark"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- FAVICON -->

                            <div
                                class="rounded-[22px] border border-slate-200 bg-slate-50 p-4"
                            >
                                <div
                                    class="flex h-28 items-center justify-center rounded-2xl bg-white"
                                >
                                    <img
                                        v-if="faviconPreview"
                                        :src="faviconPreview"
                                        class="size-14 object-contain"
                                    />

                                    <Image
                                        v-else
                                        class="size-7 text-slate-300"
                                    />
                                </div>

                                <p
                                    class="mt-4 text-center text-sm font-bold text-slate-700"
                                >
                                    Favicon
                                </p>

                                <div class="mt-4 flex gap-2">
                                    <label
                                        class="flex h-10 flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl bg-slate-950 text-xs font-bold text-white"
                                    >
                                        <Upload class="size-3.5" />

                                        Modifier

                                        <input
                                            type="file"
                                            accept="image/*,.ico"
                                            class="hidden"
                                            @change="
                                                handleFile($event, 'favicon')
                                            "
                                        />
                                    </label>

                                    <button
                                        v-if="faviconPreview"
                                        type="button"
                                        class="flex size-10 items-center justify-center rounded-xl border border-rose-200 bg-white text-rose-500"
                                        @click="removeFavicon"
                                    >
                                        <Trash2 class="size-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- COLORS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm md:p-7"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-fuchsia-50 text-fuchsia-600"
                            >
                                <Palette class="size-6" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Couleurs
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Palette graphique de l'environnement.
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="item in [
                                    {
                                        label: 'Principale',
                                        field: 'primary_color',
                                    },
                                    {
                                        label: 'Secondaire',
                                        field: 'secondary_color',
                                    },
                                    {
                                        label: 'Sidebar',
                                        field: 'sidebar_color',
                                    },
                                    {
                                        label: 'Texte sidebar',
                                        field: 'sidebar_text_color',
                                    },
                                    {
                                        label: 'Accent',
                                        field: 'accent_color',
                                    },
                                ]"
                                :key="item.field"
                                class="rounded-2xl border border-slate-200 p-4"
                            >
                                <label class="text-xs font-bold text-slate-600">
                                    {{ item.label }}
                                </label>

                                <div class="mt-3 flex items-center gap-3">
                                    <input
                                        v-model="
                                            form[
                                                item.field as keyof typeof form
                                            ] as string
                                        "
                                        type="color"
                                        class="size-11 cursor-pointer border-0 bg-transparent"
                                    />

                                    <input
                                        v-model="
                                            form[
                                                item.field as keyof typeof form
                                            ] as string
                                        "
                                        type="text"
                                        class="h-11 min-w-0 flex-1 rounded-xl border border-slate-200 px-3 font-mono text-xs font-bold uppercase outline-none focus:border-violet-300"
                                    />
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- RIGHT -->

                <aside class="space-y-5">
                    <!-- BRAND PREVIEW -->

                    <section
                        class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="p-6">
                            <p
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Aperçu en direct
                            </p>

                            <h3 class="mt-1 font-bold text-slate-900">
                                {{ form.app_name }}
                            </h3>
                        </div>

                        <div
                            class="mx-5 mb-5 overflow-hidden rounded-2xl border border-slate-200"
                        >
                            <div class="flex h-[300px]">
                                <div
                                    class="w-[38%] p-4"
                                    :style="{
                                        backgroundColor: form.sidebar_color,
                                        color: form.sidebar_text_color,
                                    }"
                                >
                                    <img
                                        v-if="logoDarkPreview || logoPreview"
                                        :src="logoDarkPreview || logoPreview!"
                                        class="max-h-10 max-w-[90px] object-contain"
                                    />

                                    <div
                                        class="mt-8 rounded-lg px-3 py-2 text-[9px] font-bold"
                                        :style="{
                                            backgroundColor: form.primary_color,
                                        }"
                                    >
                                        Dashboard
                                    </div>

                                    <div
                                        class="mt-2 px-3 py-2 text-[9px] opacity-60"
                                    >
                                        Réclamations
                                    </div>

                                    <div
                                        class="px-3 py-2 text-[9px] opacity-60"
                                    >
                                        Référentiels
                                    </div>
                                </div>

                                <div class="flex-1 bg-slate-50 p-4">
                                    <div
                                        class="h-3 w-16 rounded bg-slate-200"
                                    />

                                    <div
                                        class="mt-5 rounded-xl bg-white p-4 shadow-sm"
                                    >
                                        <div
                                            class="size-9 rounded-lg"
                                            :style="{
                                                backgroundColor:
                                                    form.primary_color,
                                            }"
                                        />

                                        <div
                                            class="mt-4 h-2 rounded bg-slate-100"
                                        />

                                        <div
                                            class="mt-2 h-2 w-2/3 rounded bg-slate-100"
                                        />

                                        <div
                                            class="mt-5 h-8 w-20 rounded-lg"
                                            :style="{
                                                backgroundColor:
                                                    form.accent_color,
                                            }"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- STATUS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3 class="font-bold text-slate-900">Statut</h3>

                        <button
                            type="button"
                            class="mt-5 flex w-full items-center justify-between rounded-2xl border p-4 text-left"
                            :class="
                                form.active
                                    ? 'border-emerald-200 bg-emerald-50'
                                    : 'border-slate-200 bg-slate-50'
                            "
                            @click="form.active = !form.active"
                        >
                            <div>
                                <p
                                    class="text-sm font-bold"
                                    :class="
                                        form.active
                                            ? 'text-emerald-700'
                                            : 'text-slate-600'
                                    "
                                >
                                    {{ form.active ? "Actif" : "Inactif" }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    Accès à cet environnement
                                </p>
                            </div>

                            <div
                                class="relative h-7 w-12 rounded-full"
                                :class="
                                    form.active
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-300'
                                "
                            >
                                <span
                                    class="absolute top-1 size-5 rounded-full bg-white shadow transition-all"
                                    :class="form.active ? 'left-6' : 'left-1'"
                                />
                            </div>
                        </button>
                    </section>

                    <!-- SAVE -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#050817] text-sm font-bold text-white transition hover:bg-violet-600 disabled:opacity-50"
                        >
                            <Save class="size-5" />

                            {{
                                form.processing
                                    ? "Enregistrement..."
                                    : "Enregistrer les modifications"
                            }}
                        </button>

                        <Link
                            href="/environments"
                            class="mt-3 flex h-13 items-center justify-center rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 hover:bg-slate-50"
                        >
                            Retour
                        </Link>
                    </section>
                </aside>
            </form>
        </div>
    </div>
</template>
