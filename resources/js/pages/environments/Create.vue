<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

import {
    ArrowLeft,
    Building2,
    Check,
    Globe2,
    Image,
    Info,
    Monitor,
    Palette,
    Save,
    Upload,
} from "lucide-vue-next";

type Branding = {
    primary_color: string;
    secondary_color: string;
    sidebar_color: string;
    sidebar_text_color: string;
    accent_color: string;
};

const props = defineProps<{
    defaultBranding: Branding;
}>();

const form = useForm({
    name: "",
    code: "",
    app_name: "School Up",
    url: "",
    current_exercise: "",
    active: true,

    primary_color: props.defaultBranding?.primary_color ?? "#8B1E2D",

    secondary_color: props.defaultBranding?.secondary_color ?? "#641520",

    sidebar_color: props.defaultBranding?.sidebar_color ?? "#111827",

    sidebar_text_color: props.defaultBranding?.sidebar_text_color ?? "#FFFFFF",

    accent_color: props.defaultBranding?.accent_color ?? "#D4AF37",

    logo: null as File | null,
    logo_dark: null as File | null,
    favicon: null as File | null,
});

const logoPreview = ref<string | null>(null);
const logoDarkPreview = ref<string | null>(null);
const faviconPreview = ref<string | null>(null);

const environmentInitials = computed(() => {
    const value = form.name.trim() || "School Up";

    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
});

const handleFile = (event: Event, field: "logo" | "logo_dark" | "favicon") => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    form[field] = file;

    const preview = file ? URL.createObjectURL(file) : null;

    if (field === "logo") {
        logoPreview.value = preview;
    }

    if (field === "logo_dark") {
        logoDarkPreview.value = preview;
    }

    if (field === "favicon") {
        faviconPreview.value = preview;
    }
};

const submit = () => {
    form.post("/environments", {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvel environnement" />

    <div class="min-h-full bg-[#fafbfc]">
        <div class="mx-auto max-w-[1600px] space-y-6 p-5 md:p-7 lg:p-8">
            <!-- HEADER -->

            <section
                class="relative overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="pointer-events-none absolute -right-24 -top-32 size-[450px] rounded-full bg-violet-100/70 blur-[100px]"
                />

                <div class="relative flex items-start gap-5 p-7 md:p-9">
                    <Link
                        href="/environments"
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-violet-200 hover:text-violet-600"
                    >
                        <ArrowLeft class="size-5" />
                    </Link>

                    <div
                        class="flex size-16 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-violet-600 to-indigo-600 text-white shadow-lg shadow-violet-500/20"
                    >
                        <Building2 class="size-8" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1
                                class="text-3xl font-bold tracking-tight text-slate-950 md:text-[36px]"
                            >
                                Nouvel environnement
                            </h1>

                            <span
                                class="rounded-full bg-violet-50 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-violet-700"
                            >
                                Établissement
                            </span>
                        </div>

                        <p
                            class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                        >
                            Configurez un nouvel établissement, son identité et
                            son apparence dans School Up.
                        </p>
                    </div>
                </div>
            </section>

            <form
                class="grid gap-6 xl:grid-cols-[1.45fr_0.8fr]"
                @submit.prevent="submit"
            >
                <!-- LEFT -->

                <div class="space-y-6">
                    <!-- INFORMATIONS -->

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
                                    Identité principale de l'établissement.
                                </p>
                            </div>
                        </div>

                        <div class="mt-7 grid gap-5 md:grid-cols-2">
                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Nom de l'établissement
                                    <span class="text-rose-500"> * </span>
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Ex. Al Jabr Oasis"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm font-medium outline-none transition focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-50"
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
                                    <span class="text-rose-500"> * </span>
                                </label>

                                <input
                                    v-model="form.code"
                                    type="text"
                                    placeholder="AJ-OASIS"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 font-mono text-sm font-bold uppercase outline-none transition focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-50"
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
                                    Nom de l'application
                                </label>

                                <input
                                    v-model="form.app_name"
                                    type="text"
                                    placeholder="School Up"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm font-medium outline-none transition focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                />

                                <p
                                    v-if="form.errors.app_name"
                                    class="mt-2 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.app_name }}
                                </p>
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
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm font-medium outline-none transition focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                />

                                <p
                                    v-if="form.errors.current_exercise"
                                    class="mt-2 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.current_exercise }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                URL de l'établissement
                            </label>

                            <div class="relative">
                                <Globe2
                                    class="absolute left-4 top-1/2 size-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="form.url"
                                    type="url"
                                    placeholder="https://..."
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 pl-12 pr-4 text-sm font-medium outline-none transition focus:border-violet-300 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                />
                            </div>

                            <p
                                v-if="form.errors.url"
                                class="mt-2 text-xs font-semibold text-rose-500"
                            >
                                {{ form.errors.url }}
                            </p>
                        </div>
                    </section>

                    <!-- LOGOS -->

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
                                    Logos & favicon
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Personnalisez l'identité graphique de
                                    l'établissement.
                                </p>
                            </div>
                        </div>

                        <div class="mt-7 grid gap-5 md:grid-cols-3">
                            <!-- LOGO -->

                            <label
                                class="group cursor-pointer rounded-[22px] border border-dashed border-slate-300 bg-slate-50/60 p-5 transition hover:border-violet-300 hover:bg-violet-50/40"
                            >
                                <input
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                    class="hidden"
                                    @change="handleFile($event, 'logo')"
                                />

                                <div
                                    class="flex h-24 items-center justify-center rounded-2xl bg-white"
                                >
                                    <img
                                        v-if="logoPreview"
                                        :src="logoPreview"
                                        class="max-h-16 max-w-[140px] object-contain"
                                    />

                                    <Upload
                                        v-else
                                        class="size-7 text-slate-300"
                                    />
                                </div>

                                <p
                                    class="mt-4 text-center text-sm font-bold text-slate-700"
                                >
                                    Logo principal
                                </p>

                                <p
                                    class="mt-1 text-center text-[11px] text-slate-400"
                                >
                                    PNG, JPG, WEBP ou SVG
                                </p>
                            </label>

                            <!-- DARK -->

                            <label
                                class="group cursor-pointer rounded-[22px] border border-dashed border-slate-300 bg-slate-950 p-5 transition hover:border-violet-400"
                            >
                                <input
                                    type="file"
                                    accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                    class="hidden"
                                    @change="handleFile($event, 'logo_dark')"
                                />

                                <div
                                    class="flex h-24 items-center justify-center rounded-2xl bg-slate-900"
                                >
                                    <img
                                        v-if="logoDarkPreview"
                                        :src="logoDarkPreview"
                                        class="max-h-16 max-w-[140px] object-contain"
                                    />

                                    <Upload
                                        v-else
                                        class="size-7 text-slate-500"
                                    />
                                </div>

                                <p
                                    class="mt-4 text-center text-sm font-bold text-white"
                                >
                                    Logo sombre
                                </p>

                                <p
                                    class="mt-1 text-center text-[11px] text-slate-500"
                                >
                                    Pour fonds sombres
                                </p>
                            </label>

                            <!-- FAVICON -->

                            <label
                                class="group cursor-pointer rounded-[22px] border border-dashed border-slate-300 bg-slate-50/60 p-5 transition hover:border-violet-300"
                            >
                                <input
                                    type="file"
                                    accept="image/*,.ico"
                                    class="hidden"
                                    @change="handleFile($event, 'favicon')"
                                />

                                <div
                                    class="flex h-24 items-center justify-center rounded-2xl bg-white"
                                >
                                    <img
                                        v-if="faviconPreview"
                                        :src="faviconPreview"
                                        class="size-12 object-contain"
                                    />

                                    <Monitor
                                        v-else
                                        class="size-7 text-slate-300"
                                    />
                                </div>

                                <p
                                    class="mt-4 text-center text-sm font-bold text-slate-700"
                                >
                                    Favicon
                                </p>

                                <p
                                    class="mt-1 text-center text-[11px] text-slate-400"
                                >
                                    Icône navigateur
                                </p>
                            </label>
                        </div>

                        <p
                            v-if="form.errors.logo"
                            class="mt-3 text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.logo }}
                        </p>

                        <p
                            v-if="form.errors.logo_dark"
                            class="mt-2 text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.logo_dark }}
                        </p>

                        <p
                            v-if="form.errors.favicon"
                            class="mt-2 text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.favicon }}
                        </p>
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
                                    Identité visuelle
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Définissez les couleurs propres à cet
                                    environnement.
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                        >
                            <div
                                v-for="item in [
                                    {
                                        label: 'Couleur principale',
                                        field: 'primary_color',
                                    },
                                    {
                                        label: 'Couleur secondaire',
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
                                        class="size-11 cursor-pointer rounded-xl border-0 bg-transparent p-0"
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
                    <!-- LIVE PREVIEW -->

                    <section
                        class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm"
                    >
                        <div class="p-6">
                            <p
                                class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400"
                            >
                                Aperçu
                            </p>

                            <h3 class="mt-1 text-lg font-bold text-slate-900">
                                Interface School Up
                            </h3>
                        </div>

                        <div
                            class="mx-5 mb-5 overflow-hidden rounded-[22px] border border-slate-200"
                        >
                            <div class="flex min-h-[330px]">
                                <div
                                    class="w-[35%] p-4"
                                    :style="{
                                        backgroundColor: form.sidebar_color,
                                        color: form.sidebar_text_color,
                                    }"
                                >
                                    <div
                                        class="flex size-11 items-center justify-center overflow-hidden rounded-xl bg-white/10"
                                    >
                                        <img
                                            v-if="
                                                logoDarkPreview || logoPreview
                                            "
                                            :src="
                                                logoDarkPreview || logoPreview!
                                            "
                                            class="max-h-8 max-w-8 object-contain"
                                        />

                                        <span v-else class="text-xs font-black">
                                            {{ environmentInitials }}
                                        </span>
                                    </div>

                                    <p class="mt-3 truncate text-xs font-bold">
                                        {{ form.app_name || "School Up" }}
                                    </p>

                                    <div class="mt-7 space-y-2">
                                        <div
                                            class="rounded-lg px-3 py-2 text-[9px] font-bold"
                                            :style="{
                                                backgroundColor:
                                                    form.primary_color,
                                            }"
                                        >
                                            Tableau de bord
                                        </div>

                                        <div
                                            class="px-3 py-2 text-[9px] opacity-60"
                                        >
                                            Réclamations
                                        </div>

                                        <div
                                            class="px-3 py-2 text-[9px] opacity-60"
                                        >
                                            Utilisateurs
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 bg-slate-50 p-5">
                                    <div
                                        class="h-3 w-20 rounded-full bg-slate-200"
                                    />

                                    <div
                                        class="mt-5 rounded-xl bg-white p-4 shadow-sm"
                                    >
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="size-9 rounded-lg"
                                                :style="{
                                                    backgroundColor:
                                                        form.primary_color,
                                                }"
                                            />

                                            <div>
                                                <div
                                                    class="h-2 w-20 rounded bg-slate-200"
                                                />

                                                <div
                                                    class="mt-2 h-2 w-12 rounded bg-slate-100"
                                                />
                                            </div>
                                        </div>

                                        <div
                                            class="mt-5 h-2 rounded bg-slate-100"
                                        />

                                        <div
                                            class="mt-2 h-2 w-3/4 rounded bg-slate-100"
                                        />

                                        <button
                                            type="button"
                                            class="mt-5 rounded-lg px-4 py-2 text-[9px] font-bold text-white"
                                            :style="{
                                                backgroundColor:
                                                    form.accent_color,
                                            }"
                                        >
                                            Action
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- STATUS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3 class="text-base font-bold text-slate-900">
                            Statut
                        </h3>

                        <button
                            type="button"
                            class="mt-5 flex w-full items-center justify-between rounded-2xl border p-4 text-left transition"
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
                                    {{
                                        form.active
                                            ? "Environnement actif"
                                            : "Environnement inactif"
                                    }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{
                                        form.active
                                            ? "Accessible aux utilisateurs."
                                            : "Accès désactivé."
                                    }}
                                </p>
                            </div>

                            <div
                                class="relative h-7 w-12 rounded-full transition"
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

                    <!-- INFO -->

                    <section
                        class="rounded-[26px] border border-violet-100 bg-violet-50/60 p-6"
                    >
                        <div class="flex gap-3">
                            <Info
                                class="mt-0.5 size-5 shrink-0 text-violet-600"
                            />

                            <p class="text-xs leading-6 text-violet-900/70">
                                Chaque environnement peut avoir ses propres
                                utilisateurs, années scolaires, cycles et
                                identité visuelle.
                            </p>
                        </div>
                    </section>

                    <!-- SAVE -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#050817] text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition hover:bg-violet-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save class="size-5" />

                            {{
                                form.processing
                                    ? "Création..."
                                    : "Créer l'environnement"
                            }}
                        </button>

                        <Link
                            href="/environments"
                            class="mt-3 flex h-13 items-center justify-center rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 transition hover:bg-slate-50"
                        >
                            Annuler
                        </Link>
                    </section>
                </aside>
            </form>
        </div>
    </div>
</template>
