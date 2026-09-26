<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    Clock3,
    Flag,
    Hash,
    History,
    Info,
    MessageSquare,
    Send,
    Tag,
    Ticket,
    User,
    UserRoundCheck,
    Users,
} from "lucide-vue-next";

interface UserType {
    id: number;
    name?: string | null;
    first_name?: string | null;
    last_name?: string | null;
}

interface Status {
    id: number;
    name: string;
    code?: string | null;
    is_closed?: boolean;
}

interface Team {
    id: number;
    name: string;
}

interface Category {
    id: number;
    name: string;
}

interface Reply {
    id: number;
    message: string;
    is_internal?: boolean;
    created_at?: string;
    user?: UserType | null;
}

interface HistoryItem {
    id: number;
    action?: string | null;
    description?: string | null;
    note?: string | null;
    created_at?: string;
    user?: UserType | null;
}

interface TicketType {
    id: number;
    reference?: string;
    subject?: string;
    description?: string;
    priority?: string;

    ticket_status_id?: number | null;
    support_team_id?: number | null;
    assigned_to_id?: number | null;

    created_at?: string;
    updated_at?: string;

    category?: Category | null;
    status?: Status | null;
    support_team?: Team | null;
    assigned_to?: UserType | null;
    creator?: UserType | null;

    replies?: Reply[];
    histories?: HistoryItem[];
}

const props = defineProps<{
    ticket: TicketType;
    statuses?: Status[];
    teams?: Team[];
    users?: UserType[];
}>();

/* =========================================================
   COMPUTED
========================================================= */

const ticketReference = computed(() => {
    return props.ticket.reference || `#${props.ticket.id}`;
});

const replies = computed(() => props.ticket.replies ?? []);
const histories = computed(() => props.ticket.histories ?? []);

/* =========================================================
   FORMS
========================================================= */

const statusForm = useForm({
    ticket_status_id:
        props.ticket.ticket_status_id ?? props.ticket.status?.id ?? "",
    note: "",
});

const assignmentForm = useForm({
    support_team_id:
        props.ticket.support_team_id ?? props.ticket.support_team?.id ?? "",

    assigned_to_id:
        props.ticket.assigned_to_id ?? props.ticket.assigned_to?.id ?? "",
});

const replyForm = useForm({
    message: "",
    is_internal: false,
});

/* =========================================================
   HELPERS
========================================================= */

const userName = (user?: UserType | null) => {
    if (!user) return "Non renseigné";

    const fullName = [user.first_name, user.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return fullName || user.name || "Utilisateur";
};

const initials = (user?: UserType | null) => {
    const name = userName(user);

    if (name === "Non renseigné") return "?";

    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
};

const formatDate = (date?: string) => {
    if (!date) return "—";

    const value = new Date(date);

    if (Number.isNaN(value.getTime())) {
        return date;
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(value);
};

const priorityLabel = (priority?: string) => {
    const labels: Record<string, string> = {
        low: "Faible",
        normal: "Normale",
        high: "Haute",
        urgent: "Urgente",
    };

    return labels[priority ?? ""] ?? priority ?? "Non définie";
};

const priorityClass = (priority?: string) => {
    switch (priority) {
        case "urgent":
            return "bg-red-50 text-red-700 border-red-200";

        case "high":
            return "bg-amber-50 text-amber-700 border-amber-200";

        case "normal":
            return "bg-emerald-50 text-emerald-700 border-emerald-200";

        case "low":
            return "bg-blue-50 text-blue-700 border-blue-200";

        default:
            return "bg-slate-50 text-slate-600 border-slate-200";
    }
};

/* =========================================================
   ACTIONS
========================================================= */

const updateStatus = () => {
    statusForm.post(`/tickets/${props.ticket.id}/status`, {
        preserveScroll: true,

        onSuccess: () => {
            statusForm.note = "";
        },
    });
};

const updateAssignment = () => {
    assignmentForm.post(`/tickets/${props.ticket.id}/assign`, {
        preserveScroll: true,
    });
};

const sendReply = () => {
    if (!replyForm.message.trim()) return;

    replyForm.post(`/tickets/${props.ticket.id}/reply`, {
        preserveScroll: true,

        onSuccess: () => {
            replyForm.reset();
        },
    });
};
</script>

<template>
    <Head :title="`Réclamation ${ticketReference}`" />

    <!-- =====================================================
         PAGE
    ====================================================== -->

    <div class="min-h-full bg-[#f8fafc]">
        <div class="mx-auto max-w-[1600px] p-5 lg:p-7">
            <!-- =================================================
                 HERO
            ================================================== -->

            <section
                class="relative mb-6 overflow-hidden rounded-[30px] border border-emerald-100 bg-white shadow-sm"
            >
                <div
                    class="absolute -right-20 -top-20 h-72 w-72 rounded-full bg-emerald-100/50 blur-3xl"
                ></div>

                <div
                    class="relative bg-gradient-to-r from-emerald-50/80 via-white to-cyan-50/60 p-6 lg:p-8"
                >
                    <div
                        class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between"
                    >
                        <!-- LEFT -->

                        <div class="flex items-start gap-4">
                            <Link
                                href="/tickets"
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-emerald-300 hover:text-emerald-700"
                            >
                                <ArrowLeft class="h-5 w-5" />
                            </Link>

                            <div>
                                <div
                                    class="mb-3 flex flex-wrap items-center gap-2"
                                >
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-700"
                                    >
                                        <Ticket class="h-3.5 w-3.5" />
                                        Réclamation
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-500"
                                    >
                                        <Hash class="h-3.5 w-3.5" />
                                        {{ ticketReference }}
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-white px-3 py-1.5 text-xs font-bold text-emerald-700"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full bg-emerald-500"
                                        ></span>

                                        {{
                                            ticket.status?.name || "Sans statut"
                                        }}
                                    </span>
                                </div>

                                <h1
                                    class="max-w-4xl text-2xl font-black tracking-tight text-slate-950 lg:text-3xl"
                                >
                                    {{ ticket.subject || "Réclamation" }}
                                </h1>

                                <div
                                    class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-500"
                                >
                                    <span
                                        class="inline-flex items-center gap-2"
                                    >
                                        <User class="h-4 w-4" />

                                        Créée par

                                        <strong class="text-slate-700">
                                            {{ userName(ticket.creator) }}
                                        </strong>
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-2"
                                    >
                                        <CalendarDays class="h-4 w-4" />

                                        {{ formatDate(ticket.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- PRIORITY -->

                        <div
                            :class="[
                                'min-w-[180px] rounded-2xl border px-5 py-4',
                                priorityClass(ticket.priority),
                            ]"
                        >
                            <div
                                class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider opacity-70"
                            >
                                <Flag class="h-4 w-4" />
                                Priorité
                            </div>

                            <div class="mt-1 text-lg font-black">
                                {{ priorityLabel(ticket.priority) }}
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =================================================
                 GRID
            ================================================== -->

            <div
                class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1fr)_390px]"
            >
                <!-- =============================================
                     LEFT
                ============================================== -->

                <main class="space-y-6">
                    <!-- DESCRIPTION -->

                    <section
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm lg:p-7"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700"
                            >
                                <MessageSquare class="h-5 w-5" />
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Description
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Détails de la réclamation
                                </p>
                            </div>
                        </div>

                        <div
                            class="min-h-[140px] whitespace-pre-wrap rounded-2xl border border-slate-100 bg-slate-50/80 p-5 text-[15px] leading-7 text-slate-700"
                        >
                            {{ ticket.description || "Aucune description." }}
                        </div>
                    </section>

                    <!-- INFORMATIONS -->

                    <section
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm lg:p-7"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-blue-700"
                            >
                                <Info class="h-5 w-5" />
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Informations
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Informations générales
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <!-- CATEGORY -->

                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-5"
                            >
                                <div
                                    class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    <Tag class="h-4 w-4" />
                                    Catégorie
                                </div>

                                <div class="font-bold text-slate-800">
                                    {{
                                        ticket.category?.name ||
                                        "Non renseignée"
                                    }}
                                </div>
                            </div>

                            <!-- TEAM -->

                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-5"
                            >
                                <div
                                    class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    <Users class="h-4 w-4" />
                                    Équipe support
                                </div>

                                <div class="font-bold text-slate-800">
                                    {{
                                        ticket.support_team?.name ||
                                        "Non affectée"
                                    }}
                                </div>
                            </div>

                            <!-- RESPONSABLE -->

                            <div
                                class="rounded-2xl border border-slate-100 bg-slate-50 p-5"
                            >
                                <div
                                    class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    <UserRoundCheck class="h-4 w-4" />
                                    Responsable
                                </div>

                                <div class="font-bold text-slate-800">
                                    {{ userName(ticket.assigned_to) }}
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- =============================================
                         DISCUSSION
                    ============================================== -->

                    <section
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm lg:p-7"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-violet-50 text-violet-700"
                                >
                                    <MessageSquare class="h-5 w-5" />
                                </div>

                                <div>
                                    <h2
                                        class="text-lg font-black text-slate-900"
                                    >
                                        Discussion
                                    </h2>

                                    <p class="text-sm text-slate-500">
                                        {{ replies.length }}
                                        réponse(s)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- REPLIES -->

                        <div v-if="replies.length" class="mb-7 space-y-4">
                            <article
                                v-for="reply in replies"
                                :key="reply.id"
                                :class="[
                                    'rounded-2xl border p-5',
                                    reply.is_internal
                                        ? 'border-amber-200 bg-amber-50'
                                        : 'border-slate-200 bg-white',
                                ]"
                            >
                                <div
                                    class="mb-4 flex items-start justify-between gap-4"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-950 text-xs font-black text-white"
                                        >
                                            {{ initials(reply.user) }}
                                        </div>

                                        <div>
                                            <div
                                                class="font-bold text-slate-900"
                                            >
                                                {{ userName(reply.user) }}
                                            </div>

                                            <div class="text-xs text-slate-400">
                                                {{
                                                    formatDate(reply.created_at)
                                                }}
                                            </div>
                                        </div>
                                    </div>

                                    <span
                                        v-if="reply.is_internal"
                                        class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700"
                                    >
                                        Note interne
                                    </span>
                                </div>

                                <div
                                    class="whitespace-pre-wrap text-sm leading-7 text-slate-700"
                                >
                                    {{ reply.message }}
                                </div>
                            </article>
                        </div>

                        <!-- EMPTY -->

                        <div
                            v-else
                            class="mb-6 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center"
                        >
                            <MessageSquare
                                class="mx-auto mb-3 h-8 w-8 text-slate-300"
                            />

                            <div class="font-bold text-slate-600">
                                Aucune réponse
                            </div>

                            <p class="mt-1 text-sm text-slate-400">
                                Aucune discussion pour le moment.
                            </p>
                        </div>

                        <!-- REPLY FORM -->

                        <form
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                            @submit.prevent="sendReply"
                        >
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Ajouter une réponse
                            </label>

                            <textarea
                                v-model="replyForm.message"
                                rows="5"
                                placeholder="Écrivez votre réponse..."
                                class="w-full resize-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            ></textarea>

                            <p
                                v-if="replyForm.errors.message"
                                class="mt-2 text-sm font-semibold text-red-600"
                            >
                                {{ replyForm.errors.message }}
                            </p>

                            <div
                                class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <label
                                    class="flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-600"
                                >
                                    <input
                                        v-model="replyForm.is_internal"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300"
                                    />

                                    Note interne
                                </label>

                                <button
                                    type="submit"
                                    :disabled="
                                        replyForm.processing ||
                                        !replyForm.message.trim()
                                    "
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-950 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <Send class="h-4 w-4" />

                                    {{
                                        replyForm.processing
                                            ? "Envoi..."
                                            : "Envoyer"
                                    }}
                                </button>
                            </div>
                        </form>
                    </section>

                    <!-- =============================================
                         HISTORY
                    ============================================== -->

                    <section
                        class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm lg:p-7"
                    >
                        <div class="mb-6 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-slate-100 text-slate-700"
                            >
                                <History class="h-5 w-5" />
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-900">
                                    Historique
                                </h2>

                                <p class="text-sm text-slate-500">
                                    Suivi des modifications
                                </p>
                            </div>
                        </div>

                        <div v-if="histories.length" class="space-y-0">
                            <div
                                v-for="history in histories"
                                :key="history.id"
                                class="relative ml-2 border-l-2 border-slate-100 pb-6 pl-7 last:pb-0"
                            >
                                <span
                                    class="absolute -left-[7px] top-1 h-3 w-3 rounded-full bg-emerald-500 ring-4 ring-white"
                                ></span>

                                <div class="font-bold text-slate-800">
                                    {{
                                        history.description ||
                                        history.action ||
                                        "Modification de la réclamation"
                                    }}
                                </div>

                                <p
                                    v-if="history.note"
                                    class="mt-1 text-sm text-slate-600"
                                >
                                    {{ history.note }}
                                </p>

                                <div class="mt-2 text-xs text-slate-400">
                                    {{ userName(history.user) }}

                                    ·

                                    {{ formatDate(history.created_at) }}
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-7 text-center text-sm text-slate-500"
                        >
                            Aucun historique disponible.
                        </div>
                    </section>
                </main>

                <!-- =============================================
                     RIGHT SIDEBAR
                ============================================== -->

                <aside class="space-y-5">
                    <!-- STATUS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700"
                            >
                                <CheckCircle2 class="h-5 w-5" />
                            </div>

                            <div>
                                <h3 class="font-black text-slate-900">
                                    Statut
                                </h3>

                                <p class="text-xs text-slate-400">
                                    Modifier l'état
                                </p>
                            </div>
                        </div>

                        <form class="space-y-4" @submit.prevent="updateStatus">
                            <select
                                v-model="statusForm.ticket_status_id"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            >
                                <option value="">Sélectionner</option>

                                <option
                                    v-for="status in statuses ?? []"
                                    :key="status.id"
                                    :value="status.id"
                                >
                                    {{ status.name }}
                                </option>
                            </select>

                            <textarea
                                v-model="statusForm.note"
                                rows="3"
                                placeholder="Note facultative..."
                                class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            ></textarea>

                            <button
                                type="submit"
                                :disabled="statusForm.processing"
                                class="w-full rounded-xl bg-emerald-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-emerald-700 disabled:opacity-50"
                            >
                                {{
                                    statusForm.processing
                                        ? "Mise à jour..."
                                        : "Mettre à jour"
                                }}
                            </button>
                        </form>
                    </section>

                    <!-- =============================================
                         ASSIGNMENT
                    ============================================== -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-700"
                            >
                                <Users class="h-5 w-5" />
                            </div>

                            <div>
                                <h3 class="font-black text-slate-900">
                                    Affectation
                                </h3>

                                <p class="text-xs text-slate-400">
                                    Équipe et responsable
                                </p>
                            </div>
                        </div>

                        <form
                            class="space-y-4"
                            @submit.prevent="updateAssignment"
                        >
                            <!-- TEAM -->

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Équipe support
                                </label>

                                <select
                                    v-model="assignmentForm.support_team_id"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                                >
                                    <option value="">Aucune équipe</option>

                                    <option
                                        v-for="team in teams ?? []"
                                        :key="team.id"
                                        :value="team.id"
                                    >
                                        {{ team.name }}
                                    </option>
                                </select>
                            </div>

                            <!-- USER -->

                            <div>
                                <label
                                    class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400"
                                >
                                    Responsable
                                </label>

                                <select
                                    v-model="assignmentForm.assigned_to_id"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-blue-400 focus:ring-4 focus:ring-blue-100"
                                >
                                    <option value="">Non affecté</option>

                                    <option
                                        v-for="user in users ?? []"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ userName(user) }}
                                    </option>
                                </select>
                            </div>

                            <button
                                type="submit"
                                :disabled="assignmentForm.processing"
                                class="w-full rounded-xl bg-slate-950 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700 disabled:opacity-50"
                            >
                                {{
                                    assignmentForm.processing
                                        ? "Enregistrement..."
                                        : "Enregistrer"
                                }}
                            </button>
                        </form>
                    </section>

                    <!-- =============================================
                         CREATOR
                    ============================================== -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="mb-4 text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Demandeur
                        </div>

                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sm font-black text-white"
                            >
                                {{ initials(ticket.creator) }}
                            </div>

                            <div class="min-w-0">
                                <div class="truncate font-bold text-slate-900">
                                    {{ userName(ticket.creator) }}
                                </div>

                                <div class="mt-1 text-xs text-slate-400">
                                    Créateur de la réclamation
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- =============================================
                         TIMELINE
                    ============================================== -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div
                            class="mb-5 text-xs font-bold uppercase tracking-wider text-slate-400"
                        >
                            Chronologie
                        </div>

                        <div class="space-y-5">
                            <!-- CREATED -->

                            <div class="flex gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500"
                                >
                                    <CalendarDays class="h-4 w-4" />
                                </div>

                                <div>
                                    <div class="text-xs text-slate-400">
                                        Création
                                    </div>

                                    <div
                                        class="mt-1 text-sm font-bold text-slate-700"
                                    >
                                        {{ formatDate(ticket.created_at) }}
                                    </div>
                                </div>
                            </div>

                            <!-- UPDATED -->

                            <div class="flex gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-50 text-slate-500"
                                >
                                    <Clock3 class="h-4 w-4" />
                                </div>

                                <div>
                                    <div class="text-xs text-slate-400">
                                        Dernière modification
                                    </div>

                                    <div
                                        class="mt-1 text-sm font-bold text-slate-700"
                                    >
                                        {{ formatDate(ticket.updated_at) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</template>
