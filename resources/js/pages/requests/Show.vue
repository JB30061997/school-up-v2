<script setup lang="ts">
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import {
    AlertTriangle,
    ArrowLeft,
    CalendarDays,
    CheckCircle2,
    CircleDot,
    Clock3,
    FileText,
    History,
    MessageSquare,
    Send,
    ShieldCheck,
    UserRound,
    Users,
    XCircle,
} from "@lucide/vue";
import { computed } from "vue";

type User = {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    email?: string | null;
};

type RequestType = {
    id: number;
    name: string;
    code: string;
    description?: string | null;
    sla_minutes?: number | null;
    requires_approval?: boolean | number;
};

type RequestComment = {
    id: number;
    internal_request_id: number;
    user_id: number;
    message: string;
    is_internal?: boolean | number;
    created_at: string;
    updated_at?: string;
    user?: User | null;
};

type RequestHistory = {
    id: number;
    internal_request_id: number;
    user_id: number;
    action: string;
    from_status?: string | null;
    to_status?: string | null;
    note?: string | null;
    metadata?: string | Record<string, unknown> | null;
    created_at: string;
    user?: User | null;
};

type InternalRequest = {
    id: number;
    reference: string;
    environment_id: number;
    request_type_id: number;
    created_by: number;
    assigned_to?: number | null;
    approved_by?: number | null;
    subject: string;
    description: string;
    status: string;
    priority: string;
    submitted_at?: string | null;
    assigned_at?: string | null;
    started_at?: string | null;
    due_at?: string | null;
    approved_at?: string | null;
    rejected_at?: string | null;
    completed_at?: string | null;
    created_at: string;
    updated_at: string;
    type?: RequestType | null;
    creator?: User | null;
    assignedTo?: User | null;
    approvedBy?: User | null;
    comments?: RequestComment[];
    histories?: RequestHistory[];
};

const props = withDefaults(
    defineProps<{
        request?: InternalRequest;
        internalRequest?: InternalRequest;
        users?: User[];
        statuses?: Record<string, string>;
        priorities?: Record<string, string>;
    }>(),
    {
        request: undefined,
        internalRequest: undefined,
        users: () => [],
        statuses: () => ({}),
        priorities: () => ({}),
    },
);

const currentRequest = computed<InternalRequest | null>(() => {
    return props.request ?? props.internalRequest ?? null;
});

const users = computed(() => props.users ?? []);

const assignForm = useForm({
    assigned_to:
        currentRequest.value?.assignedTo?.id ??
        currentRequest.value?.assigned_to ??
        "",
});

const commentForm = useForm({
    message: "",
});

const assignedUser = computed(() => {
    return currentRequest.value?.assignedTo ?? null;
});

const approvedUser = computed(() => {
    return currentRequest.value?.approvedBy ?? null;
});

const requiresApproval = computed(() => {
    return Boolean(currentRequest.value?.type?.requires_approval);
});

const userName = (user?: User | null): string => {
    if (!user) {
        return "Non renseigné";
    }

    const fullName = [user.first_name, user.last_name]
        .filter(Boolean)
        .join(" ")
        .trim();

    return fullName || user.name || "Utilisateur";
};

const initials = (user?: User | null): string => {
    if (!user) {
        return "—";
    }

    return userName(user)
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
};

const formatDateTime = (value?: string | null): string => {
    if (!value) {
        return "—";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "—";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
};

const statusLabel = (value?: string | null): string => {
    if (!value) {
        return "—";
    }

    if (props.statuses?.[value]) {
        return props.statuses[value];
    }

    const labels: Record<string, string> = {
        DRAFT: "Brouillon",
        SUBMITTED: "Soumise",
        ASSIGNED: "Affectée",
        IN_PROGRESS: "En cours",
        APPROVED: "Approuvée",
        REJECTED: "Rejetée",
        COMPLETED: "Terminée",
        CANCELLED: "Annulée",
    };

    return labels[value] ?? value;
};

const statusClass = (value?: string | null): string => {
    switch (value) {
        case "DRAFT":
            return "border-slate-200 bg-slate-50 text-slate-600";
        case "SUBMITTED":
            return "border-amber-200 bg-amber-50 text-amber-700";
        case "ASSIGNED":
            return "border-sky-200 bg-sky-50 text-sky-700";
        case "IN_PROGRESS":
            return "border-blue-200 bg-blue-50 text-blue-700";
        case "APPROVED":
            return "border-emerald-200 bg-emerald-50 text-emerald-700";
        case "COMPLETED":
            return "border-green-200 bg-green-50 text-green-700";
        case "REJECTED":
            return "border-red-200 bg-red-50 text-red-700";
        case "CANCELLED":
            return "border-slate-200 bg-slate-100 text-slate-600";
        default:
            return "border-slate-200 bg-slate-50 text-slate-600";
    }
};

const priorityLabel = (value?: string | null): string => {
    if (!value) {
        return "—";
    }

    if (props.priorities?.[value]) {
        return props.priorities[value];
    }

    const labels: Record<string, string> = {
        low: "Faible",
        normal: "Normale",
        high: "Haute",
        urgent: "Urgente",
    };

    return labels[value.toLowerCase()] ?? value;
};

const priorityClass = (value?: string | null): string => {
    switch (value?.toLowerCase()) {
        case "low":
            return "bg-slate-100 text-slate-600";
        case "normal":
            return "bg-emerald-50 text-emerald-700";
        case "high":
            return "bg-orange-50 text-orange-700";
        case "urgent":
            return "bg-red-50 text-red-700";
        default:
            return "bg-slate-100 text-slate-600";
    }
};

const historyActionLabel = (history: RequestHistory): string => {
    if (history.note) {
        return history.note;
    }

    const labels: Record<string, string> = {
        created: "Demande créée.",
        submitted: "Demande soumise.",
        assigned: "Affectation modifiée.",
        started: "Traitement démarré.",
        approved: "Demande approuvée.",
        rejected: "Demande rejetée.",
        completed: "Demande terminée.",
        cancelled: "Demande annulée.",
        comment_added: "Commentaire ajouté.",
    };

    return labels[history.action] ?? "Mise à jour de la demande.";
};

const assign = () => {
    const request = currentRequest.value;

    if (!request || !assignForm.assigned_to) {
        return;
    }

    assignForm.post(`/requests/${request.id}/assign`, {
        preserveScroll: true,
    });
};

const changeStatus = (status: string) => {
    const request = currentRequest.value;

    if (!request) {
        return;
    }

    router.post(
        `/requests/${request.id}/status`,
        { status },
        { preserveScroll: true },
    );
};

const addComment = () => {
    const request = currentRequest.value;

    if (!request || !commentForm.message.trim()) {
        return;
    }

    commentForm.post(`/requests/${request.id}/comment`, {
        preserveScroll: true,
        onSuccess: () => {
            commentForm.reset();
        },
    });
};
</script>

<template>
    <Head
        :title="
            currentRequest
                ? `${currentRequest.reference} - ${currentRequest.subject}`
                : 'Demande'
        "
    />

    <!-- REQUEST FOUND -->
    <div v-if="currentRequest" class="min-h-full bg-slate-50/60">
        <!-- HEADER -->
        <div class="border-b border-slate-200 bg-white">
            <div
                class="flex flex-col gap-5 px-6 py-6 xl:flex-row xl:items-center xl:justify-between"
            >
                <div class="flex min-w-0 items-center gap-4">
                    <Link
                        href="/requests"
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="text-xs font-black uppercase tracking-wider text-emerald-600"
                            >
                                {{ currentRequest.reference }}
                            </span>

                            <span
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-[10px] font-black"
                                :class="statusClass(currentRequest.status)"
                            >
                                {{ statusLabel(currentRequest.status) }}
                            </span>

                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-black"
                                :class="priorityClass(currentRequest.priority)"
                            >
                                {{ priorityLabel(currentRequest.priority) }}
                            </span>
                        </div>

                        <h1
                            class="mt-2 truncate text-2xl font-black tracking-tight text-slate-950"
                        >
                            {{ currentRequest.subject }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Créée le
                            {{ formatDateTime(currentRequest.created_at) }}
                            par
                            <span class="font-semibold text-slate-700">
                                {{ userName(currentRequest.creator) }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        v-if="
                            currentRequest.status === 'SUBMITTED' ||
                            currentRequest.status === 'ASSIGNED'
                        "
                        type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-blue-600 px-4 text-xs font-black text-white transition hover:bg-blue-700"
                        @click="changeStatus('IN_PROGRESS')"
                    >
                        <Clock3 class="size-4" />
                        Démarrer
                    </button>

                    <button
                        v-if="
                            currentRequest.status === 'IN_PROGRESS' &&
                            requiresApproval
                        "
                        type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-xs font-black text-emerald-700 transition hover:bg-emerald-100"
                        @click="changeStatus('APPROVED')"
                    >
                        <ShieldCheck class="size-4" />
                        Approuver
                    </button>

                    <button
                        v-if="
                            currentRequest.status === 'IN_PROGRESS' ||
                            currentRequest.status === 'APPROVED'
                        "
                        type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-4 text-xs font-black text-white transition hover:bg-emerald-700"
                        @click="changeStatus('COMPLETED')"
                    >
                        <CheckCircle2 class="size-4" />
                        Terminer
                    </button>

                    <button
                        v-if="
                            !['COMPLETED', 'CANCELLED', 'REJECTED'].includes(
                                currentRequest.status,
                            )
                        "
                        type="button"
                        class="inline-flex h-10 items-center gap-2 rounded-xl border border-red-200 bg-white px-4 text-xs font-black text-red-600 transition hover:bg-red-50"
                        @click="changeStatus('CANCELLED')"
                    >
                        <XCircle class="size-4" />
                        Annuler
                    </button>
                </div>
            </div>
        </div>

        <!-- CONTENT -->
        <div
            class="mx-auto grid max-w-[1500px] gap-5 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_370px]"
        >
            <!-- LEFT -->
            <main class="space-y-5">
                <!-- DETAILS -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h2 class="text-sm font-black text-slate-900">
                                Détail de la demande
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Informations communiquées par le demandeur.
                            </p>
                        </div>

                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <FileText class="size-4" />
                        </div>
                    </div>

                    <div class="p-5">
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50/70 p-4"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Type de demande
                                </p>

                                <p
                                    class="mt-2 text-sm font-bold text-slate-900"
                                >
                                    {{ currentRequest.type?.name ?? "—" }}
                                </p>

                                <p
                                    v-if="currentRequest.type?.code"
                                    class="mt-1 text-[10px] font-semibold text-slate-400"
                                >
                                    {{ currentRequest.type.code }}
                                </p>
                            </div>

                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50/70 p-4"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Priorité
                                </p>

                                <span
                                    class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-black"
                                    :class="
                                        priorityClass(currentRequest.priority)
                                    "
                                >
                                    {{ priorityLabel(currentRequest.priority) }}
                                </span>
                            </div>

                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50/70 p-4"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Échéance
                                </p>

                                <p
                                    class="mt-2 text-sm font-bold text-slate-900"
                                >
                                    {{ formatDateTime(currentRequest.due_at) }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="currentRequest.type?.description"
                            class="mt-4 rounded-xl border border-emerald-100 bg-emerald-50/50 px-4 py-3"
                        >
                            <p class="text-xs leading-5 text-emerald-800">
                                {{ currentRequest.type.description }}
                            </p>
                        </div>

                        <div class="mt-6">
                            <p
                                class="text-xs font-black uppercase tracking-wider text-slate-400"
                            >
                                Description
                            </p>

                            <div
                                class="mt-3 whitespace-pre-wrap rounded-2xl border border-slate-100 bg-slate-50/50 p-5 text-sm leading-7 text-slate-700"
                            >
                                {{ currentRequest.description }}
                            </div>
                        </div>
                    </div>
                </section>

                <!-- COMMENTS -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h2
                                class="flex items-center gap-2 text-sm font-black text-slate-900"
                            >
                                <MessageSquare
                                    class="size-4 text-emerald-600"
                                />
                                Échanges
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Commentaires et suivi de la demande.
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600"
                        >
                            {{ currentRequest.comments?.length ?? 0 }}
                        </span>
                    </div>

                    <div class="p-5">
                        <!-- NEW COMMENT -->
                        <form
                            class="rounded-2xl border border-slate-200 bg-slate-50/50 p-4"
                            @submit.prevent="addComment"
                        >
                            <textarea
                                v-model="commentForm.message"
                                rows="3"
                                placeholder="Ajouter un commentaire ou une information de suivi..."
                                class="w-full resize-none border-0 bg-transparent p-0 text-sm leading-6 text-slate-800 outline-none placeholder:text-slate-400 focus:ring-0"
                            />

                            <p
                                v-if="commentForm.errors.message"
                                class="mt-2 text-xs font-semibold text-red-600"
                            >
                                {{ commentForm.errors.message }}
                            </p>

                            <div
                                class="mt-3 flex items-center justify-between gap-4 border-t border-slate-200 pt-3"
                            >
                                <p class="text-[10px] text-slate-400">
                                    Le commentaire sera ajouté au suivi de la
                                    demande.
                                </p>

                                <button
                                    type="submit"
                                    :disabled="
                                        commentForm.processing ||
                                        !commentForm.message.trim()
                                    "
                                    class="inline-flex h-9 shrink-0 items-center gap-2 rounded-lg bg-slate-950 px-4 text-xs font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                                >
                                    <Send class="size-3.5" />
                                    Envoyer
                                </button>
                            </div>
                        </form>

                        <!-- COMMENTS LIST -->
                        <template
                            v-if="
                                currentRequest.comments &&
                                currentRequest.comments.length > 0
                            "
                        >
                            <div class="mt-6 space-y-5">
                                <div
                                    v-for="comment in currentRequest.comments"
                                    :key="comment.id"
                                    class="flex gap-3"
                                >
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-[10px] font-black text-emerald-700 ring-1 ring-emerald-100"
                                    >
                                        {{ initials(comment.user) }}
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex flex-wrap items-center gap-x-2 gap-y-1"
                                        >
                                            <span
                                                class="text-xs font-black text-slate-900"
                                            >
                                                {{ userName(comment.user) }}
                                            </span>

                                            <span
                                                class="text-[10px] text-slate-400"
                                            >
                                                {{
                                                    formatDateTime(
                                                        comment.created_at,
                                                    )
                                                }}
                                            </span>

                                            <span
                                                v-if="comment.is_internal"
                                                class="rounded-full bg-amber-50 px-2 py-0.5 text-[9px] font-black uppercase text-amber-700"
                                            >
                                                Interne
                                            </span>
                                        </div>

                                        <div
                                            class="mt-2 whitespace-pre-wrap rounded-r-xl rounded-bl-xl bg-slate-50 px-4 py-3 text-sm leading-6 text-slate-700"
                                        >
                                            {{ comment.message }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="py-10 text-center">
                                <div
                                    class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-50"
                                >
                                    <MessageSquare
                                        class="size-5 text-slate-300"
                                    />
                                </div>

                                <p
                                    class="mt-3 text-xs font-semibold text-slate-400"
                                >
                                    Aucun commentaire pour le moment.
                                </p>
                            </div>
                        </template>
                    </div>
                </section>

                <!-- HISTORY -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div class="border-b border-slate-100 px-5 py-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2
                                    class="flex items-center gap-2 text-sm font-black text-slate-900"
                                >
                                    <History class="size-4 text-emerald-600" />
                                    Historique
                                </h2>

                                <p class="mt-1 text-xs text-slate-500">
                                    Traçabilité complète de la demande.
                                </p>
                            </div>

                            <span
                                class="rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black text-slate-600"
                            >
                                {{ currentRequest.histories?.length ?? 0 }}
                            </span>
                        </div>
                    </div>

                    <div class="p-5">
                        <template
                            v-if="
                                currentRequest.histories &&
                                currentRequest.histories.length > 0
                            "
                        >
                            <div
                                class="relative space-y-0 before:absolute before:bottom-4 before:left-[7px] before:top-4 before:w-px before:bg-slate-200"
                            >
                                <div
                                    v-for="history in currentRequest.histories"
                                    :key="history.id"
                                    class="relative flex gap-4 pb-6 last:pb-0"
                                >
                                    <div
                                        class="relative z-10 mt-1 size-[15px] shrink-0 rounded-full border-[3px] border-white bg-emerald-500 ring-1 ring-emerald-200"
                                    />

                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex flex-wrap items-start justify-between gap-2"
                                        >
                                            <p
                                                class="text-xs font-bold text-slate-800"
                                            >
                                                {{
                                                    historyActionLabel(history)
                                                }}
                                            </p>

                                            <span
                                                class="text-[10px] text-slate-400"
                                            >
                                                {{
                                                    formatDateTime(
                                                        history.created_at,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <div
                                            v-if="
                                                history.from_status ||
                                                history.to_status
                                            "
                                            class="mt-2 flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                v-if="history.from_status"
                                                class="rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-[9px] font-bold text-slate-500"
                                            >
                                                {{
                                                    statusLabel(
                                                        history.from_status,
                                                    )
                                                }}
                                            </span>

                                            <span
                                                v-if="
                                                    history.from_status &&
                                                    history.to_status
                                                "
                                                class="text-xs text-slate-300"
                                            >
                                                →
                                            </span>

                                            <span
                                                v-if="history.to_status"
                                                class="rounded-full border border-emerald-100 bg-emerald-50 px-2 py-1 text-[9px] font-black text-emerald-700"
                                            >
                                                {{
                                                    statusLabel(
                                                        history.to_status,
                                                    )
                                                }}
                                            </span>
                                        </div>

                                        <p
                                            v-if="history.user"
                                            class="mt-2 text-[10px] text-slate-400"
                                        >
                                            Par
                                            <span
                                                class="font-semibold text-slate-500"
                                            >
                                                {{ userName(history.user) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <div class="py-8 text-center">
                                <History
                                    class="mx-auto size-6 text-slate-300"
                                />

                                <p
                                    class="mt-2 text-xs font-semibold text-slate-400"
                                >
                                    Aucun historique disponible.
                                </p>
                            </div>
                        </template>
                    </div>
                </section>
            </main>

            <!-- RIGHT -->
            <aside class="space-y-4">
                <!-- WORKFLOW -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 bg-gradient-to-br from-emerald-50 to-white p-5"
                    >
                        <div class="flex items-center gap-2">
                            <CircleDot class="size-4 text-emerald-600" />

                            <h2 class="text-sm font-black text-slate-900">
                                Suivi de traitement
                            </h2>
                        </div>

                        <p class="mt-1 text-xs text-slate-500">
                            État actuel et affectation.
                        </p>
                    </div>

                    <div class="space-y-5 p-5">
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                            >
                                Statut actuel
                            </p>

                            <div
                                class="mt-2 inline-flex items-center rounded-full border px-3 py-1.5 text-xs font-black"
                                :class="statusClass(currentRequest.status)"
                            >
                                {{ statusLabel(currentRequest.status) }}
                            </div>
                        </div>

                        <div class="h-px bg-slate-100" />

                        <!-- RESPONSIBLE -->
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                            >
                                Responsable
                            </p>

                            <template v-if="assignedUser">
                                <div class="mt-3 flex items-center gap-3">
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs font-black text-emerald-700 ring-1 ring-emerald-100"
                                    >
                                        {{ initials(assignedUser) }}
                                    </div>

                                    <div class="min-w-0">
                                        <p
                                            class="truncate text-sm font-black text-slate-900"
                                        >
                                            {{ userName(assignedUser) }}
                                        </p>

                                        <p
                                            class="mt-0.5 truncate text-[10px] text-slate-400"
                                        >
                                            {{
                                                assignedUser.email ??
                                                "Responsable"
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div
                                    class="mt-3 flex items-center gap-2 rounded-xl bg-amber-50 p-3 text-xs font-semibold text-amber-700"
                                >
                                    <AlertTriangle class="size-4" />
                                    Non affectée
                                </div>
                            </template>
                        </div>

                        <!-- ASSIGN -->
                        <form
                            v-if="users.length > 0"
                            class="border-t border-slate-100 pt-4"
                            @submit.prevent="assign"
                        >
                            <label
                                class="mb-2 block text-[10px] font-black uppercase tracking-wider text-slate-400"
                            >
                                Affecter à
                            </label>

                            <select
                                v-model="assignForm.assigned_to"
                                class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-700 outline-none transition focus:border-emerald-400 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">
                                    Sélectionner un responsable...
                                </option>

                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ userName(user) }}
                                </option>
                            </select>

                            <p
                                v-if="assignForm.errors.assigned_to"
                                class="mt-2 text-xs font-semibold text-red-600"
                            >
                                {{ assignForm.errors.assigned_to }}
                            </p>

                            <button
                                type="submit"
                                :disabled="
                                    assignForm.processing ||
                                    !assignForm.assigned_to
                                "
                                class="mt-2 flex h-9 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 text-xs font-black text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                            >
                                <Users class="size-3.5" />
                                Affecter
                            </button>
                        </form>
                    </div>
                </section>

                <!-- REQUESTER -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <UserRound class="size-4 text-emerald-600" />

                        <p
                            class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                        >
                            Demandeur
                        </p>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        <div
                            class="flex size-11 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-black text-slate-700"
                        >
                            {{ initials(currentRequest.creator) }}
                        </div>

                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-black text-slate-900"
                            >
                                {{ userName(currentRequest.creator) }}
                            </p>

                            <p class="mt-0.5 truncate text-xs text-slate-400">
                                {{ currentRequest.creator?.email ?? "—" }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- DATES -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <CalendarDays class="size-4 text-emerald-600" />

                        <h3 class="text-xs font-black text-slate-900">
                            Dates clés
                        </h3>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-500">
                                Création
                            </span>

                            <span
                                class="text-right text-xs font-bold text-slate-700"
                            >
                                {{ formatDateTime(currentRequest.created_at) }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-500">
                                Soumission
                            </span>

                            <span
                                class="text-right text-xs font-bold text-slate-700"
                            >
                                {{
                                    formatDateTime(currentRequest.submitted_at)
                                }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-500">
                                Affectation
                            </span>

                            <span
                                class="text-right text-xs font-bold text-slate-700"
                            >
                                {{ formatDateTime(currentRequest.assigned_at) }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-500">
                                Début traitement
                            </span>

                            <span
                                class="text-right text-xs font-bold text-slate-700"
                            >
                                {{ formatDateTime(currentRequest.started_at) }}
                            </span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-xs text-slate-500">
                                Échéance
                            </span>

                            <span
                                class="text-right text-xs font-bold text-slate-700"
                            >
                                {{ formatDateTime(currentRequest.due_at) }}
                            </span>
                        </div>

                        <div
                            v-if="currentRequest.completed_at"
                            class="flex items-start justify-between gap-4 border-t border-slate-100 pt-4"
                        >
                            <span class="text-xs text-slate-500">
                                Clôture
                            </span>

                            <span
                                class="text-right text-xs font-black text-emerald-700"
                            >
                                {{
                                    formatDateTime(currentRequest.completed_at)
                                }}
                            </span>
                        </div>
                    </div>
                </section>

                <!-- APPROVAL -->
                <section
                    v-if="requiresApproval"
                    class="rounded-2xl border border-amber-200 bg-amber-50 p-5"
                >
                    <div class="flex items-start gap-3">
                        <ShieldCheck
                            class="mt-0.5 size-5 shrink-0 text-amber-600"
                        />

                        <div>
                            <p class="text-xs font-black text-amber-900">
                                Validation requise
                            </p>

                            <p class="mt-1 text-xs leading-5 text-amber-700">
                                Ce type de demande nécessite une approbation
                                avant sa clôture.
                            </p>

                            <div
                                v-if="approvedUser"
                                class="mt-3 rounded-xl border border-emerald-200 bg-white/70 p-3"
                            >
                                <p
                                    class="text-[10px] font-bold uppercase text-emerald-600"
                                >
                                    Approuvée par
                                </p>

                                <p
                                    class="mt-1 text-xs font-black text-emerald-800"
                                >
                                    {{ userName(approvedUser) }}
                                </p>

                                <p
                                    v-if="currentRequest.approved_at"
                                    class="mt-1 text-[10px] text-emerald-600"
                                >
                                    {{
                                        formatDateTime(
                                            currentRequest.approved_at,
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    <!-- REQUEST NOT PROVIDED -->
    <div
        v-else
        class="flex min-h-[550px] items-center justify-center bg-slate-50/60 p-6"
    >
        <div
            class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 text-center shadow-sm"
        >
            <div
                class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
            >
                <FileText class="size-6" />
            </div>

            <h1 class="mt-5 text-xl font-black text-slate-950">
                Demande indisponible
            </h1>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Les informations de cette demande n'ont pas été transmises à la
                page.
            </p>

            <Link
                href="/requests"
                class="mt-6 inline-flex h-10 items-center gap-2 rounded-xl bg-slate-950 px-5 text-xs font-black text-white transition hover:bg-emerald-700"
            >
                <ArrowLeft class="size-4" />
                Retour aux demandes
            </Link>
        </div>
    </div>
</template>
