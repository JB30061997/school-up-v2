<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    Send,
    MessageSquareWarning,
    Tag,
    Users,
    UserRound,
    AlignLeft,
    Flag,
    Building2,
    Info,
    CheckCircle2,
} from "lucide-vue-next";

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },

    teams: {
        type: Array,
        default: () => [],
    },

    environment: {
        type: Object,
        default: null,
    },
});

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    ticket_category_id: "",
    support_team_id: "",
    assigned_to: "",
    subject: "",
    description: "",
    priority: "normal",
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const selectedCategory = computed(() => {
    if (!form.ticket_category_id) {
        return null;
    }

    return props.categories.find(
        (category) => String(category.id) === String(form.ticket_category_id),
    );
});

const selectedTeam = computed(() => {
    if (!form.support_team_id) {
        return null;
    }

    return props.teams.find(
        (team) => String(team.id) === String(form.support_team_id),
    );
});

const teamUsers = computed(() => {
    if (!selectedTeam.value) {
        return [];
    }

    return selectedTeam.value.users ?? selectedTeam.value.members ?? [];
});

const priorityOptions = [
    {
        value: "low",
        label: "Faible",
        description: "Peut être traité sans urgence.",
    },
    {
        value: "normal",
        label: "Normale",
        description: "Traitement selon le délai habituel.",
    },
    {
        value: "high",
        label: "Haute",
        description: "Nécessite une prise en charge rapide.",
    },
    {
        value: "urgent",
        label: "Urgente",
        description: "Impact important, traitement prioritaire.",
    },
];

const selectedPriority = computed(() => {
    return priorityOptions.find((priority) => priority.value === form.priority);
});

const resetAssignedUser = () => {
    form.assigned_to = "";
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = () => {
    form.post("/tickets", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvelle réclamation" />

    <div class="min-h-full bg-[#f8fafc]">
        <!-- ============================================================
             PAGE HEADER
        ============================================================= -->
        <div
            class="border-b border-slate-200 bg-white px-5 py-5 sm:px-7 lg:px-9"
        >
            <div
                class="mx-auto flex max-w-[1500px] flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="flex items-start gap-4">
                    <Link
                        href="/tickets"
                        class="mt-1 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>

                    <div>
                        <div class="mb-1 flex flex-wrap items-center gap-2">
                            <h1
                                class="text-2xl font-bold tracking-tight text-slate-950 sm:text-[28px]"
                            >
                                Nouvelle réclamation
                            </h1>

                            <span
                                class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-700"
                            >
                                Nouveau ticket
                            </span>
                        </div>

                        <p class="max-w-2xl text-sm leading-6 text-slate-500">
                            Signalez un incident ou une demande afin de
                            permettre à l'équipe concernée de la prendre en
                            charge.
                        </p>
                    </div>
                </div>

                <div
                    v-if="environment"
                    class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-emerald-600 shadow-sm"
                    >
                        <Building2 class="h-5 w-5" />
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400"
                        >
                            Établissement
                        </p>

                        <p class="text-sm font-bold text-slate-800">
                            {{ environment.name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================
             CONTENT
        ============================================================= -->
        <form
            class="mx-auto grid max-w-[1500px] gap-6 p-5 sm:p-7 lg:grid-cols-[minmax(0,1fr)_360px] lg:p-9"
            @submit.prevent="submit"
        >
            <!-- ========================================================
                 LEFT
            ========================================================= -->
            <div class="space-y-6">
                <!-- Informations principales -->
                <section
                    class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-4 border-b border-slate-100 px-6 py-5"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                        >
                            <MessageSquareWarning class="h-6 w-6" />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Informations de la réclamation
                            </h2>

                            <p class="mt-0.5 text-sm text-slate-500">
                                Renseignez les informations principales du
                                ticket.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-6 p-6">
                        <!-- Category -->
                        <div>
                            <label
                                for="ticket_category_id"
                                class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700"
                            >
                                <Tag class="h-4 w-4 text-emerald-500" />

                                Objet de la réclamation

                                <span class="text-rose-500">*</span>
                            </label>

                            <select
                                id="ticket_category_id"
                                v-model="form.ticket_category_id"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                :class="{
                                    'border-rose-300':
                                        form.errors.ticket_category_id,
                                }"
                            >
                                <option value="">Sélectionner un objet</option>

                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.id"
                                >
                                    {{ category.name }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.ticket_category_id"
                                class="mt-2 text-xs font-medium text-rose-600"
                            >
                                {{ form.errors.ticket_category_id }}
                            </p>
                        </div>

                        <!-- Subject -->
                        <div>
                            <label
                                for="subject"
                                class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700"
                            >
                                <AlignLeft class="h-4 w-4 text-emerald-500" />

                                Sujet

                                <span class="text-rose-500">*</span>
                            </label>

                            <input
                                id="subject"
                                v-model="form.subject"
                                type="text"
                                maxlength="255"
                                placeholder="Ex. Problème d'accès à Pronote"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                :class="{
                                    'border-rose-300': form.errors.subject,
                                }"
                            />

                            <div class="mt-2 flex items-center justify-between">
                                <p
                                    v-if="form.errors.subject"
                                    class="text-xs font-medium text-rose-600"
                                >
                                    {{ form.errors.subject }}
                                </p>

                                <span
                                    class="ml-auto text-[11px] text-slate-400"
                                >
                                    {{ form.subject.length }}/255
                                </span>
                            </div>
                        </div>

                        <!-- Description -->
                        <div>
                            <label
                                for="description"
                                class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700"
                            >
                                <MessageSquareWarning
                                    class="h-4 w-4 text-emerald-500"
                                />

                                Description

                                <span class="text-rose-500">*</span>
                            </label>

                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="8"
                                placeholder="Décrivez précisément le problème rencontré..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                :class="{
                                    'border-rose-300': form.errors.description,
                                }"
                            />

                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-xs font-medium text-rose-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Priority -->
                <section
                    class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-4 border-b border-slate-100 px-6 py-5"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-amber-600"
                        >
                            <Flag class="h-6 w-6" />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Niveau de priorité
                            </h2>

                            <p class="mt-0.5 text-sm text-slate-500">
                                Indiquez le niveau d'urgence de la réclamation.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 p-6 sm:grid-cols-2 xl:grid-cols-4">
                        <button
                            v-for="priority in priorityOptions"
                            :key="priority.value"
                            type="button"
                            class="relative rounded-2xl border p-4 text-left transition"
                            :class="
                                form.priority === priority.value
                                    ? 'border-emerald-400 bg-emerald-50 ring-2 ring-emerald-100'
                                    : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'
                            "
                            @click="form.priority = priority.value"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span
                                    class="text-sm font-bold"
                                    :class="
                                        form.priority === priority.value
                                            ? 'text-emerald-700'
                                            : 'text-slate-800'
                                    "
                                >
                                    {{ priority.label }}
                                </span>

                                <CheckCircle2
                                    v-if="form.priority === priority.value"
                                    class="h-5 w-5 text-emerald-500"
                                />
                            </div>

                            <p class="text-xs leading-5 text-slate-500">
                                {{ priority.description }}
                            </p>
                        </button>
                    </div>

                    <p
                        v-if="form.errors.priority"
                        class="px-6 pb-5 text-xs font-medium text-rose-600"
                    >
                        {{ form.errors.priority }}
                    </p>
                </section>

                <!-- Assignment -->
                <section
                    class="overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center gap-4 border-b border-slate-100 px-6 py-5"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600"
                        >
                            <Users class="h-6 w-6" />
                        </div>

                        <div>
                            <h2 class="text-base font-bold text-slate-900">
                                Affectation
                            </h2>

                            <p class="mt-0.5 text-sm text-slate-500">
                                Vous pouvez orienter directement la réclamation
                                vers une équipe.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-5 p-6 md:grid-cols-2">
                        <!-- Team -->
                        <div>
                            <label
                                for="support_team_id"
                                class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700"
                            >
                                <Users class="h-4 w-4 text-sky-500" />

                                Équipe support
                            </label>

                            <select
                                id="support_team_id"
                                v-model="form.support_team_id"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-50"
                                @change="resetAssignedUser"
                            >
                                <option value="">
                                    Affectation automatique
                                </option>

                                <option
                                    v-for="team in teams"
                                    :key="team.id"
                                    :value="team.id"
                                >
                                    {{ team.name }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.support_team_id"
                                class="mt-2 text-xs font-medium text-rose-600"
                            >
                                {{ form.errors.support_team_id }}
                            </p>
                        </div>

                        <!-- User -->
                        <div>
                            <label
                                for="assigned_to"
                                class="mb-2 flex items-center gap-2 text-sm font-semibold text-slate-700"
                            >
                                <UserRound class="h-4 w-4 text-sky-500" />

                                Affecté à
                            </label>

                            <select
                                id="assigned_to"
                                v-model="form.assigned_to"
                                :disabled="teamUsers.length === 0"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-sky-400 focus:ring-4 focus:ring-sky-50 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
                            >
                                <option value="">
                                    {{
                                        teamUsers.length
                                            ? "Sélectionner un membre"
                                            : "Sélectionnez d’abord une équipe"
                                    }}
                                </option>

                                <option
                                    v-for="user in teamUsers"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{
                                        user.name ??
                                        `${user.first_name ?? ""} ${user.last_name ?? ""}`.trim()
                                    }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.assigned_to"
                                class="mt-2 text-xs font-medium text-rose-600"
                            >
                                {{ form.errors.assigned_to }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Mobile actions -->
                <div
                    class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end lg:hidden"
                >
                    <Link
                        href="/tickets"
                        class="flex h-12 items-center justify-center rounded-xl border border-slate-200 bg-white px-6 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Annuler
                    </Link>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex h-12 items-center justify-center gap-2 rounded-xl bg-slate-950 px-7 text-sm font-bold text-white transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <Send class="h-4 w-4" />

                        {{
                            form.processing
                                ? "Création..."
                                : "Créer la réclamation"
                        }}
                    </button>
                </div>
            </div>

            <!-- ========================================================
                 RIGHT SIDEBAR
            ========================================================= -->
            <aside class="hidden lg:block">
                <div class="sticky top-6 space-y-5">
                    <!-- Summary -->
                    <div
                        class="rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div class="mb-5 flex items-center justify-between">
                            <div>
                                <p
                                    class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400"
                                >
                                    Aperçu
                                </p>

                                <h3
                                    class="mt-1 text-lg font-bold text-slate-900"
                                >
                                    Votre réclamation
                                </h3>
                            </div>

                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                            >
                                <MessageSquareWarning class="h-5 w-5" />
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Objet
                                </p>

                                <p
                                    class="mt-1.5 text-sm font-semibold text-slate-700"
                                >
                                    {{
                                        selectedCategory?.name ??
                                        "Non sélectionné"
                                    }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Priorité
                                </p>

                                <div class="mt-2 flex items-center gap-2">
                                    <span
                                        class="h-2 w-2 rounded-full bg-emerald-500"
                                    />

                                    <span
                                        class="text-sm font-semibold text-slate-700"
                                    >
                                        {{ selectedPriority?.label }}
                                    </span>
                                </div>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p
                                    class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Équipe
                                </p>

                                <p
                                    class="mt-1.5 text-sm font-semibold text-slate-700"
                                >
                                    {{
                                        selectedTeam?.name ??
                                        "Affectation automatique"
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Info -->
                    <div
                        class="rounded-[24px] border border-emerald-100 bg-emerald-50/70 p-5"
                    >
                        <div class="flex gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-600"
                            >
                                <Info class="h-4 w-4" />
                            </div>

                            <div>
                                <p class="text-sm font-bold text-emerald-900">
                                    Bon à savoir
                                </p>

                                <p
                                    class="mt-1 text-xs leading-5 text-emerald-800/70"
                                >
                                    Donnez suffisamment de détails afin de
                                    faciliter le traitement de votre réclamation
                                    par l'équipe support.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div
                        class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-emerald-600 hover:shadow-emerald-100 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Send class="h-4 w-4" />

                            {{
                                form.processing
                                    ? "Création en cours..."
                                    : "Créer la réclamation"
                            }}
                        </button>

                        <Link
                            href="/tickets"
                            class="mt-3 flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Annuler
                        </Link>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</template>
