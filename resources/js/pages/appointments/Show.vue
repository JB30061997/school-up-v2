<script setup lang="ts">
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

import {
    AlertTriangle,
    ArrowLeft,
    Ban,
    CalendarCheck2,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock3,
    FileText,
    History,
    MapPin,
    MessageSquareText,
    ShieldCheck,
    UserCheck,
    UserRound,
    UsersRound,
    X,
    XCircle,
} from "@lucide/vue";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type User = {
    id: number;
    name?: string | null;
    first_name?: string | null;
    last_name?: string | null;
    email?: string | null;
};

type AppointmentType = {
    id: number;
    name: string;
    code: string;
    duration_minutes?: number | null;
};

type AppointmentHistory = {
    id: number;
    action?: string | null;
    old_status?: string | null;
    new_status?: string | null;
    note?: string | null;
    created_at: string;
    user?: User | null;
    creator?: User | null;
};

type Appointment = {
    id: number;
    reference: string;
    title: string;
    description?: string | null;
    location?: string | null;

    status: string;

    starts_at: string;
    ends_at: string;

    confirmation_note?: string | null;
    rejection_reason?: string | null;
    cancellation_reason?: string | null;

    confirmed_at?: string | null;
    rejected_at?: string | null;
    cancelled_at?: string | null;
    completed_at?: string | null;

    created_at?: string | null;
    updated_at?: string | null;

    type?: AppointmentType | null;
    creator?: User | null;

    assigned_to?: User | null;
    assignedTo?: User | null;

    histories?: AppointmentHistory[];
};

type Permissions = {
    assign?: boolean;
    validate?: boolean;
    cancel?: boolean;
    update?: boolean;
};

const props = defineProps<{
    appointment: Appointment;
    users?: User[];
    statuses?: Record<string, string>;
    permissions?: Permissions;
}>();

/*
|--------------------------------------------------------------------------
| Modal state
|--------------------------------------------------------------------------
*/

type ModalType = "assign" | "confirm" | "reject" | "cancel" | "complete" | null;

const activeModal = ref<ModalType>(null);

/*
|--------------------------------------------------------------------------
| Forms
|--------------------------------------------------------------------------
*/

const assignForm = useForm({
    assigned_to: "",
});

const confirmForm = useForm({
    confirmation_note: "",
});

const rejectForm = useForm({
    rejection_reason: "",
});

const cancelForm = useForm({
    cancellation_reason: "",
});

/*
|--------------------------------------------------------------------------
| Appointment values
|--------------------------------------------------------------------------
*/

const assignedUser = computed(() => {
    return (
        props.appointment.assignedTo ?? props.appointment.assigned_to ?? null
    );
});

const histories = computed(() => {
    return props.appointment.histories ?? [];
});

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
|
| Ila controller kayrj3 permissions, ghadi nesta3mlohom.
| Ila mazal ma kayrj3homch, actions ghadi ybano bach ma ytkhbach UI.
|
*/

const canAssign = computed(() => {
    return props.permissions?.assign ?? true;
});

const canValidate = computed(() => {
    return props.permissions?.validate ?? true;
});

const canCancel = computed(() => {
    return props.permissions?.cancel ?? true;
});

const canUpdate = computed(() => {
    return props.permissions?.update ?? true;
});

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const statusLabel = (status: string): string => {
    if (props.statuses?.[status]) {
        return props.statuses[status];
    }

    const labels: Record<string, string> = {
        REQUESTED: "Demandé",
        CONFIRMED: "Confirmé",
        REJECTED: "Rejeté",
        CANCELLED: "Annulé",
        COMPLETED: "Effectué",
    };

    return labels[status] ?? status;
};

const statusClasses = (status: string): string => {
    switch (status) {
        case "CONFIRMED":
            return "bg-blue-50 text-blue-700 ring-blue-100 dark:bg-blue-950/30 dark:text-blue-300 dark:ring-blue-900";

        case "COMPLETED":
            return "bg-emerald-50 text-emerald-700 ring-emerald-100 dark:bg-emerald-950/30 dark:text-emerald-300 dark:ring-emerald-900";

        case "REJECTED":
            return "bg-rose-50 text-rose-700 ring-rose-100 dark:bg-rose-950/30 dark:text-rose-300 dark:ring-rose-900";

        case "CANCELLED":
            return "bg-slate-100 text-slate-600 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800";

        default:
            return "bg-amber-50 text-amber-700 ring-amber-100 dark:bg-amber-950/30 dark:text-amber-300 dark:ring-amber-900";
    }
};

const statusDotClasses = (status: string): string => {
    switch (status) {
        case "CONFIRMED":
            return "bg-blue-500";

        case "COMPLETED":
            return "bg-emerald-500";

        case "REJECTED":
            return "bg-rose-500";

        case "CANCELLED":
            return "bg-slate-400";

        default:
            return "bg-amber-500";
    }
};

const isFinalStatus = computed(() => {
    return ["REJECTED", "CANCELLED", "COMPLETED"].includes(
        props.appointment.status,
    );
});

/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

const userName = (user?: User | null): string => {
    if (!user) {
        return "Non affecté";
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

/*
|--------------------------------------------------------------------------
| Date helpers
|--------------------------------------------------------------------------
*/

const toDate = (value?: string | null): Date | null => {
    if (!value) {
        return null;
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    return date;
};

const formatDate = (value?: string | null): string => {
    const date = toDate(value);

    if (!date) {
        return "—";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        weekday: "long",
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(date);
};

const formatShortDate = (value?: string | null): string => {
    const date = toDate(value);

    if (!date) {
        return "—";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(date);
};

const formatTime = (value?: string | null): string => {
    const date = toDate(value);

    if (!date) {
        return "--:--";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    }).format(date);
};

const formatDateTime = (value?: string | null): string => {
    const date = toDate(value);

    if (!date) {
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

const duration = computed(() => {
    const start = toDate(props.appointment.starts_at);
    const end = toDate(props.appointment.ends_at);

    if (!start || !end) {
        return "—";
    }

    const minutes = Math.max(
        0,
        Math.round((end.getTime() - start.getTime()) / 60000),
    );

    if (minutes < 60) {
        return `${minutes} min`;
    }

    const hours = Math.floor(minutes / 60);
    const remaining = minutes % 60;

    if (!remaining) {
        return `${hours} h`;
    }

    return `${hours} h ${remaining} min`;
});

/*
|--------------------------------------------------------------------------
| Modals
|--------------------------------------------------------------------------
*/

const openAssignModal = (): void => {
    assignForm.reset();

    assignForm.assigned_to = assignedUser.value
        ? String(assignedUser.value.id)
        : "";

    activeModal.value = "assign";
};

const closeModal = (): void => {
    activeModal.value = null;

    assignForm.clearErrors();
    confirmForm.clearErrors();
    rejectForm.clearErrors();
    cancelForm.clearErrors();
};

/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

const submitAssign = (): void => {
    assignForm.post(`/appointments/${props.appointment.id}/assign`, {
        preserveScroll: true,

        onSuccess: () => {
            closeModal();
        },
    });
};

const submitConfirm = (): void => {
    confirmForm.post(`/appointments/${props.appointment.id}/confirm`, {
        preserveScroll: true,

        onSuccess: () => {
            closeModal();
            confirmForm.reset();
        },
    });
};

const submitReject = (): void => {
    rejectForm.post(`/appointments/${props.appointment.id}/reject`, {
        preserveScroll: true,

        onSuccess: () => {
            closeModal();
            rejectForm.reset();
        },
    });
};

const submitCancel = (): void => {
    cancelForm.post(`/appointments/${props.appointment.id}/cancel`, {
        preserveScroll: true,

        onSuccess: () => {
            closeModal();
            cancelForm.reset();
        },
    });
};

const submitComplete = (): void => {
    router.post(
        `/appointments/${props.appointment.id}/complete`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                closeModal();
            },
        },
    );
};

/*
|--------------------------------------------------------------------------
| History
|--------------------------------------------------------------------------
*/

const historyTitle = (history: AppointmentHistory): string => {
    if (history.new_status) {
        return `Statut : ${statusLabel(history.new_status)}`;
    }

    if (history.action) {
        const labels: Record<string, string> = {
            CREATED: "Rendez-vous créé",
            ASSIGNED: "Rendez-vous affecté",
            CONFIRMED: "Rendez-vous confirmé",
            REJECTED: "Rendez-vous rejeté",
            CANCELLED: "Rendez-vous annulé",
            COMPLETED: "Rendez-vous effectué",
        };

        return labels[history.action] ?? history.action;
    }

    return "Mise à jour du rendez-vous";
};

const historyUser = (history: AppointmentHistory): User | null => {
    return history.user ?? history.creator ?? null;
};
</script>

<template>
    <Head :title="appointment.reference" />

    <main class="min-h-full bg-[#f8faf9] p-4 sm:p-5 lg:p-6 dark:bg-background">
        <div class="mx-auto w-full max-w-[1500px]">
            <!-- BACK -->

            <div class="mb-4">
                <Link
                    href="/appointments"
                    class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition-colors hover:text-emerald-700 dark:text-muted-foreground dark:hover:text-emerald-400"
                >
                    <ArrowLeft class="size-4" />

                    Retour aux rendez-vous
                </Link>
            </div>

            <!-- ================================================= -->
            <!-- HERO -->
            <!-- ================================================= -->

            <section
                class="relative mb-5 overflow-hidden rounded-[26px] border border-emerald-100 bg-white shadow-sm dark:border-emerald-900/50 dark:bg-card"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-24 size-80 rounded-full bg-emerald-100/70 blur-3xl dark:bg-emerald-950/30"
                />

                <div
                    class="pointer-events-none absolute right-[25%] top-0 size-44 rounded-full bg-teal-50 blur-3xl dark:bg-teal-950/20"
                />

                <div class="relative p-6 sm:p-7">
                    <div
                        class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between"
                    >
                        <div class="flex min-w-0 items-start gap-4">
                            <div
                                class="hidden size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20 sm:flex"
                            >
                                <CalendarCheck2 class="size-6" />
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="text-xs font-black tracking-wide text-emerald-700 dark:text-emerald-400"
                                    >
                                        {{ appointment.reference }}
                                    </span>

                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold ring-1 ring-inset"
                                        :class="
                                            statusClasses(appointment.status)
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                statusDotClasses(
                                                    appointment.status,
                                                )
                                            "
                                        />

                                        {{ statusLabel(appointment.status) }}
                                    </span>
                                </div>

                                <h1
                                    class="mt-2 break-words text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl dark:text-white"
                                >
                                    {{ appointment.title }}
                                </h1>

                                <div
                                    class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-slate-500"
                                >
                                    <div class="flex items-center gap-1.5">
                                        <CalendarDays
                                            class="size-4 text-emerald-600"
                                        />

                                        <span class="capitalize">
                                            {{
                                                formatDate(
                                                    appointment.starts_at,
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <Clock3
                                            class="size-4 text-emerald-600"
                                        />

                                        {{ formatTime(appointment.starts_at) }}

                                        <span>→</span>

                                        {{ formatTime(appointment.ends_at) }}
                                    </div>

                                    <div
                                        v-if="appointment.location"
                                        class="flex items-center gap-1.5"
                                    >
                                        <MapPin
                                            class="size-4 text-emerald-600"
                                        />

                                        {{ appointment.location }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            v-if="!isFinalStatus && canAssign"
                            class="shrink-0"
                        >
                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-600 shadow-sm transition-all hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 dark:border-border dark:bg-background"
                                @click="openAssignModal"
                            >
                                <UserCheck class="size-4" />

                                Affecter
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================= -->
            <!-- CONTENT -->
            <!-- ================================================= -->

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
                <!-- LEFT -->

                <div class="space-y-5">
                    <!-- MAIN INFORMATION -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-border"
                        >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30"
                            >
                                <FileText class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold">
                                    Informations du rendez-vous
                                </h2>

                                <p class="mt-0.5 text-[11px] text-slate-500">
                                    Détails généraux de la planification
                                </p>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6">
                            <div
                                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4"
                            >
                                <!-- TYPE -->

                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-muted/30"
                                >
                                    <p
                                        class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                                    >
                                        Type
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-bold text-slate-700 dark:text-foreground"
                                    >
                                        {{
                                            appointment.type?.name ??
                                            "Non défini"
                                        }}
                                    </p>
                                </div>

                                <!-- DATE -->

                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-muted/30"
                                >
                                    <p
                                        class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                                    >
                                        Date
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-bold capitalize text-slate-700 dark:text-foreground"
                                    >
                                        {{
                                            formatShortDate(
                                                appointment.starts_at,
                                            )
                                        }}
                                    </p>
                                </div>

                                <!-- TIME -->

                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-muted/30"
                                >
                                    <p
                                        class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                                    >
                                        Horaire
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-bold text-slate-700 dark:text-foreground"
                                    >
                                        {{ formatTime(appointment.starts_at) }}
                                        -
                                        {{ formatTime(appointment.ends_at) }}
                                    </p>
                                </div>

                                <!-- DURATION -->

                                <div
                                    class="rounded-2xl bg-slate-50 p-4 dark:bg-muted/30"
                                >
                                    <p
                                        class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                                    >
                                        Durée
                                    </p>

                                    <p
                                        class="mt-2 text-sm font-bold text-slate-700 dark:text-foreground"
                                    >
                                        {{ duration }}
                                    </p>
                                </div>
                            </div>

                            <!-- LOCATION -->

                            <div
                                class="mt-5 flex items-start gap-3 rounded-2xl border border-slate-100 p-4 dark:border-border"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/30 dark:text-violet-400"
                                >
                                    <MapPin class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                                    >
                                        Lieu du rendez-vous
                                    </p>

                                    <p
                                        class="mt-1.5 text-sm font-semibold text-slate-700 dark:text-foreground"
                                    >
                                        {{
                                            appointment.location ||
                                            "Aucun lieu renseigné"
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- DESCRIPTION -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-border"
                        >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/30 dark:text-blue-400"
                            >
                                <MessageSquareText class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold">Description</h2>

                                <p class="mt-0.5 text-[11px] text-slate-500">
                                    Informations complémentaires
                                </p>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6">
                            <p
                                v-if="appointment.description"
                                class="whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-muted-foreground"
                            >
                                {{ appointment.description }}
                            </p>

                            <div
                                v-else
                                class="flex min-h-28 items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-5 text-center text-sm text-slate-400 dark:border-border dark:bg-muted/20"
                            >
                                Aucune description renseignée pour ce
                                rendez-vous.
                            </div>
                        </div>
                    </section>

                    <!-- NOTES / REASONS -->

                    <section
                        v-if="
                            appointment.confirmation_note ||
                            appointment.rejection_reason ||
                            appointment.cancellation_reason
                        "
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-border"
                        >
                            <h2 class="text-sm font-bold">
                                Suivi du rendez-vous
                            </h2>
                        </div>

                        <div class="space-y-3 p-5 sm:p-6">
                            <div
                                v-if="appointment.confirmation_note"
                                class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 dark:border-blue-900 dark:bg-blue-950/20"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-wider text-blue-600"
                                >
                                    Note de confirmation
                                </p>

                                <p
                                    class="mt-2 text-sm leading-6 text-blue-900 dark:text-blue-200"
                                >
                                    {{ appointment.confirmation_note }}
                                </p>
                            </div>

                            <div
                                v-if="appointment.rejection_reason"
                                class="rounded-xl border border-rose-100 bg-rose-50/60 p-4 dark:border-rose-900 dark:bg-rose-950/20"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-wider text-rose-600"
                                >
                                    Motif du rejet
                                </p>

                                <p
                                    class="mt-2 text-sm leading-6 text-rose-900 dark:text-rose-200"
                                >
                                    {{ appointment.rejection_reason }}
                                </p>
                            </div>

                            <div
                                v-if="appointment.cancellation_reason"
                                class="rounded-xl border border-slate-200 bg-slate-50 p-4 dark:border-border dark:bg-muted/30"
                            >
                                <p
                                    class="text-[10px] font-black uppercase tracking-wider text-slate-500"
                                >
                                    Motif d'annulation
                                </p>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-700 dark:text-muted-foreground"
                                >
                                    {{ appointment.cancellation_reason }}
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- HISTORY -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-border"
                        >
                            <div
                                class="flex size-9 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/30 dark:text-amber-400"
                            >
                                <History class="size-4" />
                            </div>

                            <div>
                                <h2 class="text-sm font-bold">Historique</h2>

                                <p class="mt-0.5 text-[11px] text-slate-500">
                                    Traçabilité des actions
                                </p>
                            </div>
                        </div>

                        <div v-if="histories.length" class="p-5 sm:p-6">
                            <div
                                v-for="(history, index) in histories"
                                :key="history.id"
                                class="relative flex gap-4 pb-6 last:pb-0"
                            >
                                <div
                                    v-if="index < histories.length - 1"
                                    class="absolute left-[17px] top-9 h-[calc(100%-20px)] w-px bg-slate-200 dark:bg-border"
                                />

                                <div
                                    class="relative z-10 flex size-9 shrink-0 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 text-emerald-600 dark:border-emerald-900 dark:bg-emerald-950/30"
                                >
                                    <Check class="size-3.5" />
                                </div>

                                <div class="min-w-0 flex-1 pt-0.5">
                                    <div
                                        class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between"
                                    >
                                        <p
                                            class="text-sm font-bold text-slate-700 dark:text-foreground"
                                        >
                                            {{ historyTitle(history) }}
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

                                    <p class="mt-1 text-xs text-slate-500">
                                        Par
                                        <strong
                                            class="font-semibold text-slate-600 dark:text-muted-foreground"
                                        >
                                            {{ userName(historyUser(history)) }}
                                        </strong>
                                    </p>

                                    <p
                                        v-if="history.note"
                                        class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs leading-5 text-slate-500 dark:bg-muted/30"
                                    >
                                        {{ history.note }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="px-6 py-10 text-center text-sm text-slate-400"
                        >
                            Aucun historique disponible.
                        </div>
                    </section>
                </div>

                <!-- ================================================= -->
                <!-- RIGHT SIDEBAR -->
                <!-- ================================================= -->

                <aside class="space-y-5">
                    <!-- STATUS -->

                    <section
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="border-b border-slate-100 bg-gradient-to-br from-emerald-50 to-white p-5 dark:border-border dark:from-emerald-950/20 dark:to-card"
                        >
                            <p
                                class="text-[10px] font-black uppercase tracking-[0.15em] text-emerald-600"
                            >
                                État actuel
                            </p>

                            <div class="mt-3 flex items-center gap-3">
                                <div
                                    class="flex size-11 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-emerald-100 dark:bg-background dark:ring-emerald-900"
                                >
                                    <CalendarCheck2
                                        class="size-5 text-emerald-600"
                                    />
                                </div>

                                <div>
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-bold ring-1 ring-inset"
                                        :class="
                                            statusClasses(appointment.status)
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                statusDotClasses(
                                                    appointment.status,
                                                )
                                            "
                                        />

                                        {{ statusLabel(appointment.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-5">
                            <!-- CREATOR -->

                            <p
                                class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                            >
                                Créé par
                            </p>

                            <div class="mt-2 flex items-center gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-black text-slate-600 dark:bg-muted"
                                >
                                    {{ initials(appointment.creator) }}
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-bold text-slate-700 dark:text-foreground"
                                    >
                                        {{ userName(appointment.creator) }}
                                    </p>

                                    <p
                                        v-if="appointment.creator?.email"
                                        class="truncate text-[10px] text-slate-400"
                                    >
                                        {{ appointment.creator.email }}
                                    </p>
                                </div>
                            </div>

                            <div
                                class="my-4 h-px bg-slate-100 dark:bg-border"
                            />

                            <!-- ASSIGNEE -->

                            <div
                                class="flex items-center justify-between gap-2"
                            >
                                <p
                                    class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                                >
                                    Responsable
                                </p>

                                <button
                                    v-if="canAssign && !isFinalStatus"
                                    type="button"
                                    class="text-[10px] font-bold text-emerald-600 hover:text-emerald-700"
                                    @click="openAssignModal"
                                >
                                    Modifier
                                </button>
                            </div>

                            <div class="mt-2 flex items-center gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-[10px] font-black text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                >
                                    {{ initials(assignedUser) }}
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-bold text-slate-700 dark:text-foreground"
                                    >
                                        {{ userName(assignedUser) }}
                                    </p>

                                    <p
                                        v-if="assignedUser?.email"
                                        class="truncate text-[10px] text-slate-400"
                                    >
                                        {{ assignedUser.email }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- ACTIONS -->

                    <section
                        v-if="!isFinalStatus"
                        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-border dark:bg-card"
                    >
                        <div
                            class="border-b border-slate-100 px-5 py-4 dark:border-border"
                        >
                            <div class="flex items-center gap-2">
                                <ShieldCheck class="size-4 text-emerald-600" />

                                <h2 class="text-sm font-bold">Actions</h2>
                            </div>

                            <p class="mt-1 text-[11px] text-slate-500">
                                Gérez le cycle du rendez-vous
                            </p>
                        </div>

                        <div class="space-y-2 p-4">
                            <!-- CONFIRM -->

                            <button
                                v-if="
                                    appointment.status === 'REQUESTED' &&
                                    canValidate
                                "
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 p-3 text-left transition-all hover:border-emerald-200 hover:bg-emerald-100/70 dark:border-emerald-900 dark:bg-emerald-950/20"
                                @click="activeModal = 'confirm'"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-600 text-white"
                                >
                                    <CheckCircle2 class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold text-emerald-800 dark:text-emerald-300"
                                    >
                                        Confirmer
                                    </p>

                                    <p
                                        class="mt-0.5 text-[10px] text-emerald-600"
                                    >
                                        Valider ce rendez-vous
                                    </p>
                                </div>
                            </button>

                            <!-- REJECT -->

                            <button
                                v-if="
                                    appointment.status === 'REQUESTED' &&
                                    canValidate
                                "
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl border border-rose-100 bg-rose-50/60 p-3 text-left transition-all hover:border-rose-200 hover:bg-rose-100/60 dark:border-rose-900 dark:bg-rose-950/20"
                                @click="activeModal = 'reject'"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white"
                                >
                                    <XCircle class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold text-rose-700 dark:text-rose-300"
                                    >
                                        Rejeter
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-rose-500">
                                        Refuser la demande
                                    </p>
                                </div>
                            </button>

                            <!-- COMPLETE -->

                            <button
                                v-if="
                                    appointment.status === 'CONFIRMED' &&
                                    canUpdate
                                "
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl border border-blue-100 bg-blue-50/60 p-3 text-left transition-all hover:border-blue-200 hover:bg-blue-100/60 dark:border-blue-900 dark:bg-blue-950/20"
                                @click="activeModal = 'complete'"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-white"
                                >
                                    <Check class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold text-blue-700 dark:text-blue-300"
                                    >
                                        Marquer effectué
                                    </p>

                                    <p class="mt-0.5 text-[10px] text-blue-500">
                                        Le rendez-vous a eu lieu
                                    </p>
                                </div>
                            </button>

                            <!-- CANCEL -->

                            <button
                                v-if="canCancel"
                                type="button"
                                class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-left transition-all hover:bg-slate-100 dark:border-border dark:bg-muted/30 dark:hover:bg-muted"
                                @click="activeModal = 'cancel'"
                            >
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-slate-600 text-white"
                                >
                                    <Ban class="size-4" />
                                </div>

                                <div>
                                    <p
                                        class="text-xs font-bold text-slate-700 dark:text-foreground"
                                    >
                                        Annuler
                                    </p>

                                    <p
                                        class="mt-0.5 text-[10px] text-slate-500"
                                    >
                                        Annuler ce rendez-vous
                                    </p>
                                </div>
                            </button>
                        </div>
                    </section>

                    <!-- META -->

                    <section
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-border dark:bg-card"
                    >
                        <p
                            class="text-[9px] font-black uppercase tracking-wider text-slate-400"
                        >
                            Informations système
                        </p>

                        <div class="mt-4 space-y-3 text-xs">
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span class="text-slate-400"> Référence </span>

                                <strong
                                    class="text-slate-600 dark:text-foreground"
                                >
                                    {{ appointment.reference }}
                                </strong>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span class="text-slate-400"> Création </span>

                                <strong
                                    class="text-right text-slate-600 dark:text-foreground"
                                >
                                    {{ formatDateTime(appointment.created_at) }}
                                </strong>
                            </div>

                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span class="text-slate-400">
                                    Modification
                                </span>

                                <strong
                                    class="text-right text-slate-600 dark:text-foreground"
                                >
                                    {{ formatDateTime(appointment.updated_at) }}
                                </strong>
                            </div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>

        <!-- ===================================================== -->
        <!-- MODAL OVERLAY -->
        <!-- ===================================================== -->

        <div
            v-if="activeModal"
            class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm"
            @click.self="closeModal"
        >
            <div
                class="w-full max-w-md overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-border dark:bg-card"
            >
                <!-- ================= ASSIGN ================= -->

                <template v-if="activeModal === 'assign'">
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 dark:border-border"
                    >
                        <div>
                            <h3 class="text-sm font-bold">
                                Affecter le rendez-vous
                            </h3>

                            <p class="mt-1 text-[11px] text-slate-500">
                                Sélectionnez un responsable
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 dark:hover:bg-muted"
                            @click="closeModal"
                        >
                            <X class="size-4" />
                        </button>
                    </div>

                    <form class="p-5" @submit.prevent="submitAssign">
                        <label
                            class="mb-2 block text-xs font-bold text-slate-600 dark:text-foreground"
                        >
                            Responsable
                        </label>

                        <select
                            v-model="assignForm.assigned_to"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100/50 dark:border-border dark:bg-background"
                        >
                            <option value="">Non affecté</option>

                            <option
                                v-for="user in users ?? []"
                                :key="user.id"
                                :value="String(user.id)"
                            >
                                {{ userName(user) }}
                            </option>
                        </select>

                        <p
                            v-if="assignForm.errors.assigned_to"
                            class="mt-2 text-xs text-rose-600"
                        >
                            {{ assignForm.errors.assigned_to }}
                        </p>

                        <div class="mt-5 flex justify-end gap-2">
                            <button
                                type="button"
                                class="h-10 rounded-xl px-4 text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-muted"
                                @click="closeModal"
                            >
                                Annuler
                            </button>

                            <button
                                type="submit"
                                :disabled="assignForm.processing"
                                class="h-10 rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-50"
                            >
                                Affecter
                            </button>
                        </div>
                    </form>
                </template>

                <!-- ================= CONFIRM ================= -->

                <template v-else-if="activeModal === 'confirm'">
                    <div
                        class="flex items-start gap-3 border-b border-emerald-100 bg-emerald-50/70 p-5 dark:border-emerald-900 dark:bg-emerald-950/20"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white"
                        >
                            <CheckCircle2 class="size-5" />
                        </div>

                        <div class="flex-1">
                            <h3
                                class="text-sm font-bold text-emerald-900 dark:text-emerald-300"
                            >
                                Confirmer le rendez-vous
                            </h3>

                            <p
                                class="mt-1 text-xs text-emerald-700 dark:text-emerald-400"
                            >
                                Le rendez-vous passera au statut confirmé.
                            </p>
                        </div>

                        <button type="button" @click="closeModal">
                            <X class="size-4 text-emerald-700" />
                        </button>
                    </div>

                    <form class="p-5" @submit.prevent="submitConfirm">
                        <label class="mb-2 block text-xs font-bold">
                            Note de confirmation
                        </label>

                        <textarea
                            v-model="confirmForm.confirmation_note"
                            rows="4"
                            placeholder="Ajouter une note si nécessaire..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm outline-none focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100/50 dark:border-border dark:bg-background"
                        />

                        <p
                            v-if="confirmForm.errors.confirmation_note"
                            class="mt-2 text-xs text-rose-600"
                        >
                            {{ confirmForm.errors.confirmation_note }}
                        </p>

                        <div class="mt-5 flex justify-end gap-2">
                            <button
                                type="button"
                                class="h-10 rounded-xl px-4 text-sm font-semibold text-slate-500 hover:bg-slate-100"
                                @click="closeModal"
                            >
                                Retour
                            </button>

                            <button
                                type="submit"
                                :disabled="confirmForm.processing"
                                class="h-10 rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white hover:bg-emerald-700 disabled:opacity-50"
                            >
                                Confirmer
                            </button>
                        </div>
                    </form>
                </template>

                <!-- ================= REJECT ================= -->

                <template v-else-if="activeModal === 'reject'">
                    <div
                        class="flex items-start gap-3 border-b border-rose-100 bg-rose-50 p-5 dark:border-rose-900 dark:bg-rose-950/20"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-rose-500 text-white"
                        >
                            <XCircle class="size-5" />
                        </div>

                        <div class="flex-1">
                            <h3
                                class="text-sm font-bold text-rose-800 dark:text-rose-300"
                            >
                                Rejeter le rendez-vous
                            </h3>

                            <p class="mt-1 text-xs text-rose-600">
                                Indiquez le motif du rejet.
                            </p>
                        </div>

                        <button type="button" @click="closeModal">
                            <X class="size-4 text-rose-600" />
                        </button>
                    </div>

                    <form class="p-5" @submit.prevent="submitReject">
                        <label class="mb-2 block text-xs font-bold">
                            Motif du rejet
                        </label>

                        <textarea
                            v-model="rejectForm.rejection_reason"
                            rows="4"
                            placeholder="Expliquez pourquoi le rendez-vous est rejeté..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm outline-none focus:border-rose-300 focus:ring-4 focus:ring-rose-100/50 dark:border-border dark:bg-background"
                        />

                        <p
                            v-if="rejectForm.errors.rejection_reason"
                            class="mt-2 text-xs text-rose-600"
                        >
                            {{ rejectForm.errors.rejection_reason }}
                        </p>

                        <div class="mt-5 flex justify-end gap-2">
                            <button
                                type="button"
                                class="h-10 rounded-xl px-4 text-sm font-semibold text-slate-500 hover:bg-slate-100"
                                @click="closeModal"
                            >
                                Retour
                            </button>

                            <button
                                type="submit"
                                :disabled="rejectForm.processing"
                                class="h-10 rounded-xl bg-rose-500 px-5 text-sm font-bold text-white hover:bg-rose-600 disabled:opacity-50"
                            >
                                Rejeter
                            </button>
                        </div>
                    </form>
                </template>

                <!-- ================= CANCEL ================= -->

                <template v-else-if="activeModal === 'cancel'">
                    <div
                        class="flex items-start gap-3 border-b border-slate-200 bg-slate-50 p-5 dark:border-border dark:bg-muted/30"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-600 text-white"
                        >
                            <Ban class="size-5" />
                        </div>

                        <div class="flex-1">
                            <h3 class="text-sm font-bold">
                                Annuler le rendez-vous
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Cette action changera le statut du rendez-vous.
                            </p>
                        </div>

                        <button type="button" @click="closeModal">
                            <X class="size-4 text-slate-500" />
                        </button>
                    </div>

                    <form class="p-5" @submit.prevent="submitCancel">
                        <label class="mb-2 block text-xs font-bold">
                            Motif d'annulation
                        </label>

                        <textarea
                            v-model="cancelForm.cancellation_reason"
                            rows="4"
                            placeholder="Indiquez le motif de l'annulation..."
                            class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm outline-none focus:border-slate-300 focus:ring-4 focus:ring-slate-100 dark:border-border dark:bg-background"
                        />

                        <p
                            v-if="cancelForm.errors.cancellation_reason"
                            class="mt-2 text-xs text-rose-600"
                        >
                            {{ cancelForm.errors.cancellation_reason }}
                        </p>

                        <div class="mt-5 flex justify-end gap-2">
                            <button
                                type="button"
                                class="h-10 rounded-xl px-4 text-sm font-semibold text-slate-500 hover:bg-slate-100"
                                @click="closeModal"
                            >
                                Retour
                            </button>

                            <button
                                type="submit"
                                :disabled="cancelForm.processing"
                                class="h-10 rounded-xl bg-slate-700 px-5 text-sm font-bold text-white hover:bg-slate-800 disabled:opacity-50"
                            >
                                Annuler le rendez-vous
                            </button>
                        </div>
                    </form>
                </template>

                <!-- ================= COMPLETE ================= -->

                <template v-else-if="activeModal === 'complete'">
                    <div class="p-6 text-center">
                        <div
                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30"
                        >
                            <CheckCircle2 class="size-7" />
                        </div>

                        <h3 class="mt-4 text-base font-bold">
                            Rendez-vous effectué ?
                        </h3>

                        <p
                            class="mx-auto mt-2 max-w-sm text-sm leading-6 text-slate-500"
                        >
                            Confirmez que ce rendez-vous a bien eu lieu. Son
                            statut passera à « Effectué ».
                        </p>

                        <div class="mt-6 flex justify-center gap-2">
                            <button
                                type="button"
                                class="h-10 rounded-xl px-4 text-sm font-semibold text-slate-500 hover:bg-slate-100 dark:hover:bg-muted"
                                @click="closeModal"
                            >
                                Retour
                            </button>

                            <button
                                type="button"
                                class="inline-flex h-10 items-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white hover:bg-emerald-700"
                                @click="submitComplete"
                            >
                                <Check class="size-4" />

                                Oui, effectué
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </main>
</template>
