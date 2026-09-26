<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CheckCircle2,
    ChevronRight,
    Hash,
    MessageSquareText,
    Plus,
    Save,
    Tag,
} from "lucide-vue-next";

import AppLayout from "@/layouts/AppLayout.vue";

interface Environment {
    id: number;
    name: string;
    code?: string | null;
}

const props = defineProps<{
    environments?: Environment[];
}>();

const page = usePage<any>();

const currentEnvironment = computed(() => {
    return page.props.currentEnvironment ?? null;
});

const form = useForm({
    environment_id:
        currentEnvironment.value?.id ?? props.environments?.[0]?.id ?? "",
    name: "",
    code: "",
    active: true,
});

const generateCode = () => {
    if (!form.name || form.code) return;

    form.code = form.name
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toUpperCase()
        .replace(/[^A-Z0-9]+/g, "_")
        .replace(/^_+|_+$/g, "");
};

const submit = () => {
    form.post("/ticket-categories", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvel objet de réclamation" />

    <AppLayout>
        <div class="min-h-screen bg-[#f7f9fc]">
            <div class="mx-auto max-w-5xl px-5 py-7 lg:px-8">
                <!-- Breadcrumb -->
                <div
                    class="mb-6 flex items-center gap-2 text-sm text-slate-400"
                >
                    <Link
                        href="/ticket-categories"
                        class="transition hover:text-emerald-600"
                    >
                        Objets des réclamations
                    </Link>

                    <ChevronRight :size="15" />

                    <span class="font-semibold text-slate-700">
                        Nouvel objet
                    </span>
                </div>

                <!-- Header -->
                <div
                    class="relative mb-7 overflow-hidden rounded-[28px] border border-emerald-100 bg-white p-7 shadow-sm"
                >
                    <div
                        class="absolute right-0 top-0 h-44 w-44 rounded-full bg-emerald-50 blur-3xl"
                    />

                    <div
                        class="relative flex items-center justify-between gap-6"
                    >
                        <div class="flex items-center gap-5">
                            <div
                                class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-100"
                            >
                                <Plus :size="29" />
                            </div>

                            <div>
                                <div class="mb-2 flex items-center gap-3">
                                    <h1
                                        class="text-2xl font-bold tracking-tight text-slate-900 lg:text-3xl"
                                    >
                                        Nouvel objet de réclamation
                                    </h1>

                                    <span
                                        class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-700"
                                    >
                                        Référentiel
                                    </span>
                                </div>

                                <p
                                    class="max-w-2xl text-sm leading-6 text-slate-500"
                                >
                                    Créez un nouvel objet permettant de
                                    catégoriser les réclamations dans School Up.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form
                    @submit.prevent="submit"
                    class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm"
                >
                    <!-- Form header -->
                    <div
                        class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-7 py-5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                            >
                                <MessageSquareText :size="20" />
                            </div>

                            <div>
                                <h2 class="font-bold text-slate-900">
                                    Informations de l'objet
                                </h2>

                                <p class="text-xs text-slate-400">
                                    Renseignez les informations principales.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-7 p-7">
                        <!-- Environment -->
                        <div>
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Environnement
                            </label>

                            <select
                                v-model="form.environment_id"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                            >
                                <option value="" disabled>
                                    Sélectionner un environnement
                                </option>

                                <option
                                    v-for="environment in environments"
                                    :key="environment.id"
                                    :value="environment.id"
                                >
                                    {{ environment.name }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.environment_id"
                                class="mt-2 text-xs font-semibold text-red-500"
                            >
                                {{ form.errors.environment_id }}
                            </p>
                        </div>

                        <!-- Name + Code -->
                        <div class="grid gap-6 md:grid-cols-2">
                            <!-- Name -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Titre de l'objet
                                </label>

                                <div class="relative">
                                    <Tag
                                        :size="18"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Ex : Transport"
                                        @blur="generateCode"
                                        class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.name"
                                    class="mt-2 text-xs font-semibold text-red-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- Code -->
                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Code
                                </label>

                                <div class="relative">
                                    <Hash
                                        :size="18"
                                        class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"
                                    />

                                    <input
                                        v-model="form.code"
                                        type="text"
                                        placeholder="Ex : TRANSPORT"
                                        class="h-12 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm uppercase text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                    />
                                </div>

                                <p
                                    v-if="form.errors.code"
                                    class="mt-2 text-xs font-semibold text-red-500"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>
                        </div>

                        <!-- Active -->
                        <div
                            class="flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-5"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600"
                                >
                                    <CheckCircle2 :size="21" />
                                </div>

                                <div>
                                    <p class="font-bold text-slate-800">
                                        Objet actif
                                    </p>

                                    <p class="mt-1 text-xs text-slate-400">
                                        L'objet sera disponible pour les
                                        nouvelles réclamations.
                                    </p>
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="form.active = !form.active"
                                class="relative h-7 w-12 rounded-full transition"
                                :class="
                                    form.active
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-300'
                                "
                            >
                                <span
                                    class="absolute top-1 h-5 w-5 rounded-full bg-white shadow transition-all"
                                    :class="form.active ? 'left-6' : 'left-1'"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50/70 px-7 py-5"
                    >
                        <Link
                            href="/ticket-categories"
                            class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-100"
                        >
                            <ArrowLeft :size="17" />
                            Annuler
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-11 items-center gap-2 rounded-xl bg-slate-950 px-6 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save :size="17" />

                            {{
                                form.processing
                                    ? "Enregistrement..."
                                    : "Créer l’objet"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
