<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
}

interface Status {
    id: number;
    name: string;
    code: string;
}

interface User {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
}

interface SupportTeam {
    id: number;
    name: string;
}

interface Ticket {
    id: number;
    reference: string;
    subject: string;
    priority: string;
    created_at: string;

    category?: Category | null;
    status?: Status | null;
    support_team?: SupportTeam | null;
    assigned_to?: User | null;
    creator?: User | null;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface TicketsPagination {
    data: Ticket[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface Environment {
    id: number;
    name: string;
    code: string;
}

const props = defineProps<{
    tickets: TicketsPagination;
    environment: Environment;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tickets',
                href: '/tickets',
            },
        ],
    },
});

const priorityLabel = (priority: string): string => {
    const labels: Record<string, string> = {
        low: 'Faible',
        normal: 'Normale',
        high: 'Haute',
        urgent: 'Urgente',
    };

    return labels[priority] ?? priority;
};

const priorityClass = (priority: string): string => {
    const classes: Record<string, string> = {
        low: 'bg-slate-100 text-slate-700 ring-slate-200',
        normal: 'bg-blue-50 text-blue-700 ring-blue-200',
        high: 'bg-amber-50 text-amber-700 ring-amber-200',
        urgent: 'bg-red-50 text-red-700 ring-red-200',
    };

    return classes[priority] ?? classes.normal;
};

const statusClass = (status?: Status | null): string => {
    const code = status?.code?.toUpperCase() ?? '';

    if (
        [
            'OPEN',
            'OUVERT',
            'NEW',
        ].includes(code)
    ) {
        return 'bg-emerald-50 text-emerald-700 ring-emerald-200';
    }

    if (
        [
            'IN_PROGRESS',
            'IN-PROGRESS',
            'PROCESSING',
            'EN_COURS',
        ].includes(code)
    ) {
        return 'bg-blue-50 text-blue-700 ring-blue-200';
    }

    if (
        [
            'PENDING',
            'WAITING',
            'ON_HOLD',
        ].includes(code)
    ) {
        return 'bg-amber-50 text-amber-700 ring-amber-200';
    }

    if (
        [
            'RESOLVED',
            'RESOLU',
        ].includes(code)
    ) {
        return 'bg-violet-50 text-violet-700 ring-violet-200';
    }

    if (
        [
            'CLOSED',
            'CLOTURE',
            'CLOSED_TICKET',
        ].includes(code)
    ) {
        return 'bg-slate-100 text-slate-600 ring-slate-200';
    }

    return 'bg-slate-100 text-slate-700 ring-slate-200';
};

const userName = (user?: User | null): string => {
    if (!user) {
        return 'Non affecté';
    }

    const fullName = [
        user.first_name,
        user.last_name,
    ]
        .filter(Boolean)
        .join(' ')
        .trim();

    return fullName || user.name;
};

const initials = (user?: User | null): string => {
    if (!user) {
        return '?';
    }

    const first = user.first_name?.charAt(0) ?? '';
    const last = user.last_name?.charAt(0) ?? '';

    if (first || last) {
        return `${first}${last}`.toUpperCase();
    }

    return user.name
        .split(' ')
        .slice(0, 2)
        .map((part) => part.charAt(0))
        .join('')
        .toUpperCase();
};

const formatDate = (date: string): string => {
    return new Intl.DateTimeFormat('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(date));
};

const countByStatus = (...codes: string[]): number => {
    const normalizedCodes = codes.map((code) =>
        code.toUpperCase(),
    );

    return props.tickets.data.filter((ticket) =>
        normalizedCodes.includes(
            ticket.status?.code?.toUpperCase() ?? '',
        ),
    ).length;
};

const openCount = () =>
    countByStatus('OPEN', 'OUVERT', 'NEW');

const progressCount = () =>
    countByStatus(
        'IN_PROGRESS',
        'IN-PROGRESS',
        'PROCESSING',
        'EN_COURS',
    );

const closedCount = () =>
    countByStatus(
        'CLOSED',
        'CLOTURE',
        'CLOSED_TICKET',
    );
</script>

<template>
    <Head title="Tickets" />

    <div class="min-h-full bg-slate-50/60 p-4 md:p-6">
        <div class="mx-auto flex max-w-[1600px] flex-col gap-5">

            <!-- HEADER -->
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <div class="mb-1 flex items-center gap-2">
                        <span
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white shadow-sm"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                class="h-4 w-4"
                                stroke-width="2"
                            >
                                <path
                                    d="M4 4h16v12H5.17L4 17.17V4Z"
                                />
                                <path d="M8 8h8" />
                                <path d="M8 12h5" />
                            </svg>
                        </span>

                        <div>
                            <h1
                                class="text-xl font-bold tracking-tight text-slate-900 md:text-2xl"
                            >
                                Gestion des tickets
                            </h1>
                        </div>
                    </div>

                    <p class="ml-11 text-sm text-slate-500">
                        Suivi des demandes de support —
                        <span class="font-semibold text-slate-700">
                            {{ environment.name }}
                        </span>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="hidden rounded-lg border border-slate-200 bg-white px-3 py-2 sm:block"
                    >
                        <p
                            class="text-[10px] font-semibold uppercase tracking-wider text-slate-400"
                        >
                            Établissement
                        </p>

                        <p
                            class="text-sm font-semibold text-slate-700"
                        >
                            {{ environment.code }}
                        </p>
                    </div>

                    <Link
                        href="/tickets/create"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-slate-900 px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-4 w-4"
                            stroke-width="2"
                        >
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>

                        Nouveau ticket
                    </Link>
                </div>
            </div>

            <!-- KPI -->
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <!-- Total -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-medium text-slate-500"
                            >
                                Total des tickets
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-slate-900"
                            >
                                {{ tickets.total }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                class="h-5 w-5"
                                stroke-width="2"
                            >
                                <path
                                    d="M4 4h16v12H5.17L4 17.17V4Z"
                                />
                                <path d="M8 8h8" />
                                <path d="M8 12h5" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Ouverts -->
                <div
                    class="rounded-xl border border-emerald-100 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-medium text-slate-500"
                            >
                                Tickets ouverts
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-emerald-600"
                            >
                                {{ openCount() }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600"
                        >
                            <span
                                class="h-2.5 w-2.5 rounded-full bg-emerald-500"
                            ></span>
                        </div>
                    </div>
                </div>

                <!-- En cours -->
                <div
                    class="rounded-xl border border-blue-100 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-medium text-slate-500"
                            >
                                En cours
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-blue-600"
                            >
                                {{ progressCount() }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-600"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                class="h-5 w-5"
                                stroke-width="2"
                            >
                                <path d="M12 6v6l4 2" />
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Clôturés -->
                <div
                    class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p
                                class="text-xs font-medium text-slate-500"
                            >
                                Tickets clôturés
                            </p>

                            <p
                                class="mt-1 text-2xl font-bold text-slate-700"
                            >
                                {{ closedCount() }}
                            </p>
                        </div>

                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-600"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                class="h-5 w-5"
                                stroke-width="2"
                            >
                                <path d="m5 12 4 4L19 6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
            >
                <!-- Table header -->
                <div
                    class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-base font-bold text-slate-900"
                        >
                            Liste des tickets
                        </h2>

                        <p class="mt-0.5 text-xs text-slate-500">
                            Tous les tickets de
                            {{ environment.name }}
                        </p>
                    </div>

                    <div
                        class="inline-flex w-fit items-center gap-2 rounded-lg bg-slate-100 px-3 py-2 text-xs font-medium text-slate-600"
                    >
                        <span
                            class="h-2 w-2 rounded-full bg-emerald-500"
                        ></span>

                        {{ tickets.total }} ticket(s)
                    </div>
                </div>

                <!-- EMPTY -->
                <div
                    v-if="tickets.data.length === 0"
                    class="flex min-h-[300px] flex-col items-center justify-center px-6 py-12 text-center"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-600"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                            stroke-width="2"
                        >
                            <path d="m5 12 4 4L19 6" />
                        </svg>
                    </div>

                    <h3
                        class="mt-4 font-semibold text-slate-900"
                    >
                        Aucun ticket
                    </h3>

                    <p
                        class="mt-1 max-w-sm text-sm text-slate-500"
                    >
                        Aucun ticket n'est disponible pour cet
                        établissement.
                    </p>

                    <Link
                        href="/tickets/create"
                        class="mt-5 inline-flex h-9 items-center rounded-lg bg-slate-900 px-4 text-sm font-medium text-white hover:bg-slate-800"
                    >
                        Créer un ticket
                    </Link>
                </div>

                <!-- TABLE -->
                <div v-else class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr
                                class="border-b border-slate-200 bg-slate-50/80"
                            >
                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Référence
                                </th>

                                <th
                                    class="min-w-[260px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Ticket
                                </th>

                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Catégorie
                                </th>

                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Priorité
                                </th>

                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Statut
                                </th>

                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Affectation
                                </th>

                                <th
                                    class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
                                >
                                    Date
                                </th>

                                <th class="w-16 px-5 py-3"></th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="ticket in tickets.data"
                                :key="ticket.id"
                                class="group border-b border-slate-100 transition hover:bg-slate-50/80 last:border-0"
                            >
                                <!-- Ref -->
                                <td
                                    class="whitespace-nowrap px-5 py-4"
                                >
                                    <Link
                                        :href="`/tickets/${ticket.id}`"
                                        class="font-semibold text-slate-900 transition hover:text-blue-600"
                                    >
                                        {{ ticket.reference }}
                                    </Link>
                                </td>

                                <!-- Ticket -->
                                <td class="px-5 py-4">
                                    <Link
                                        :href="`/tickets/${ticket.id}`"
                                        class="font-semibold text-slate-800 transition hover:text-blue-600"
                                    >
                                        {{ ticket.subject }}
                                    </Link>

                                    <p
                                        class="mt-1 text-xs text-slate-400"
                                    >
                                        Créé par
                                        {{
                                            userName(
                                                ticket.creator,
                                            )
                                        }}
                                    </p>
                                </td>

                                <!-- Category -->
                                <td
                                    class="whitespace-nowrap px-5 py-4"
                                >
                                    <span
                                        class="inline-flex rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-600"
                                    >
                                        {{
                                            ticket.category?.name ??
                                            '—'
                                        }}
                                    </span>
                                </td>

                                <!-- Priority -->
                                <td
                                    class="whitespace-nowrap px-5 py-4"
                                >
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
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

                                <!-- Status -->
                                <td
                                    class="whitespace-nowrap px-5 py-4"
                                >
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"
                                        :class="
                                            statusClass(
                                                ticket.status,
                                            )
                                        "
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full bg-current"
                                        ></span>

                                        {{
                                            ticket.status?.name ??
                                            '—'
                                        }}
                                    </span>
                                </td>

                                <!-- Assignee -->
                                <td
                                    class="whitespace-nowrap px-5 py-4"
                                >
                                    <div
                                        v-if="ticket.assigned_to"
                                        class="flex items-center gap-2"
                                    >
                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900 text-[10px] font-bold text-white"
                                        >
                                            {{
                                                initials(
                                                    ticket.assigned_to,
                                                )
                                            }}
                                        </div>

                                        <span
                                            class="font-medium text-slate-700"
                                        >
                                            {{
                                                userName(
                                                    ticket.assigned_to,
                                                )
                                            }}
                                        </span>
                                    </div>

                                    <span
                                        v-else
                                        class="text-xs text-slate-400"
                                    >
                                        Non affecté
                                    </span>
                                </td>

                                <!-- Date -->
                                <td
                                    class="whitespace-nowrap px-5 py-4 text-xs text-slate-500"
                                >
                                    {{
                                        formatDate(
                                            ticket.created_at,
                                        )
                                    }}
                                </td>

                                <!-- Action -->
                                <td class="px-5 py-4 text-right">
                                    <Link
                                        :href="`/tickets/${ticket.id}`"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-900"
                                        title="Voir le ticket"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            class="h-4 w-4"
                                            stroke-width="2"
                                        >
                                            <path
                                                d="m9 18 6-6-6-6"
                                            />
                                        </svg>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- PAGINATION -->
                <div
                    v-if="tickets.last_page > 1"
                    class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <p class="text-xs text-slate-500">
                        Affichage de {{ tickets.from }} à
                        {{ tickets.to }} sur
                        {{ tickets.total }} résultat(s)
                    </p>

                    <div class="flex flex-wrap gap-1">
                        <template
                            v-for="link in tickets.links"
                            :key="link.label"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="inline-flex h-8 min-w-8 items-center justify-center rounded-lg border border-slate-200 px-2.5 text-xs font-medium text-slate-600 transition hover:bg-slate-50"
                                :class="{
                                    '!border-slate-900 !bg-slate-900 !text-white':
                                        link.active,
                                }"
                            >
                                <span
                                    v-html="link.label"
                                ></span>
                            </Link>

                            <span
                                v-else
                                class="inline-flex h-8 min-w-8 cursor-not-allowed items-center justify-center rounded-lg border border-slate-100 px-2.5 text-xs text-slate-300"
                                v-html="link.label"
                            ></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>