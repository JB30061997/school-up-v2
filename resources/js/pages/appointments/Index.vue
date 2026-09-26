<script setup lang="ts">
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import {
    CalendarDays,
    CheckCircle2,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
    Circle,
    Clock3,
    Filter,
    List,
    MapPin,
    MoreHorizontal,
    Plus,
    RefreshCcw,
    Search,
    SlidersHorizontal,
    UserRound,
    Users,
    X,
    XCircle,
} from "@lucide/vue";
import { computed, ref } from "vue";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type Person = {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
};

type AppointmentType = {
    id: number;
    name: string;
    code: string;
    duration_minutes?: number | null;
};

type Appointment = {
    id: number;
    reference: string;
    title: string;
    description?: string | null;
    starts_at: string;
    ends_at: string;
    location?: string | null;
    status: string;
    type?: AppointmentType | null;
    creator?: Person | null;
    assigned_to?: Person | null;
    assignedTo?: Person | null;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedAppointments = {
    data: Appointment[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from?: number | null;
    to?: number | null;
    links: PaginationLink[];
};

type Filters = {
    search?: string | null;
    status?: string | null;
    appointment_type_id?: number | string | null;
    date_from?: string | null;
    date_to?: string | null;
    calendar_from?: string | null;
    calendar_to?: string | null;
};

const props = defineProps<{
    appointments: PaginatedAppointments;
    calendarAppointments: Appointment[];
    types: AppointmentType[];
    filters: Filters;
    statuses: Record<string, string>;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Rendez-vous",
                href: "/appointments",
            },
        ],
    },
});

const page = usePage();

/*
|--------------------------------------------------------------------------
| Permissions
|--------------------------------------------------------------------------
*/

const permissions = computed<string[]>(() => {
    const auth = page.props.auth as any;

    return auth?.permissions ?? [];
});

const canCreate = computed(() => {
    if (!permissions.value.length) {
        return true;
    }

    return (
        permissions.value.includes("appointments.create") ||
        permissions.value.includes("rdv.create")
    );
});

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const viewMode = ref<"calendar" | "list">("calendar");

const search = ref(props.filters.search ?? "");

const selectedStatus = ref(props.filters.status ?? "");

const selectedType = ref(
    props.filters.appointment_type_id
        ? String(props.filters.appointment_type_id)
        : "",
);

const currentDate = ref(
    props.filters.calendar_from
        ? new Date(`${props.filters.calendar_from}T12:00:00`)
        : new Date(),
);

const startHour = 7;
const endHour = 20;

const hourHeight = 76;

const hours = Array.from(
    {
        length: endHour - startHour + 1,
    },
    (_, index) => startHour + index,
);

const weekDaysNames = ["DIM", "LUN", "MAR", "MER", "JEU", "VEN", "SAM"];

const months = [
    "Janvier",
    "Février",
    "Mars",
    "Avril",
    "Mai",
    "Juin",
    "Juillet",
    "Août",
    "Septembre",
    "Octobre",
    "Novembre",
    "Décembre",
];

/*
|--------------------------------------------------------------------------
| Date helpers
|--------------------------------------------------------------------------
*/

const startOfWeek = computed(() => {
    const date = new Date(currentDate.value);

    const day = date.getDay();

    const difference = date.getDate() - day + (day === 0 ? -6 : 1);

    date.setDate(difference);

    date.setHours(0, 0, 0, 0);

    return date;
});

const weekDays = computed(() => {
    return Array.from({ length: 7 }, (_, index) => {
        const date = new Date(startOfWeek.value);

        date.setDate(date.getDate() + index);

        return date;
    });
});

const endOfWeek = computed(() => {
    const date = new Date(startOfWeek.value);

    date.setDate(date.getDate() + 6);

    date.setHours(23, 59, 59, 999);

    return date;
});

const monthTitle = computed(() => {
    const first = weekDays.value[0];
    const last = weekDays.value[6];

    if (first.getMonth() === last.getMonth()) {
        return `${months[first.getMonth()]} ${first.getFullYear()}`;
    }

    return `${months[first.getMonth()]} – ${
        months[last.getMonth()]
    } ${last.getFullYear()}`;
});

const toDateKey = (date: Date): string => {
    const year = date.getFullYear();

    const month = String(date.getMonth() + 1).padStart(2, "0");

    const day = String(date.getDate()).padStart(2, "0");

    return `${year}-${month}-${day}`;
};

const isToday = (date: Date): boolean => {
    const today = new Date();

    return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    );
};

const formatTime = (value: string): string => {
    return new Intl.DateTimeFormat("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(value));
};

const formatLongDate = (value: string): string => {
    return new Intl.DateTimeFormat("fr-FR", {
        weekday: "short",
        day: "2-digit",
        month: "short",
    }).format(new Date(value));
};

/*
|--------------------------------------------------------------------------
| Calendar appointments
|--------------------------------------------------------------------------
*/

const visibleAppointments = computed(() => {
    return props.calendarAppointments.filter((appointment) => {
        const date = new Date(appointment.starts_at);

        return date >= startOfWeek.value && date <= endOfWeek.value;
    });
});

const appointmentsForDay = (date: Date): Appointment[] => {
    const key = toDateKey(date);

    return visibleAppointments.value.filter(
        (appointment) => toDateKey(new Date(appointment.starts_at)) === key,
    );
};

/*
|--------------------------------------------------------------------------
| Event positioning
|--------------------------------------------------------------------------
*/

const eventStyle = (appointment: Appointment) => {
    const start = new Date(appointment.starts_at);

    const end = new Date(appointment.ends_at);

    const startMinutes = start.getHours() * 60 + start.getMinutes();

    const endMinutes = end.getHours() * 60 + end.getMinutes();

    const calendarStart = startHour * 60;

    const top = ((startMinutes - calendarStart) / 60) * hourHeight;

    const duration = Math.max(endMinutes - startMinutes, 30);

    const height = (duration / 60) * hourHeight;

    return {
        top: `${Math.max(top, 0)}px`,
        height: `${Math.max(height - 4, 38)}px`,
    };
};

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const statusLabel = (status: string): string => {
    return props.statuses[status] ?? status;
};

const statusClass = (status: string): string => {
    switch (status) {
        case "CONFIRMED":
            return "border-emerald-500/30 bg-emerald-500/10 text-emerald-950 dark:text-emerald-100";

        case "COMPLETED":
            return "border-blue-500/30 bg-blue-500/10 text-blue-950 dark:text-blue-100";

        case "REJECTED":
            return "border-rose-500/30 bg-rose-500/10 text-rose-950 dark:text-rose-100";

        case "CANCELLED":
            return "border-slate-400/30 bg-slate-500/10 text-slate-700 dark:text-slate-200";

        default:
            return "border-amber-500/30 bg-amber-500/10 text-amber-950 dark:text-amber-100";
    }
};

const statusDotClass = (status: string): string => {
    switch (status) {
        case "CONFIRMED":
            return "bg-emerald-500";

        case "COMPLETED":
            return "bg-blue-500";

        case "REJECTED":
            return "bg-rose-500";

        case "CANCELLED":
            return "bg-slate-400";

        default:
            return "bg-amber-500";
    }
};

/*
|--------------------------------------------------------------------------
| People
|--------------------------------------------------------------------------
*/

const personName = (person?: Person | null): string => {
    if (!person) {
        return "Non affecté";
    }

    const fullName = [person.first_name, person.last_name]
        .filter(Boolean)
        .join(" ");

    return fullName || person.name || "Utilisateur";
};

const assignedPerson = (appointment: Appointment) => {
    return appointment.assignedTo ?? appointment.assigned_to ?? null;
};

/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const totalWeek = computed(() => visibleAppointments.value.length);

const confirmedWeek = computed(
    () =>
        visibleAppointments.value.filter((item) => item.status === "CONFIRMED")
            .length,
);

const pendingWeek = computed(
    () =>
        visibleAppointments.value.filter((item) => item.status === "REQUESTED")
            .length,
);

const completedWeek = computed(
    () =>
        visibleAppointments.value.filter((item) => item.status === "COMPLETED")
            .length,
);

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

let searchTimer: ReturnType<typeof setTimeout> | undefined;

const applyFilters = () => {
    router.get(
        "/appointments",
        {
            search: search.value || undefined,

            status: selectedStatus.value || undefined,

            appointment_type_id: selectedType.value || undefined,

            calendar_from: toDateKey(startOfWeek.value),

            calendar_to: toDateKey(endOfWeek.value),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const onSearch = () => {
    clearTimeout(searchTimer);

    searchTimer = setTimeout(applyFilters, 350);
};

const clearFilters = () => {
    search.value = "";
    selectedStatus.value = "";
    selectedType.value = "";

    applyFilters();
};

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

const loadWeek = () => {
    router.get(
        "/appointments",
        {
            search: search.value || undefined,

            status: selectedStatus.value || undefined,

            appointment_type_id: selectedType.value || undefined,

            calendar_from: toDateKey(startOfWeek.value),

            calendar_to: toDateKey(endOfWeek.value),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ["calendarAppointments", "filters"],
        },
    );
};

const previousWeek = () => {
    const date = new Date(currentDate.value);

    date.setDate(date.getDate() - 7);

    currentDate.value = date;

    loadWeek();
};

const nextWeek = () => {
    const date = new Date(currentDate.value);

    date.setDate(date.getDate() + 7);

    currentDate.value = date;

    loadWeek();
};

const goToday = () => {
    currentDate.value = new Date();

    loadWeek();
};

/*
|--------------------------------------------------------------------------
| Current time
|--------------------------------------------------------------------------
*/

const now = new Date();

const currentTimeTop = computed(() => {
    const minutes = now.getHours() * 60 + now.getMinutes();

    const calendarStart = startHour * 60;

    return ((minutes - calendarStart) / 60) * hourHeight;
});

const showCurrentTime = computed(() => {
    const hour = now.getHours();

    return hour >= startHour && hour <= endHour;
});
</script>

<template>
    <Head title="Rendez-vous" />

    <div class="min-h-[calc(100vh-4rem)] bg-muted/20">
        <!-- ======================================================= -->
        <!-- PAGE HEADER -->
        <!-- ======================================================= -->

        <div class="border-b border-border bg-background">
            <div
                class="flex flex-col gap-5 px-5 py-5 lg:flex-row lg:items-center lg:justify-between lg:px-7"
            >
                <div>
                    <div class="mb-1 flex items-center gap-2">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600"
                        >
                            <CalendarDays class="size-4" />
                        </div>

                        <h1
                            class="text-xl font-bold tracking-tight md:text-2xl"
                        >
                            Rendez-vous
                        </h1>
                    </div>

                    <p class="ml-11 text-sm text-muted-foreground">
                        Planning et suivi des rendez-vous de l'établissement
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="flex items-center rounded-xl border border-border bg-muted/30 p-1"
                    >
                        <button
                            type="button"
                            class="flex h-8 items-center gap-2 rounded-lg px-3 text-xs font-semibold transition"
                            :class="
                                viewMode === 'calendar'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="viewMode = 'calendar'"
                        >
                            <CalendarDays class="size-3.5" />

                            Planning
                        </button>

                        <button
                            type="button"
                            class="flex h-8 items-center gap-2 rounded-lg px-3 text-xs font-semibold transition"
                            :class="
                                viewMode === 'list'
                                    ? 'bg-background text-foreground shadow-sm'
                                    : 'text-muted-foreground hover:text-foreground'
                            "
                            @click="viewMode = 'list'"
                        >
                            <List class="size-3.5" />

                            Liste
                        </button>
                    </div>

                    <Link
                        v-if="canCreate"
                        href="/appointments/create"
                        class="flex h-10 items-center gap-2 rounded-xl bg-foreground px-4 text-sm font-semibold text-background shadow-sm transition hover:opacity-90"
                    >
                        <Plus class="size-4" />

                        Nouveau rendez-vous
                    </Link>
                </div>
            </div>

            <!-- Stats -->

            <div
                class="grid border-t border-border sm:grid-cols-2 xl:grid-cols-4"
            >
                <div
                    class="border-b border-border px-6 py-4 sm:border-r xl:border-b-0"
                >
                    <p class="text-xs font-medium text-muted-foreground">
                        Cette semaine
                    </p>

                    <div class="mt-1 flex items-center gap-2">
                        <span class="text-2xl font-bold">
                            {{ totalWeek }}
                        </span>

                        <span class="text-xs text-muted-foreground">
                            rendez-vous
                        </span>
                    </div>
                </div>

                <div
                    class="border-b border-border px-6 py-4 xl:border-b-0 xl:border-r"
                >
                    <p class="text-xs font-medium text-muted-foreground">
                        Confirmés
                    </p>

                    <div class="mt-1 flex items-center gap-2">
                        <span class="size-2 rounded-full bg-emerald-500" />

                        <span class="text-2xl font-bold">
                            {{ confirmedWeek }}
                        </span>
                    </div>
                </div>

                <div
                    class="border-b border-border px-6 py-4 sm:border-r sm:border-b-0"
                >
                    <p class="text-xs font-medium text-muted-foreground">
                        En attente
                    </p>

                    <div class="mt-1 flex items-center gap-2">
                        <span class="size-2 rounded-full bg-amber-500" />

                        <span class="text-2xl font-bold">
                            {{ pendingWeek }}
                        </span>
                    </div>
                </div>

                <div class="px-6 py-4">
                    <p class="text-xs font-medium text-muted-foreground">
                        Effectués
                    </p>

                    <div class="mt-1 flex items-center gap-2">
                        <span class="size-2 rounded-full bg-blue-500" />

                        <span class="text-2xl font-bold">
                            {{ completedWeek }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================= -->
        <!-- CALENDAR MODE -->
        <!-- ======================================================= -->

        <div
            v-if="viewMode === 'calendar'"
            class="grid xl:grid-cols-[270px_minmax(0,1fr)]"
        >
            <!-- SIDEBAR FILTERS -->

            <aside
                class="border-b border-border bg-background p-5 xl:min-h-[calc(100vh-14rem)] xl:border-b-0 xl:border-r"
            >
                <div class="mb-5 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <SlidersHorizontal class="size-4" />

                        <h2 class="text-sm font-bold">Filtres</h2>
                    </div>

                    <button
                        v-if="search || selectedStatus || selectedType"
                        type="button"
                        class="text-xs font-medium text-muted-foreground hover:text-foreground"
                        @click="clearFilters"
                    >
                        Réinitialiser
                    </button>
                </div>

                <!-- Search -->

                <div class="relative mb-5">
                    <Search
                        class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                    />

                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher..."
                        class="h-10 w-full rounded-xl border border-border bg-background pl-9 pr-3 text-sm outline-none transition focus:ring-4 focus:ring-muted"
                        @input="onSearch"
                    />
                </div>

                <!-- Status -->

                <div class="mb-6">
                    <p
                        class="mb-3 text-[10px] font-bold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Statut
                    </p>

                    <div class="space-y-1">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted"
                            :class="
                                !selectedStatus ? 'bg-muted font-semibold' : ''
                            "
                            @click="
                                selectedStatus = '';
                                applyFilters();
                            "
                        >
                            <span class="size-2 rounded-full bg-foreground" />

                            Tous les rendez-vous
                        </button>

                        <button
                            v-for="(label, code) in statuses"
                            :key="code"
                            type="button"
                            class="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left text-sm transition hover:bg-muted"
                            :class="
                                selectedStatus === code
                                    ? 'bg-muted font-semibold'
                                    : ''
                            "
                            @click="
                                selectedStatus = code;
                                applyFilters();
                            "
                        >
                            <span
                                class="size-2 rounded-full"
                                :class="statusDotClass(code)"
                            />

                            {{ label }}
                        </button>
                    </div>
                </div>

                <!-- Type -->

                <div>
                    <p
                        class="mb-3 text-[10px] font-bold uppercase tracking-[0.16em] text-muted-foreground"
                    >
                        Type de rendez-vous
                    </p>

                    <select
                        v-model="selectedType"
                        class="h-10 w-full rounded-xl border border-border bg-background px-3 text-sm outline-none focus:ring-4 focus:ring-muted"
                        @change="applyFilters"
                    >
                        <option value="">Tous les types</option>

                        <option
                            v-for="type in types"
                            :key="type.id"
                            :value="String(type.id)"
                        >
                            {{ type.name }}
                        </option>
                    </select>
                </div>

                <!-- Legend -->

                <div
                    class="mt-8 rounded-2xl border border-border bg-muted/30 p-4"
                >
                    <p class="text-xs font-bold">Planning</p>

                    <p class="mt-1 text-xs leading-5 text-muted-foreground">
                        Cliquez sur un rendez-vous pour consulter son détail,
                        son affectation et son historique.
                    </p>
                </div>
            </aside>

            <!-- CALENDAR MAIN -->

            <main class="min-w-0 bg-background">
                <!-- Toolbar -->

                <div
                    class="flex min-h-16 flex-wrap items-center justify-between gap-3 border-b border-border px-4 py-3 lg:px-5"
                >
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="h-9 rounded-xl border border-border bg-background px-3 text-sm font-semibold transition hover:bg-muted"
                            @click="goToday"
                        >
                            Aujourd'hui
                        </button>

                        <div
                            class="flex items-center rounded-xl border border-border p-1"
                        >
                            <button
                                type="button"
                                class="flex size-8 items-center justify-center rounded-lg transition hover:bg-muted"
                                @click="previousWeek"
                            >
                                <ChevronLeft class="size-4" />
                            </button>

                            <button
                                type="button"
                                class="flex size-8 items-center justify-center rounded-lg transition hover:bg-muted"
                                @click="nextWeek"
                            >
                                <ChevronRight class="size-4" />
                            </button>
                        </div>

                        <h2 class="ml-2 hidden text-base font-bold sm:block">
                            {{ monthTitle }}
                        </h2>
                    </div>

                    <div class="flex items-center gap-2">
                        <div
                            class="hidden items-center gap-2 rounded-xl bg-muted/50 px-3 py-2 text-xs text-muted-foreground md:flex"
                        >
                            <span class="size-2 rounded-full bg-emerald-500" />

                            {{ confirmedWeek }}
                            confirmé{{ confirmedWeek > 1 ? "s" : "" }}
                        </div>

                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-xl border border-border transition hover:bg-muted"
                            title="Actualiser"
                            @click="loadWeek"
                        >
                            <RefreshCcw class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Calendar -->

                <div class="overflow-x-auto">
                    <div class="min-w-[980px]">
                        <!-- Days header -->

                        <div
                            class="grid grid-cols-[70px_repeat(7,minmax(125px,1fr))] border-b border-border"
                        >
                            <div
                                class="flex items-end justify-center border-r border-border pb-3 text-[9px] font-medium text-muted-foreground"
                            >
                                GMT+1
                            </div>

                            <div
                                v-for="date in weekDays"
                                :key="date.toISOString()"
                                class="relative flex h-[86px] flex-col items-center justify-center border-r border-border last:border-r-0"
                                :class="
                                    isToday(date)
                                        ? 'bg-emerald-500/[0.035]'
                                        : ''
                                "
                            >
                                <span
                                    class="text-[10px] font-bold tracking-[0.14em]"
                                    :class="
                                        isToday(date)
                                            ? 'text-emerald-600'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ weekDaysNames[date.getDay()] }}
                                </span>

                                <span
                                    class="mt-1 flex size-9 items-center justify-center rounded-full text-lg font-bold"
                                    :class="
                                        isToday(date)
                                            ? 'bg-emerald-500 text-white shadow-sm'
                                            : ''
                                    "
                                >
                                    {{ date.getDate() }}
                                </span>

                                <span
                                    v-if="appointmentsForDay(date).length"
                                    class="mt-1 text-[9px] font-medium text-muted-foreground"
                                >
                                    {{ appointmentsForDay(date).length }}
                                    RDV
                                </span>
                            </div>
                        </div>

                        <!-- Time grid -->

                        <div
                            class="grid grid-cols-[70px_repeat(7,minmax(125px,1fr))]"
                        >
                            <!-- Hours -->

                            <div
                                class="relative border-r border-border"
                                :style="{
                                    height: hours.length * hourHeight + 'px',
                                }"
                            >
                                <div
                                    v-for="(hour, index) in hours"
                                    :key="hour"
                                    class="absolute left-0 right-0"
                                    :style="{
                                        top: index * hourHeight + 'px',
                                    }"
                                >
                                    <span
                                        class="absolute -top-2.5 right-3 bg-background px-1 text-[10px] font-medium text-muted-foreground"
                                    >
                                        {{ String(hour).padStart(2, "0") }}:00
                                    </span>
                                </div>
                            </div>

                            <!-- Days -->

                            <div
                                v-for="date in weekDays"
                                :key="`column-${date.toISOString()}`"
                                class="relative border-r border-border last:border-r-0"
                                :class="
                                    isToday(date) ? 'bg-emerald-500/[0.02]' : ''
                                "
                                :style="{
                                    height: hours.length * hourHeight + 'px',
                                }"
                            >
                                <!-- Hour lines -->

                                <div
                                    v-for="(hour, index) in hours"
                                    :key="`line-${hour}`"
                                    class="absolute left-0 right-0 border-t border-border/70"
                                    :style="{
                                        top: index * hourHeight + 'px',
                                    }"
                                />

                                <!-- Half hour -->

                                <div
                                    v-for="(hour, index) in hours"
                                    :key="`half-${hour}`"
                                    class="absolute left-0 right-0 border-t border-dashed border-border/40"
                                    :style="{
                                        top:
                                            index * hourHeight +
                                            hourHeight / 2 +
                                            'px',
                                    }"
                                />

                                <!-- Current time -->

                                <div
                                    v-if="isToday(date) && showCurrentTime"
                                    class="absolute left-0 right-0 z-20 flex items-center"
                                    :style="{
                                        top: currentTimeTop + 'px',
                                    }"
                                >
                                    <span
                                        class="-ml-1 size-2 rounded-full bg-rose-500"
                                    />

                                    <span class="h-px flex-1 bg-rose-500" />
                                </div>

                                <!-- Appointments -->

                                <Link
                                    v-for="appointment in appointmentsForDay(
                                        date,
                                    )"
                                    :key="appointment.id"
                                    :href="`/appointments/${appointment.id}`"
                                    class="absolute left-1.5 right-1.5 z-10 overflow-hidden rounded-lg border px-2 py-1.5 shadow-sm transition duration-200 hover:z-30 hover:-translate-y-0.5 hover:shadow-lg"
                                    :class="statusClass(appointment.status)"
                                    :style="eventStyle(appointment)"
                                >
                                    <div class="flex h-full flex-col">
                                        <div
                                            class="flex items-start justify-between gap-1"
                                        >
                                            <p
                                                class="truncate text-[11px] font-bold"
                                            >
                                                {{ appointment.title }}
                                            </p>

                                            <span
                                                class="mt-1 size-1.5 shrink-0 rounded-full"
                                                :class="
                                                    statusDotClass(
                                                        appointment.status,
                                                    )
                                                "
                                            />
                                        </div>

                                        <p
                                            class="mt-0.5 text-[9px] font-semibold opacity-75"
                                        >
                                            {{
                                                formatTime(
                                                    appointment.starts_at,
                                                )
                                            }}
                                            –
                                            {{
                                                formatTime(appointment.ends_at)
                                            }}
                                        </p>

                                        <div
                                            v-if="appointment.location"
                                            class="mt-auto hidden items-center gap-1 pt-1 text-[9px] opacity-70 2xl:flex"
                                        >
                                            <MapPin class="size-2.5 shrink-0" />

                                            <span class="truncate">
                                                {{ appointment.location }}
                                            </span>
                                        </div>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- ======================================================= -->
        <!-- LIST MODE -->
        <!-- ======================================================= -->

        <div v-else class="p-5 lg:p-7">
            <div class="mx-auto max-w-[1500px]">
                <!-- Filters -->

                <div
                    class="mb-5 flex flex-col gap-3 rounded-2xl border border-border bg-background p-4 shadow-sm lg:flex-row lg:items-center"
                >
                    <div class="relative min-w-0 flex-1">
                        <Search
                            class="absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher un rendez-vous..."
                            class="h-10 w-full rounded-xl border border-border bg-background pl-9 pr-3 text-sm outline-none focus:ring-4 focus:ring-muted"
                            @input="onSearch"
                        />
                    </div>

                    <select
                        v-model="selectedStatus"
                        class="h-10 rounded-xl border border-border bg-background px-3 text-sm outline-none"
                        @change="applyFilters"
                    >
                        <option value="">Tous les statuts</option>

                        <option
                            v-for="(label, code) in statuses"
                            :key="code"
                            :value="code"
                        >
                            {{ label }}
                        </option>
                    </select>

                    <select
                        v-model="selectedType"
                        class="h-10 rounded-xl border border-border bg-background px-3 text-sm outline-none"
                        @change="applyFilters"
                    >
                        <option value="">Tous les types</option>

                        <option
                            v-for="type in types"
                            :key="type.id"
                            :value="String(type.id)"
                        >
                            {{ type.name }}
                        </option>
                    </select>
                </div>

                <!-- Table -->

                <div
                    class="overflow-hidden rounded-2xl border border-border bg-background shadow-sm"
                >
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1000px]">
                            <thead class="border-b border-border bg-muted/40">
                                <tr>
                                    <th
                                        class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-muted-foreground"
                                    >
                                        Rendez-vous
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-muted-foreground"
                                    >
                                        Date & heure
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-muted-foreground"
                                    >
                                        Type
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-muted-foreground"
                                    >
                                        Affectation
                                    </th>

                                    <th
                                        class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-muted-foreground"
                                    >
                                        Statut
                                    </th>

                                    <th class="w-16 px-5 py-3" />
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-border">
                                <tr
                                    v-for="appointment in appointments.data"
                                    :key="appointment.id"
                                    class="group transition hover:bg-muted/30"
                                >
                                    <td class="px-5 py-4">
                                        <Link
                                            :href="`/appointments/${appointment.id}`"
                                            class="block"
                                        >
                                            <p class="font-semibold">
                                                {{ appointment.title }}
                                            </p>

                                            <p
                                                class="mt-1 text-xs text-muted-foreground"
                                            >
                                                {{ appointment.reference }}
                                            </p>
                                        </Link>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-start gap-2">
                                            <CalendarDays
                                                class="mt-0.5 size-4 text-muted-foreground"
                                            />

                                            <div>
                                                <p
                                                    class="text-sm font-medium capitalize"
                                                >
                                                    {{
                                                        formatLongDate(
                                                            appointment.starts_at,
                                                        )
                                                    }}
                                                </p>

                                                <p
                                                    class="mt-0.5 text-xs text-muted-foreground"
                                                >
                                                    {{
                                                        formatTime(
                                                            appointment.starts_at,
                                                        )
                                                    }}
                                                    –
                                                    {{
                                                        formatTime(
                                                            appointment.ends_at,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-sm">
                                        {{ appointment.type?.name ?? "—" }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="flex size-8 items-center justify-center rounded-full bg-muted"
                                            >
                                                <UserRound
                                                    class="size-3.5 text-muted-foreground"
                                                />
                                            </div>

                                            <span class="text-sm font-medium">
                                                {{
                                                    personName(
                                                        assignedPerson(
                                                            appointment,
                                                        ),
                                                    )
                                                }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full border px-2.5 py-1 text-xs font-semibold"
                                            :class="
                                                statusClass(appointment.status)
                                            "
                                        >
                                            <span
                                                class="size-1.5 rounded-full"
                                                :class="
                                                    statusDotClass(
                                                        appointment.status,
                                                    )
                                                "
                                            />

                                            {{
                                                statusLabel(appointment.status)
                                            }}
                                        </span>
                                    </td>

                                    <td class="px-5 py-4 text-right">
                                        <Link
                                            :href="`/appointments/${appointment.id}`"
                                            class="inline-flex size-8 items-center justify-center rounded-lg transition hover:bg-muted"
                                        >
                                            <ChevronRight class="size-4" />
                                        </Link>
                                    </td>
                                </tr>

                                <tr v-if="!appointments.data.length">
                                    <td
                                        colspan="6"
                                        class="px-6 py-20 text-center"
                                    >
                                        <div
                                            class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-muted"
                                        >
                                            <CalendarDays
                                                class="size-6 text-muted-foreground"
                                            />
                                        </div>

                                        <h3 class="mt-4 font-bold">
                                            Aucun rendez-vous
                                        </h3>

                                        <p
                                            class="mt-1 text-sm text-muted-foreground"
                                        >
                                            Aucun rendez-vous ne correspond aux
                                            critères sélectionnés.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->

                    <div
                        v-if="appointments.last_page > 1"
                        class="flex flex-wrap items-center justify-between gap-3 border-t border-border px-5 py-4"
                    >
                        <p class="text-xs text-muted-foreground">
                            {{ appointments.from ?? 0 }}
                            –
                            {{ appointments.to ?? 0 }}
                            sur
                            {{ appointments.total }}
                            rendez-vous
                        </p>

                        <div class="flex items-center gap-1">
                            <template
                                v-for="link in appointments.links"
                                :key="link.label"
                            >
                                <Link
                                    v-if="link.url"
                                    :href="link.url"
                                    preserve-scroll
                                    preserve-state
                                    class="flex min-w-8 items-center justify-center rounded-lg border px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted"
                                    :class="
                                        link.active
                                            ? 'border-foreground bg-foreground text-background'
                                            : 'border-border'
                                    "
                                    v-html="link.label"
                                />

                                <span
                                    v-else
                                    class="flex min-w-8 cursor-not-allowed items-center justify-center rounded-lg border border-border px-2.5 py-1.5 text-xs text-muted-foreground opacity-40"
                                    v-html="link.label"
                                />
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
