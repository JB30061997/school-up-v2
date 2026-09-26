<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CalendarDays,
    CalendarRange,
    CheckCircle2,
    Info,
    Save,
    Sparkles,
} from "lucide-vue-next";

const form = useForm({
    name: "",
    start_date: "",
    end_date: "",
    is_current: false,
    active: true,
});

const submit = () => {
    form.post("/school-years", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvelle année scolaire" />

    <div class="min-h-full bg-[#fbfcfd]">
        <!-- Header -->
        <div class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-[1300px] items-center gap-4 px-6 py-7 lg:px-8"
            >
                <Link
                    href="/school-years"
                    class="flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                >
                    <ArrowLeft class="size-4" />
                </Link>

                <div
                    class="flex size-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-lg shadow-emerald-500/20"
                >
                    <CalendarRange class="size-6" />
                </div>

                <div>
                    <div class="flex items-center gap-2">
                        <h1
                            class="text-2xl font-bold tracking-tight text-slate-950"
                        >
                            Nouvelle année scolaire
                        </h1>

                        <span
                            class="hidden items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-emerald-700 sm:inline-flex"
                        >
                            <Sparkles class="size-3" />
                            Configuration
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Configurez une nouvelle période pour l'établissement
                        actuel.
                    </p>
                </div>
            </div>
        </div>

        <form
            class="mx-auto grid max-w-[1300px] gap-6 px-6 py-8 lg:grid-cols-[1fr_340px] lg:px-8"
            @submit.prevent="submit"
        >
            <!-- Main -->
            <div
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
            >
                <div class="border-b border-slate-100 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <CalendarDays class="size-4" />
                        </div>

                        <div>
                            <h2 class="font-bold text-slate-950">
                                Informations de la période
                            </h2>
                            <p class="text-xs text-slate-400">
                                Définissez le nom et les dates de l'année.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-7 p-6">
                    <!-- Name -->
                    <div>
                        <label
                            for="name"
                            class="mb-2 block text-xs font-bold text-slate-700"
                        >
                            Nom de l'année scolaire
                            <span class="text-red-500">*</span>
                        </label>

                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Ex. 2026-2027"
                            class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-medium outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                        />

                        <p
                            v-if="form.errors.name"
                            class="mt-1.5 text-xs font-medium text-red-500"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Dates -->
                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label
                                for="start_date"
                                class="mb-2 block text-xs font-bold text-slate-700"
                            >
                                Date de début
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="start_date"
                                v-model="form.start_date"
                                type="date"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                            />

                            <p
                                v-if="form.errors.start_date"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ form.errors.start_date }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="end_date"
                                class="mb-2 block text-xs font-bold text-slate-700"
                            >
                                Date de fin
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="end_date"
                                v-model="form.end_date"
                                type="date"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm outline-none transition focus:border-emerald-400 focus:bg-white focus:ring-4 focus:ring-emerald-500/10"
                            />

                            <p
                                v-if="form.errors.end_date"
                                class="mt-1.5 text-xs text-red-500"
                            >
                                {{ form.errors.end_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Current -->
                    <label
                        class="flex cursor-pointer items-start justify-between gap-5 rounded-2xl border border-slate-200 p-5 transition hover:border-emerald-200 hover:bg-emerald-50/30"
                    >
                        <div class="flex gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                            >
                                <CheckCircle2 class="size-5" />
                            </div>

                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Définir comme année actuelle
                                </p>

                                <p
                                    class="mt-1 max-w-xl text-xs leading-5 text-slate-500"
                                >
                                    Cette année sera considérée comme la période
                                    scolaire courante de l'établissement.
                                </p>
                            </div>
                        </div>

                        <input
                            v-model="form.is_current"
                            type="checkbox"
                            class="mt-2 size-4 accent-emerald-600"
                        />
                    </label>

                    <!-- Active -->
                    <label
                        class="flex cursor-pointer items-start justify-between gap-5 rounded-2xl border border-slate-200 p-5 transition hover:border-emerald-200 hover:bg-emerald-50/30"
                    >
                        <div class="flex gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                            >
                                <CheckCircle2 class="size-5" />
                            </div>

                            <div>
                                <p class="text-sm font-bold text-slate-900">
                                    Année active
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-slate-500"
                                >
                                    Rend cette période disponible dans School
                                    Up.
                                </p>
                            </div>
                        </div>

                        <input
                            v-model="form.active"
                            type="checkbox"
                            class="mt-2 size-4 accent-emerald-600"
                        />
                    </label>
                </div>

                <!-- Footer -->
                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-5 sm:flex-row sm:justify-end"
                >
                    <Link
                        href="/school-years"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Annuler
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-6 text-sm font-semibold text-white shadow-lg transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <Save class="size-4" />

                        {{
                            form.processing
                                ? "Enregistrement..."
                                : "Créer l'année scolaire"
                        }}
                    </button>
                </div>
            </div>

            <!-- Sidebar -->
            <aside class="space-y-4">
                <div
                    class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-5"
                >
                    <div
                        class="flex size-9 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm"
                    >
                        <Info class="size-4" />
                    </div>

                    <h3 class="mt-4 text-sm font-bold text-slate-900">
                        À savoir
                    </h3>

                    <p class="mt-2 text-xs leading-5 text-slate-600">
                        Une année scolaire appartient automatiquement à
                        l'établissement actuellement sélectionné.
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p
                        class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                    >
                        Exemple
                    </p>

                    <p class="mt-3 text-lg font-bold text-slate-950">
                        2026-2027
                    </p>

                    <div class="mt-4 h-px bg-slate-100" />

                    <p class="mt-4 text-xs leading-5 text-slate-500">
                        Début : 01/09/2026
                        <br />
                        Fin : 31/08/2027
                    </p>
                </div>
            </aside>
        </form>
    </div>
</template>
