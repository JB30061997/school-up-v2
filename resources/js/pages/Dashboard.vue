<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    CalendarDays,
    CheckCircle2,
    ChevronRight,
    CircleAlert,
    Clock3,
    ClipboardList,
    Headphones,
    Inbox,
    MessageSquareText,
    Plus,
    School,
    Sparkles,
    Ticket,
    TrendingUp,
    UserCheck,
} from '@lucide/vue';

import { dashboard } from '@/routes';

/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tableau de bord',
                href: dashboard(),
            },
        ],
    },
});

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type DashboardUser = {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
};

type Environment = {
    id: number;
    name: string;
    code: string;
    app_name?: string | null;
    current_exercise?: string | null;
    primary_color?: string | null;
    secondary_color?: string | null;
    accent_color?: string | null;
};

type TicketStats = {
    total: number;
    open: number;
    in_progress: number;
    pending: number;
    resolved: number;
    closed: number;
};

type RequestStats = {
    total: number;
    pending: number;
    in_progress: number;
    completed: number;
};

type AppointmentStats = {
    total: number;
    pending: number;
    confirmed: number;
    completed: number;
    cancelled: number;
};

type DashboardStats = {
    tickets: TicketStats;
    requests: RequestStats;
    appointments: AppointmentStats;
};

type RecentTicket = {
    id: number;
    reference: string;
    subject: string;
    priority: string;
    created_at: string | null;
    category?: {
        id: number;
        name: string;
    } | null;
    status?: {
        id: number;
        name: string;
        code: string;
    } | null;
    creator?: {
        id: number;
        name: string;
    } | null;
    assigned_to?: {
        id: number;
        name: string;
    } | null;
};

type UpcomingAppointment = {
    id: number;
    reference: string;
    title: string;
    starts_at: string | null;
    ends_at?: string | null;
    location?: string | null;
    status: string;
    assigned_to?: {
        id: number;
        name: string;
    } | null;
};

type DashboardAuth = {
    user: DashboardUser | null;
};

type DashboardPageProps = {
    auth: DashboardAuth;
    currentEnvironment: Environment | null;
};

type DashboardProps = {
    stats?: Partial<DashboardStats>;
    recentTickets?: RecentTicket[];
    upcomingAppointments?: UpcomingAppointment[];
};

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = withDefaults(defineProps<DashboardProps>(), {
    stats: () => ({}),
    recentTickets: () => [],
    upcomingAppointments: () => [],
});

/*
|--------------------------------------------------------------------------
| Inertia page
|--------------------------------------------------------------------------
*/

const page = usePage<DashboardPageProps>();

const auth = computed<DashboardAuth>(() => page.props.auth);

const currentEnvironment = computed<Environment | null>(
    () => page.props.currentEnvironment,
);

/*
|--------------------------------------------------------------------------
| Safe statistics
|--------------------------------------------------------------------------
*/

const ticketStats = computed<TicketStats>(() => ({
    total: props.stats?.tickets?.total ?? 0,
    open: props.stats?.tickets?.open ?? 0,
    in_progress: props.stats?.tickets?.in_progress ?? 0,
    pending: props.stats?.tickets?.pending ?? 0,
    resolved: props.stats?.tickets?.resolved ?? 0,
    closed: props.stats?.tickets?.closed ?? 0,
}));

const requestStats = computed<RequestStats>(() => ({
    total: props.stats?.requests?.total ?? 0,
    pending: props.stats?.requests?.pending ?? 0,
    in_progress: props.stats?.requests?.in_progress ?? 0,
    completed: props.stats?.requests?.completed ?? 0,
}));

const appointmentStats = computed<AppointmentStats>(() => ({
    total: props.stats?.appointments?.total ?? 0,
    pending: props.stats?.appointments?.pending ?? 0,
    confirmed: props.stats?.appointments?.confirmed ?? 0,
    completed: props.stats?.appointments?.completed ?? 0,
    cancelled: props.stats?.appointments?.cancelled ?? 0,
}));

/*
|--------------------------------------------------------------------------
| User
|--------------------------------------------------------------------------
*/

const userDisplayName = computed<string>(() => {
    const user = auth.value.user;

    if (!user) {
        return 'Utilisateur';
    }

    const fullName = [user.first_name, user.last_name]
        .filter(Boolean)
        .join(' ')
        .trim();

    return fullName || user.name || 'Utilisateur';
});

const userFirstName = computed<string>(() => {
    const user = auth.value.user;

    if (!user) {
        return 'Utilisateur';
    }

    if (user.first_name) {
        return user.first_name;
    }

    return user.name?.split(' ')[0] || 'Utilisateur';
});

/*
|--------------------------------------------------------------------------
| Global indicators
|--------------------------------------------------------------------------
*/

const totalActivities = computed<number>(() => {
    return (
        ticketStats.value.total +
        requestStats.value.total +
        appointmentStats.value.total
    );
});

const activeActivities = computed<number>(() => {
    return (
        ticketStats.value.open +
        ticketStats.value.in_progress +
        ticketStats.value.pending +
        requestStats.value.pending +
        requestStats.value.in_progress +
        appointmentStats.value.pending +
        appointmentStats.value.confirmed
    );
});

const completedActivities = computed<number>(() => {
    return (
        ticketStats.value.resolved +
        ticketStats.value.closed +
        requestStats.value.completed +
        appointmentStats.value.completed
    );
});

const resolutionRate = computed<number>(() => {
    if (totalActivities.value === 0) {
        return 0;
    }

    return Math.round(
        (completedActivities.value / totalActivities.value) * 100,
    );
});

/*
|--------------------------------------------------------------------------
| Ticket progress
|--------------------------------------------------------------------------
*/

const ticketProgress = computed<number>(() => {
    if (ticketStats.value.total === 0) {
        return 0;
    }

    const done =
        ticketStats.value.resolved +
        ticketStats.value.closed;

    return Math.min(
        100,
        Math.round((done / ticketStats.value.total) * 100),
    );
});

/*
|--------------------------------------------------------------------------
| Request progress
|--------------------------------------------------------------------------
*/

const requestProgress = computed<number>(() => {
    if (requestStats.value.total === 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.round(
            (requestStats.value.completed /
                requestStats.value.total) *
                100,
        ),
    );
});

/*
|--------------------------------------------------------------------------
| Appointment progress
|--------------------------------------------------------------------------
*/

const appointmentProgress = computed<number>(() => {
    if (appointmentStats.value.total === 0) {
        return 0;
    }

    return Math.min(
        100,
        Math.round(
            (appointmentStats.value.completed /
                appointmentStats.value.total) *
                100,
        ),
    );
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const formatDate = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
};

const formatDateTime = (
    value: string | null | undefined,
): string => {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
};

const priorityLabel = (priority: string): string => {
    const labels: Record<string, string> = {
        low: 'Faible',
        LOW: 'Faible',
        normal: 'Normale',
        NORMAL: 'Normale',
        medium: 'Moyenne',
        MEDIUM: 'Moyenne',
        high: 'Haute',
        HIGH: 'Haute',
        urgent: 'Urgente',
        URGENT: 'Urgente',
        critical: 'Critique',
        CRITICAL: 'Critique',
    };

    return labels[priority] ?? priority;
};

const priorityClass = (priority: string): string => {
    const normalized = priority.toLowerCase();

    if (
        normalized === 'urgent' ||
        normalized === 'critical'
    ) {
        return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300';
    }

    if (normalized === 'high') {
        return 'border-orange-200 bg-orange-50 text-orange-700 dark:border-orange-900/60 dark:bg-orange-950/40 dark:text-orange-300';
    }

    if (
        normalized === 'medium' ||
        normalized === 'normal'
    ) {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300';
    }

    return 'border-slate-200 bg-slate-50 text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300';
};

const statusClass = (
    code: string | undefined,
): string => {
    switch (code) {
        case 'OPEN':
            return 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-900/60 dark:bg-sky-950/40 dark:text-sky-300';

        case 'IN_PROGRESS':
            return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300';

        case 'PENDING':
            return 'border-violet-200 bg-violet-50 text-violet-700 dark:border-violet-900/60 dark:bg-violet-950/40 dark:text-violet-300';

        case 'RESOLVED':
        case 'CLOSED':
            return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300';

        case 'CANCELLED':
            return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300';

        default:
            return 'border-border bg-muted text-muted-foreground';
    }
};

const appointmentStatusLabel = (
    status: string,
): string => {
    const labels: Record<string, string> = {
        pending: 'En attente',
        PENDING: 'En attente',
        confirmed: 'Confirmé',
        CONFIRMED: 'Confirmé',
        completed: 'Terminé',
        COMPLETED: 'Terminé',
        cancelled: 'Annulé',
        CANCELLED: 'Annulé',
        rejected: 'Refusé',
        REJECTED: 'Refusé',
    };

    return labels[status] ?? status;
};

const appointmentStatusClass = (
    status: string,
): string => {
    const normalized = status.toLowerCase();

    if (
        normalized === 'confirmed' ||
        normalized === 'completed'
    ) {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300';
    }

    if (normalized === 'pending') {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300';
    }

    if (
        normalized === 'cancelled' ||
        normalized === 'rejected'
    ) {
        return 'border-red-200 bg-red-50 text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300';
    }

    return 'border-border bg-muted text-muted-foreground';
};
</script>

<template>
    <Head title="Tableau de bord" />

    <div
        class="min-h-full bg-slate-50/60 dark:bg-background"
    >
        <div
            class="mx-auto flex w-full max-w-[1800px] flex-col gap-6 p-4 sm:p-6 lg:p-8"
        >
            <!-- ===================================================== -->
            <!-- HERO -->
            <!-- ===================================================== -->

            <section
                class="relative overflow-hidden rounded-3xl border border-border bg-card shadow-sm"
            >
                <div
                    class="absolute -right-20 -top-24 size-72 rounded-full bg-emerald-500/10 blur-3xl"
                />

                <div
                    class="absolute -bottom-32 left-1/3 size-80 rounded-full bg-sky-500/10 blur-3xl"
                />

                <div
                    class="relative flex flex-col gap-6 p-6 lg:flex-row lg:items-center lg:justify-between lg:p-8"
                >
                    <div class="min-w-0">
                        <div
                            class="mb-4 flex flex-wrap items-center gap-2"
                        >
                            <span
                                class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
                            >
                                <Sparkles class="size-3.5" />
                                School Up
                            </span>

                            <span
                                v-if="
                                    currentEnvironment?.current_exercise
                                "
                                class="inline-flex items-center rounded-full border border-border bg-background px-3 py-1 text-xs font-medium text-muted-foreground"
                            >
                                {{
                                    currentEnvironment.current_exercise
                                }}
                            </span>
                        </div>

                        <h1
                            class="text-2xl font-black tracking-tight text-foreground sm:text-3xl lg:text-4xl"
                        >
                            Bonjour
                            {{ userFirstName }}
                            👋
                        </h1>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-muted-foreground sm:text-base"
                        >
                            Voici un aperçu de l'activité de
                            <strong
                                class="font-semibold text-foreground"
                            >
                                {{
                                    currentEnvironment?.name ??
                                    'votre établissement'
                                }}
                            </strong>.
                            Suivez vos réclamations, demandes
                            internes et rendez-vous depuis un seul
                            espace.
                        </p>
                    </div>

                    <div
                        class="flex flex-col gap-3 sm:flex-row lg:justify-end"
                    >
                        <Link
                            href="/tickets/create"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-foreground px-5 text-sm font-semibold text-background shadow-sm transition hover:opacity-90"
                        >
                            <Plus class="size-4" />
                            Nouvelle réclamation
                        </Link>

                        <Link
                            href="/appointments/create"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-border bg-background px-5 text-sm font-semibold text-foreground shadow-sm transition hover:bg-muted"
                        >
                            <CalendarDays class="size-4" />
                            Nouveau rendez-vous
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- KPI -->
            <!-- ===================================================== -->

            <section
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <!-- Réclamations -->

                <article
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Réclamations
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight"
                            >
                                {{ ticketStats.total }}
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                        >
                            <MessageSquareText
                                class="size-5"
                            />
                        </div>
                    </div>

                    <div class="mt-5">
                        <div
                            class="mb-2 flex items-center justify-between text-xs"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Traitement
                            </span>

                            <span class="font-bold">
                                {{ ticketProgress }}%
                            </span>
                        </div>

                        <div
                            class="h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-emerald-500 transition-all"
                                :style="{
                                    width: `${ticketProgress}%`,
                                }"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-4 flex items-center gap-4 text-xs text-muted-foreground"
                    >
                        <span>
                            <strong
                                class="text-foreground"
                            >
                                {{ ticketStats.open }}
                            </strong>
                            ouvertes
                        </span>

                        <span>
                            <strong
                                class="text-foreground"
                            >
                                {{ ticketStats.in_progress }}
                            </strong>
                            en cours
                        </span>
                    </div>
                </article>

                <!-- Demandes -->

                <article
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Demandes internes
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight"
                            >
                                {{ requestStats.total }}
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400"
                        >
                            <ClipboardList
                                class="size-5"
                            />
                        </div>
                    </div>

                    <div class="mt-5">
                        <div
                            class="mb-2 flex items-center justify-between text-xs"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Terminées
                            </span>

                            <span class="font-bold">
                                {{ requestProgress }}%
                            </span>
                        </div>

                        <div
                            class="h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-sky-500 transition-all"
                                :style="{
                                    width: `${requestProgress}%`,
                                }"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-4 flex items-center gap-4 text-xs text-muted-foreground"
                    >
                        <span>
                            <strong
                                class="text-foreground"
                            >
                                {{ requestStats.pending }}
                            </strong>
                            en attente
                        </span>

                        <span>
                            <strong
                                class="text-foreground"
                            >
                                {{ requestStats.in_progress }}
                            </strong>
                            en cours
                        </span>
                    </div>
                </article>

                <!-- RDV -->

                <article
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Rendez-vous
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight"
                            >
                                {{ appointmentStats.total }}
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400"
                        >
                            <CalendarDays
                                class="size-5"
                            />
                        </div>
                    </div>

                    <div class="mt-5">
                        <div
                            class="mb-2 flex items-center justify-between text-xs"
                        >
                            <span
                                class="text-muted-foreground"
                            >
                                Réalisés
                            </span>

                            <span class="font-bold">
                                {{ appointmentProgress }}%
                            </span>
                        </div>

                        <div
                            class="h-1.5 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full bg-violet-500 transition-all"
                                :style="{
                                    width: `${appointmentProgress}%`,
                                }"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-4 flex items-center gap-4 text-xs text-muted-foreground"
                    >
                        <span>
                            <strong
                                class="text-foreground"
                            >
                                {{ appointmentStats.confirmed }}
                            </strong>
                            confirmés
                        </span>

                        <span>
                            <strong
                                class="text-foreground"
                            >
                                {{ appointmentStats.pending }}
                            </strong>
                            en attente
                        </span>
                    </div>
                </article>

                <!-- Activity -->

                <article
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div>
                            <p
                                class="text-sm font-medium text-muted-foreground"
                            >
                                Activités actives
                            </p>

                            <p
                                class="mt-2 text-3xl font-black tracking-tight"
                            >
                                {{ activeActivities }}
                            </p>
                        </div>

                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400"
                        >
                            <TrendingUp
                                class="size-5"
                            />
                        </div>
                    </div>

                    <div
                        class="mt-5 flex items-end justify-between"
                    >
                        <div>
                            <p
                                class="text-xs text-muted-foreground"
                            >
                                Taux de résolution
                            </p>

                            <p
                                class="mt-1 text-xl font-black"
                            >
                                {{ resolutionRate }}%
                            </p>
                        </div>

                        <div
                            class="flex size-12 items-center justify-center rounded-full border-4 border-emerald-100 text-xs font-black text-emerald-600 dark:border-emerald-950"
                        >
                            {{ resolutionRate }}%
                        </div>
                    </div>
                </article>
            </section>

            <!-- ===================================================== -->
            <!-- MAIN CONTENT -->
            <!-- ===================================================== -->

            <section
                class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(320px,0.7fr)]"
            >
                <!-- RECENT TICKETS -->

                <div
                    class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
                >
                    <div
                        class="flex flex-col gap-3 border-b border-border p-5 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <div
                                class="flex items-center gap-2"
                            >
                                <div
                                    class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                                >
                                    <Ticket
                                        class="size-4"
                                    />
                                </div>

                                <div>
                                    <h2
                                        class="font-bold text-foreground"
                                    >
                                        Réclamations récentes
                                    </h2>

                                    <p
                                        class="text-xs text-muted-foreground"
                                    >
                                        Dernières demandes de
                                        support
                                    </p>
                                </div>
                            </div>
                        </div>

                        <Link
                            href="/tickets"
                            class="inline-flex items-center gap-1 text-sm font-semibold text-foreground transition hover:opacity-70"
                        >
                            Tout afficher
                            <ArrowRight
                                class="size-4"
                            />
                        </Link>
                    </div>

                    <!-- TABLE -->

                    <div
                        v-if="recentTickets.length > 0"
                        class="overflow-x-auto"
                    >
                        <table
                            class="w-full min-w-[800px]"
                        >
                            <thead>
                                <tr
                                    class="border-b border-border bg-muted/30 text-left"
                                >
                                    <th
                                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Référence
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Réclamation
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Priorité
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Statut
                                    </th>

                                    <th
                                        class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-muted-foreground"
                                    >
                                        Date
                                    </th>

                                    <th
                                        class="w-12 px-5 py-3"
                                    />
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-border"
                            >
                                <tr
                                    v-for="ticket in recentTickets"
                                    :key="ticket.id"
                                    class="group transition hover:bg-muted/30"
                                >
                                    <td
                                        class="whitespace-nowrap px-5 py-4"
                                    >
                                        <span
                                            class="text-sm font-bold text-foreground"
                                        >
                                            {{
                                                ticket.reference
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="px-5 py-4"
                                    >
                                        <div
                                            class="max-w-[320px]"
                                        >
                                            <p
                                                class="truncate text-sm font-semibold text-foreground"
                                            >
                                                {{
                                                    ticket.subject
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 truncate text-xs text-muted-foreground"
                                            >
                                                {{
                                                    ticket.category
                                                        ?.name ??
                                                    'Sans catégorie'
                                                }}
                                            </p>
                                        </div>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4"
                                    >
                                        <span
                                            class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                priorityClass(
                                                    ticket.priority,
                                                )
                                            "
                                        >
                                            {{
                                                priorityLabel(
                                                    ticket.priority,
                                                )
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4"
                                    >
                                        <span
                                            class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                statusClass(
                                                    ticket.status
                                                        ?.code,
                                                )
                                            "
                                        >
                                            {{
                                                ticket.status
                                                    ?.name ??
                                                '—'
                                            }}
                                        </span>
                                    </td>

                                    <td
                                        class="whitespace-nowrap px-5 py-4 text-sm text-muted-foreground"
                                    >
                                        {{
                                            formatDate(
                                                ticket.created_at,
                                            )
                                        }}
                                    </td>

                                    <td
                                        class="px-5 py-4 text-right"
                                    >
                                        <Link
                                            :href="`/tickets/${ticket.id}`"
                                            class="inline-flex size-8 items-center justify-center rounded-lg text-muted-foreground transition hover:bg-muted hover:text-foreground"
                                        >
                                            <ChevronRight
                                                class="size-4"
                                            />
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- EMPTY -->

                    <div
                        v-else
                        class="flex min-h-[280px] flex-col items-center justify-center p-8 text-center"
                    >
                        <div
                            class="flex size-14 items-center justify-center rounded-2xl bg-muted"
                        >
                            <Inbox
                                class="size-6 text-muted-foreground"
                            />
                        </div>

                        <h3
                            class="mt-4 font-bold text-foreground"
                        >
                            Aucune réclamation
                        </h3>

                        <p
                            class="mt-1 max-w-sm text-sm text-muted-foreground"
                        >
                            Les dernières réclamations de
                            l'établissement apparaîtront ici.
                        </p>

                        <Link
                            href="/tickets/create"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl bg-foreground px-4 py-2.5 text-sm font-semibold text-background"
                        >
                            <Plus class="size-4" />
                            Créer une réclamation
                        </Link>
                    </div>
                </div>

                <!-- UPCOMING APPOINTMENTS -->

                <div
                    class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-border p-5"
                    >
                        <div
                            class="flex items-center gap-3"
                        >
                            <div
                                class="flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400"
                            >
                                <CalendarDays
                                    class="size-4"
                                />
                            </div>

                            <div>
                                <h2
                                    class="font-bold"
                                >
                                    Prochains rendez-vous
                                </h2>

                                <p
                                    class="text-xs text-muted-foreground"
                                >
                                    Planning à venir
                                </p>
                            </div>
                        </div>

                        <Link
                            href="/appointments"
                            class="flex size-8 items-center justify-center rounded-lg transition hover:bg-muted"
                        >
                            <ArrowRight
                                class="size-4"
                            />
                        </Link>
                    </div>

                    <div
                        v-if="
                            upcomingAppointments.length >
                            0
                        "
                        class="divide-y divide-border"
                    >
                        <Link
                            v-for="appointment in upcomingAppointments"
                            :key="appointment.id"
                            :href="`/appointments/${appointment.id}`"
                            class="flex gap-4 p-5 transition hover:bg-muted/30"
                        >
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400"
                            >
                                <CalendarDays
                                    class="size-5"
                                />
                            </div>

                            <div
                                class="min-w-0 flex-1"
                            >
                                <div
                                    class="flex items-start justify-between gap-2"
                                >
                                    <div
                                        class="min-w-0"
                                    >
                                        <p
                                            class="truncate text-sm font-bold"
                                        >
                                            {{
                                                appointment.title
                                            }}
                                        </p>

                                        <p
                                            class="mt-1 text-xs text-muted-foreground"
                                        >
                                            {{
                                                appointment.reference
                                            }}
                                        </p>
                                    </div>

                                    <span
                                        class="shrink-0 rounded-full border px-2 py-1 text-[10px] font-bold"
                                        :class="
                                            appointmentStatusClass(
                                                appointment.status,
                                            )
                                        "
                                    >
                                        {{
                                            appointmentStatusLabel(
                                                appointment.status,
                                            )
                                        }}
                                    </span>
                                </div>

                                <div
                                    class="mt-3 flex items-center gap-2 text-xs text-muted-foreground"
                                >
                                    <Clock3
                                        class="size-3.5"
                                    />

                                    {{
                                        formatDateTime(
                                            appointment.starts_at,
                                        )
                                    }}
                                </div>

                                <p
                                    v-if="
                                        appointment.location
                                    "
                                    class="mt-1 truncate text-xs text-muted-foreground"
                                >
                                    {{
                                        appointment.location
                                    }}
                                </p>
                            </div>
                        </Link>
                    </div>

                    <div
                        v-else
                        class="flex min-h-[280px] flex-col items-center justify-center p-8 text-center"
                    >
                        <div
                            class="flex size-14 items-center justify-center rounded-2xl bg-muted"
                        >
                            <CalendarDays
                                class="size-6 text-muted-foreground"
                            />
                        </div>

                        <h3
                            class="mt-4 font-bold"
                        >
                            Aucun rendez-vous
                        </h3>

                        <p
                            class="mt-1 text-sm text-muted-foreground"
                        >
                            Aucun rendez-vous à venir pour le
                            moment.
                        </p>

                        <Link
                            href="/appointments/create"
                            class="mt-5 inline-flex items-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold shadow-sm transition hover:bg-muted"
                        >
                            <Plus class="size-4" />
                            Nouveau rendez-vous
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- SECOND ROW -->
            <!-- ===================================================== -->

            <section
                class="grid gap-6 lg:grid-cols-3"
            >
                <!-- STATUS -->

                <div
                    class="rounded-2xl border border-border bg-card p-5 shadow-sm"
                >
                    <div
                        class="mb-5 flex items-center justify-between"
                    >
                        <div>
                            <h2
                                class="font-bold"
                            >
                                État des réclamations
                            </h2>

                            <p
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Répartition actuelle
                            </p>
                        </div>

                        <Headphones
                            class="size-5 text-muted-foreground"
                        />
                    </div>

                    <div class="space-y-4">
                        <div
                            class="flex items-center justify-between"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="size-2.5 rounded-full bg-sky-500"
                                />

                                <span
                                    class="text-sm text-muted-foreground"
                                >
                                    Ouvertes
                                </span>
                            </div>

                            <strong>
                                {{ ticketStats.open }}
                            </strong>
                        </div>

                        <div
                            class="flex items-center justify-between"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="size-2.5 rounded-full bg-amber-500"
                                />

                                <span
                                    class="text-sm text-muted-foreground"
                                >
                                    En cours
                                </span>
                            </div>

                            <strong>
                                {{
                                    ticketStats.in_progress
                                }}
                            </strong>
                        </div>

                        <div
                            class="flex items-center justify-between"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="size-2.5 rounded-full bg-violet-500"
                                />

                                <span
                                    class="text-sm text-muted-foreground"
                                >
                                    En attente
                                </span>
                            </div>

                            <strong>
                                {{ ticketStats.pending }}
                            </strong>
                        </div>

                        <div
                            class="flex items-center justify-between"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="size-2.5 rounded-full bg-emerald-500"
                                />

                                <span
                                    class="text-sm text-muted-foreground"
                                >
                                    Résolues
                                </span>
                            </div>

                            <strong>
                                {{
                                    ticketStats.resolved +
                                    ticketStats.closed
                                }}
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- ACTIVITY -->

                <div
                    class="rounded-2xl border border-border bg-card p-5 shadow-sm"
                >
                    <div
                        class="mb-5 flex items-center justify-between"
                    >
                        <div>
                            <h2
                                class="font-bold"
                            >
                                Performance
                            </h2>

                            <p
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Vue globale de l'activité
                            </p>
                        </div>

                        <TrendingUp
                            class="size-5 text-muted-foreground"
                        />
                    </div>

                    <div
                        class="flex items-center gap-5"
                    >
                        <div
                            class="flex size-24 shrink-0 items-center justify-center rounded-full border-[8px] border-emerald-100 dark:border-emerald-950"
                        >
                            <div
                                class="text-center"
                            >
                                <p
                                    class="text-xl font-black"
                                >
                                    {{ resolutionRate }}%
                                </p>

                                <p
                                    class="text-[10px] text-muted-foreground"
                                >
                                    résolution
                                </p>
                            </div>
                        </div>

                        <div
                            class="min-w-0 flex-1 space-y-3"
                        >
                            <div
                                class="flex justify-between text-sm"
                            >
                                <span
                                    class="text-muted-foreground"
                                >
                                    Total
                                </span>

                                <strong>
                                    {{ totalActivities }}
                                </strong>
                            </div>

                            <div
                                class="flex justify-between text-sm"
                            >
                                <span
                                    class="text-muted-foreground"
                                >
                                    Actives
                                </span>

                                <strong>
                                    {{ activeActivities }}
                                </strong>
                            </div>

                            <div
                                class="flex justify-between text-sm"
                            >
                                <span
                                    class="text-muted-foreground"
                                >
                                    Terminées
                                </span>

                                <strong
                                    class="text-emerald-600"
                                >
                                    {{
                                        completedActivities
                                    }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SCHOOL -->

                <div
                    class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm"
                >
                    <div
                        class="absolute -right-10 -top-10 size-32 rounded-full bg-emerald-500/10 blur-2xl"
                    />

                    <div class="relative">
                        <div
                            class="mb-5 flex items-center justify-between"
                        >
                            <div>
                                <h2
                                    class="font-bold"
                                >
                                    Établissement
                                </h2>

                                <p
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    Environnement actuel
                                </p>
                            </div>

                            <School
                                class="size-5 text-muted-foreground"
                            />
                        </div>

                        <div
                            class="flex items-center gap-4"
                        >
                            <div
                                class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                            >
                                <School
                                    class="size-5"
                                />
                            </div>

                            <div class="min-w-0">
                                <p
                                    class="truncate font-bold"
                                >
                                    {{
                                        currentEnvironment?.name ??
                                        'Aucun établissement'
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-muted-foreground"
                                >
                                    {{
                                        currentEnvironment?.code ??
                                        '—'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="mt-5 rounded-xl bg-muted/50 p-4"
                        >
                            <div
                                class="flex items-center justify-between gap-4"
                            >
                                <span
                                    class="text-xs text-muted-foreground"
                                >
                                    Année scolaire
                                </span>

                                <span
                                    class="text-sm font-bold"
                                >
                                    {{
                                        currentEnvironment?.current_exercise ??
                                        'Non définie'
                                    }}
                                </span>
                            </div>
                        </div>

                        <Link
                            href="/select-environment"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-border bg-background px-4 py-2.5 text-sm font-semibold shadow-sm transition hover:bg-muted"
                        >
                            Changer d'établissement
                            <ChevronsRightIcon />
                        </Link>
                    </div>
                </div>
            </section>

            <!-- ===================================================== -->
            <!-- QUICK ACTIONS -->
            <!-- ===================================================== -->

            <section
                class="rounded-2xl border border-border bg-card p-5 shadow-sm"
            >
                <div
                    class="mb-5 flex items-center justify-between"
                >
                    <div>
                        <h2
                            class="font-bold"
                        >
                            Accès rapides
                        </h2>

                        <p
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            Accédez rapidement aux principales
                            fonctionnalités.
                        </p>
                    </div>
                </div>

                <div
                    class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <Link
                        href="/tickets/create"
                        class="group flex items-center gap-4 rounded-xl border border-border p-4 transition hover:border-emerald-200 hover:bg-emerald-50/50 dark:hover:border-emerald-900 dark:hover:bg-emerald-950/20"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400"
                        >
                            <MessageSquareText
                                class="size-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-bold"
                            >
                                Réclamation
                            </p>

                            <p
                                class="truncate text-xs text-muted-foreground"
                            >
                                Créer une réclamation
                            </p>
                        </div>

                        <ChevronRight
                            class="size-4 text-muted-foreground transition group-hover:translate-x-1"
                        />
                    </Link>

                    <Link
                        href="/requests/create"
                        class="group flex items-center gap-4 rounded-xl border border-border p-4 transition hover:bg-muted/40"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600 dark:bg-sky-950/40 dark:text-sky-400"
                        >
                            <ClipboardList
                                class="size-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-bold"
                            >
                                Demande
                            </p>

                            <p
                                class="truncate text-xs text-muted-foreground"
                            >
                                Nouvelle demande interne
                            </p>
                        </div>

                        <ChevronRight
                            class="size-4 text-muted-foreground transition group-hover:translate-x-1"
                        />
                    </Link>

                    <Link
                        href="/appointments/create"
                        class="group flex items-center gap-4 rounded-xl border border-border p-4 transition hover:bg-muted/40"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400"
                        >
                            <CalendarDays
                                class="size-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-bold"
                            >
                                Rendez-vous
                            </p>

                            <p
                                class="truncate text-xs text-muted-foreground"
                            >
                                Planifier un rendez-vous
                            </p>
                        </div>

                        <ChevronRight
                            class="size-4 text-muted-foreground transition group-hover:translate-x-1"
                        />
                    </Link>

                    <Link
                        href="/notifications"
                        class="group flex items-center gap-4 rounded-xl border border-border p-4 transition hover:bg-muted/40"
                    >
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400"
                        >
                            <CircleAlert
                                class="size-4"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="text-sm font-bold"
                            >
                                Notifications
                            </p>

                            <p
                                class="truncate text-xs text-muted-foreground"
                            >
                                Consulter les notifications
                            </p>
                        </div>

                        <ChevronRight
                            class="size-4 text-muted-foreground transition group-hover:translate-x-1"
                        />
                    </Link>
                </div>
            </section>

            <!-- FOOTER INFO -->

            <div
                class="flex flex-col gap-2 pb-2 text-xs text-muted-foreground sm:flex-row sm:items-center sm:justify-between"
            >
                <div
                    class="flex items-center gap-2"
                >
                    <CheckCircle2
                        class="size-3.5 text-emerald-500"
                    />

                    <span>
                        Connecté en tant que
                        <strong
                            class="font-semibold text-foreground"
                        >
                            {{ userDisplayName }}
                        </strong>
                    </span>
                </div>

                <span>
                    School Up • Gestion scolaire
                </span>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { ChevronsRight } from '@lucide/vue';

export default {
    components: {
        ChevronsRightIcon: ChevronsRight,
    },
};
</script>