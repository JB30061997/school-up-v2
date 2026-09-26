<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    CalendarDays,
    Check,
    ChevronLeft,
    ChevronRight,
    Clock3,
    MapPin,
    Plus,
    School,
    UserRound,
    Users,
    Video,
} from "@lucide/vue";
import { computed, ref } from "vue";

type AppointmentType = {
    id: number;
    name: string;
    code: string;
    duration_minutes?: number | null;
};

type User = {
    id: number;
    name: string;
    first_name?: string | null;
    last_name?: string | null;
    email?: string | null;
};

const props = defineProps<{
    types: AppointmentType[];
    users: User[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: "Rendez-vous",
                href: "/appointments",
            },
            {
                title: "Nouveau rendez-vous",
                href: "/appointments/create",
            },
        ],
    },
});

/*
|--------------------------------------------------------------------------
| Form
|--------------------------------------------------------------------------
*/

const form = useForm({
    appointment_type_id: null as number | null,
    assigned_to: null as number | null,
    title: "",
    description: "",
    starts_at: "",
    ends_at: "",
    location: "",
});

/*
|--------------------------------------------------------------------------
| Calendar
|--------------------------------------------------------------------------
*/

const currentDate = ref(new Date());

const selectedDate = ref<Date | null>(null);
const selectedHour = ref<number | null>(null);
const selectedMinute = ref(0);

const calendarStartHour = 7;
const calendarEndHour = 19;

const hours = Array.from(
    {
        length: calendarEndHour - calendarStartHour + 1,
    },
    (_, index) => calendarStartHour + index,
);

const frenchMonths = [
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

const frenchDays = ["DIM", "LUN", "MAR", "MER", "JEU", "VEN", "SAM"];

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

const monthTitle = computed(() => {
    const first = weekDays.value[0];
    const last = weekDays.value[6];

    if (first.getMonth() === last.getMonth()) {
        return `${frenchMonths[first.getMonth()]} ${first.getFullYear()}`;
    }

    return `${frenchMonths[first.getMonth()]} – ${
        frenchMonths[last.getMonth()]
    } ${last.getFullYear()}`;
});

const selectedType = computed(() => {
    return props.types.find((type) => type.id === form.appointment_type_id);
});

const selectedUser = computed(() => {
    return props.users.find((user) => user.id === form.assigned_to);
});

const selectedDateLabel = computed(() => {
    if (!selectedDate.value || selectedHour.value === null) {
        return "Aucun créneau sélectionné";
    }

    return new Intl.DateTimeFormat("fr-FR", {
        weekday: "long",
        day: "numeric",
        month: "long",
        hour: "2-digit",
        minute: "2-digit",
    }).format(
        new Date(
            selectedDate.value.getFullYear(),
            selectedDate.value.getMonth(),
            selectedDate.value.getDate(),
            selectedHour.value,
            selectedMinute.value,
        ),
    );
});

/*
|--------------------------------------------------------------------------
| Utils
|--------------------------------------------------------------------------
*/

const isToday = (date: Date): boolean => {
    const today = new Date();

    return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
    );
};

const isSelectedDay = (date: Date): boolean => {
    if (!selectedDate.value) {
        return false;
    }

    return (
        date.getDate() === selectedDate.value.getDate() &&
        date.getMonth() === selectedDate.value.getMonth() &&
        date.getFullYear() === selectedDate.value.getFullYear()
    );
};

const formatHour = (hour: number): string => {
    return `${String(hour).padStart(2, "0")}:00`;
};

const formatDateTime = (date: Date): string => {
    const year = date.getFullYear();

    const month = String(date.getMonth() + 1).padStart(2, "0");

    const day = String(date.getDate()).padStart(2, "0");

    const hour = String(date.getHours()).padStart(2, "0");

    const minute = String(date.getMinutes()).padStart(2, "0");

    return `${year}-${month}-${day}T${hour}:${minute}`;
};

const userDisplayName = (user: User): string => {
    const fullName = [user.first_name, user.last_name]
        .filter(Boolean)
        .join(" ");

    return fullName || user.name;
};

/*
|--------------------------------------------------------------------------
| Select slot
|--------------------------------------------------------------------------
*/

const selectSlot = (date: Date, hour: number, minute = 0): void => {
    selectedDate.value = new Date(date);
    selectedHour.value = hour;
    selectedMinute.value = minute;

    const start = new Date(
        date.getFullYear(),
        date.getMonth(),
        date.getDate(),
        hour,
        minute,
        0,
    );

    const duration = selectedType.value?.duration_minutes || 60;

    const end = new Date(start.getTime() + duration * 60 * 1000);

    form.starts_at = formatDateTime(start);
    form.ends_at = formatDateTime(end);
};

/*
|--------------------------------------------------------------------------
| Type
|--------------------------------------------------------------------------
*/

const selectType = (type: AppointmentType): void => {
    form.appointment_type_id = type.id;

    if (selectedDate.value && selectedHour.value !== null) {
        selectSlot(
            selectedDate.value,
            selectedHour.value,
            selectedMinute.value,
        );
    }
};

/*
|--------------------------------------------------------------------------
| Navigation
|--------------------------------------------------------------------------
*/

const previousWeek = (): void => {
    const date = new Date(currentDate.value);

    date.setDate(date.getDate() - 7);

    currentDate.value = date;
};

const nextWeek = (): void => {
    const date = new Date(currentDate.value);

    date.setDate(date.getDate() + 7);

    currentDate.value = date;
};

const goToday = (): void => {
    currentDate.value = new Date();
};

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submit = (): void => {
    form.post("/appointments", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouveau rendez-vous" />

    <div class="min-h-[calc(100vh-4rem)] bg-muted/20">
        <!-- ========================================================= -->
        <!-- TOP TOOLBAR -->
        <!-- ========================================================= -->

        <div
            class="sticky top-16 z-20 border-b border-border bg-background/95 backdrop-blur"
        >
            <div
                class="flex min-h-16 flex-wrap items-center justify-between gap-3 px-4 py-3 lg:px-6"
            >
                <div class="flex items-center gap-3">
                    <Link
                        href="/appointments"
                        class="flex size-10 items-center justify-center rounded-xl border border-border bg-background transition hover:bg-muted"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>

                    <div>
                        <h1 class="text-lg font-bold tracking-tight">
                            Nouveau rendez-vous
                        </h1>

                        <p class="text-xs text-muted-foreground">
                            Sélectionnez directement un créneau dans l'agenda
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="hidden h-9 items-center gap-2 rounded-xl border border-border bg-background px-3 text-sm font-medium transition hover:bg-muted sm:flex"
                        @click="goToday"
                    >
                        Aujourd'hui
                    </button>

                    <div
                        class="flex items-center rounded-xl border border-border bg-background p-1"
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

                    <div
                        class="hidden min-w-44 text-center text-sm font-semibold md:block"
                    >
                        {{ monthTitle }}
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- CONTENT -->
        <!-- ========================================================= -->

        <div
            class="grid min-h-[calc(100vh-8rem)] xl:grid-cols-[350px_minmax(0,1fr)]"
        >
            <!-- ===================================================== -->
            <!-- LEFT PANEL -->
            <!-- ===================================================== -->

            <aside
                class="border-b border-border bg-background xl:border-b-0 xl:border-r"
            >
                <form class="flex h-full flex-col" @submit.prevent="submit">
                    <div class="flex-1 space-y-6 overflow-y-auto p-5">
                        <!-- TITLE -->

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wider text-muted-foreground"
                            >
                                Objet du rendez-vous
                            </label>

                            <input
                                v-model="form.title"
                                type="text"
                                placeholder="Ex. Rendez-vous avec la direction"
                                class="h-12 w-full rounded-xl border border-border bg-background px-4 text-sm font-medium outline-none transition placeholder:text-muted-foreground focus:border-foreground/30 focus:ring-4 focus:ring-muted"
                            />

                            <p
                                v-if="form.errors.title"
                                class="mt-1.5 text-xs text-destructive"
                            >
                                {{ form.errors.title }}
                            </p>
                        </div>

                        <!-- TYPE -->

                        <div>
                            <div class="mb-3 flex items-center gap-2">
                                <CalendarDays
                                    class="size-4 text-muted-foreground"
                                />

                                <label
                                    class="text-xs font-bold uppercase tracking-wider text-muted-foreground"
                                >
                                    Type de rendez-vous
                                </label>
                            </div>

                            <div class="space-y-2">
                                <button
                                    v-for="type in types"
                                    :key="type.id"
                                    type="button"
                                    class="group flex w-full items-center gap-3 rounded-xl border p-3 text-left transition"
                                    :class="
                                        form.appointment_type_id === type.id
                                            ? 'border-emerald-500/40 bg-emerald-500/10'
                                            : 'border-border bg-background hover:bg-muted/60'
                                    "
                                    @click="selectType(type)"
                                >
                                    <div
                                        class="flex size-9 shrink-0 items-center justify-center rounded-lg"
                                        :class="
                                            form.appointment_type_id === type.id
                                                ? 'bg-emerald-500 text-white'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                    >
                                        <CalendarDays class="size-4" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p
                                            class="truncate text-sm font-semibold"
                                        >
                                            {{ type.name }}
                                        </p>

                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ type.duration_minutes || 60 }}
                                            minutes
                                        </p>
                                    </div>

                                    <Check
                                        v-if="
                                            form.appointment_type_id === type.id
                                        "
                                        class="size-4 text-emerald-600"
                                    />
                                </button>
                            </div>

                            <p
                                v-if="form.errors.appointment_type_id"
                                class="mt-1.5 text-xs text-destructive"
                            >
                                {{ form.errors.appointment_type_id }}
                            </p>
                        </div>

                        <!-- ASSIGNED USER -->

                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <UserRound
                                    class="size-4 text-muted-foreground"
                                />

                                <label
                                    class="text-xs font-bold uppercase tracking-wider text-muted-foreground"
                                >
                                    Affecté à
                                </label>
                            </div>

                            <div class="relative">
                                <Users
                                    class="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground"
                                />

                                <select
                                    v-model="form.assigned_to"
                                    class="h-11 w-full appearance-none rounded-xl border border-border bg-background pl-10 pr-4 text-sm outline-none transition focus:ring-4 focus:ring-muted"
                                >
                                    <option :value="null">Non affecté</option>

                                    <option
                                        v-for="user in users"
                                        :key="user.id"
                                        :value="user.id"
                                    >
                                        {{ userDisplayName(user) }}
                                    </option>
                                </select>
                            </div>

                            <p
                                v-if="selectedUser?.email"
                                class="mt-1.5 truncate text-xs text-muted-foreground"
                            >
                                {{ selectedUser.email }}
                            </p>

                            <p
                                v-if="form.errors.assigned_to"
                                class="mt-1.5 text-xs text-destructive"
                            >
                                {{ form.errors.assigned_to }}
                            </p>
                        </div>

                        <!-- LOCATION -->

                        <div>
                            <div class="mb-2 flex items-center gap-2">
                                <MapPin class="size-4 text-muted-foreground" />

                                <label
                                    class="text-xs font-bold uppercase tracking-wider text-muted-foreground"
                                >
                                    Lieu
                                </label>
                            </div>

                            <input
                                v-model="form.location"
                                type="text"
                                placeholder="Salle, bureau, visioconférence..."
                                class="h-11 w-full rounded-xl border border-border bg-background px-4 text-sm outline-none transition focus:ring-4 focus:ring-muted"
                            />

                            <div class="mt-2 flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 rounded-lg bg-muted px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted/70"
                                    @click="form.location = 'En ligne'"
                                >
                                    <Video class="size-3.5" />
                                    En ligne
                                </button>

                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 rounded-lg bg-muted px-2.5 py-1.5 text-xs font-medium transition hover:bg-muted/70"
                                    @click="form.location = 'Établissement'"
                                >
                                    <School class="size-3.5" />
                                    Établissement
                                </button>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold uppercase tracking-wider text-muted-foreground"
                            >
                                Description
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="4"
                                placeholder="Ajoutez les informations utiles..."
                                class="w-full resize-none rounded-xl border border-border bg-background p-4 text-sm outline-none transition focus:ring-4 focus:ring-muted"
                            />

                            <p
                                v-if="form.errors.description"
                                class="mt-1.5 text-xs text-destructive"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>

                    <!-- SELECTED SLOT -->

                    <div class="border-t border-border bg-muted/20 p-5">
                        <div
                            class="mb-4 rounded-xl border border-border bg-background p-4"
                        >
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600"
                                >
                                    <Clock3 class="size-4" />
                                </div>

                                <div>
                                    <p class="text-xs text-muted-foreground">
                                        Créneau sélectionné
                                    </p>

                                    <p
                                        class="mt-0.5 text-sm font-semibold capitalize"
                                    >
                                        {{ selectedDateLabel }}
                                    </p>

                                    <p
                                        v-if="form.starts_at && form.ends_at"
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Durée :
                                        {{
                                            selectedType?.duration_minutes || 60
                                        }}
                                        min
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p
                            v-if="form.errors.starts_at"
                            class="mb-2 text-xs text-destructive"
                        >
                            {{ form.errors.starts_at }}
                        </p>

                        <p
                            v-if="form.errors.ends_at"
                            class="mb-2 text-xs text-destructive"
                        >
                            {{ form.errors.ends_at }}
                        </p>

                        <button
                            type="submit"
                            :disabled="
                                form.processing ||
                                !form.starts_at ||
                                !form.appointment_type_id ||
                                !form.title
                            "
                            class="flex h-11 w-full items-center justify-center gap-2 rounded-xl bg-foreground px-4 text-sm font-bold text-background shadow-sm transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40"
                        >
                            <Plus class="size-4" />

                            <span v-if="!form.processing">
                                Créer le rendez-vous
                            </span>

                            <span v-else> Création... </span>
                        </button>
                    </div>
                </form>
            </aside>

            <!-- ===================================================== -->
            <!-- CALENDAR -->
            <!-- ===================================================== -->

            <section class="min-w-0 overflow-hidden bg-background">
                <!-- Calendar header -->

                <div
                    class="grid grid-cols-[72px_repeat(7,minmax(110px,1fr))] border-b border-border"
                >
                    <div
                        class="flex items-end justify-center border-r border-border pb-3 text-[10px] font-medium text-muted-foreground"
                    >
                        GMT+1
                    </div>

                    <button
                        v-for="date in weekDays"
                        :key="date.toISOString()"
                        type="button"
                        class="flex min-h-24 flex-col items-center justify-center border-r border-border transition last:border-r-0 hover:bg-muted/40"
                        :class="isSelectedDay(date) ? 'bg-emerald-500/5' : ''"
                        @click="selectSlot(date, selectedHour ?? 9)"
                    >
                        <span
                            class="mb-1 text-[10px] font-bold tracking-wider"
                            :class="
                                isToday(date)
                                    ? 'text-emerald-600'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ frenchDays[date.getDay()] }}
                        </span>

                        <span
                            class="flex size-10 items-center justify-center rounded-full text-lg font-semibold transition"
                            :class="[
                                isToday(date)
                                    ? 'bg-emerald-500 text-white shadow-sm'
                                    : '',
                                !isToday(date) && isSelectedDay(date)
                                    ? 'bg-emerald-500/10 text-emerald-700'
                                    : '',
                            ]"
                        >
                            {{ date.getDate() }}
                        </span>
                    </button>
                </div>

                <!-- Scroll area -->

                <div class="max-h-[calc(100vh-13rem)] overflow-auto">
                    <div class="relative min-w-[900px]">
                        <!-- Hours -->

                        <div
                            v-for="hour in hours"
                            :key="hour"
                            class="grid h-20 grid-cols-[72px_repeat(7,minmax(110px,1fr))]"
                        >
                            <!-- Time -->

                            <div class="relative border-r border-border">
                                <span
                                    class="absolute -top-2.5 right-3 bg-background px-1 text-[10px] font-medium text-muted-foreground"
                                >
                                    {{ formatHour(hour) }}
                                </span>
                            </div>

                            <!-- Day cells -->

                            <button
                                v-for="date in weekDays"
                                :key="`${date.toISOString()}-${hour}`"
                                type="button"
                                class="group relative border-r border-t border-border text-left transition last:border-r-0 hover:bg-emerald-500/5"
                                :class="
                                    isSelectedDay(date) && selectedHour === hour
                                        ? 'bg-emerald-500/10'
                                        : ''
                                "
                                @click="selectSlot(date, hour, 0)"
                            >
                                <!-- Hover -->

                                <div
                                    class="absolute inset-x-2 top-2 hidden rounded-lg border border-dashed border-emerald-500/40 bg-emerald-500/5 px-2 py-1.5 group-hover:block"
                                >
                                    <span
                                        class="text-[10px] font-semibold text-emerald-700"
                                    >
                                        + {{ formatHour(hour) }}
                                    </span>
                                </div>

                                <!-- Selected event -->

                                <div
                                    v-if="
                                        isSelectedDay(date) &&
                                        selectedHour === hour
                                    "
                                    class="absolute inset-x-1.5 top-1.5 z-10 min-h-16 overflow-hidden rounded-lg border border-emerald-500/30 bg-emerald-500 p-2 text-white shadow-md"
                                >
                                    <div class="flex items-start gap-1.5">
                                        <CalendarDays
                                            class="mt-0.5 size-3 shrink-0"
                                        />

                                        <div class="min-w-0">
                                            <p
                                                class="truncate text-[11px] font-bold"
                                            >
                                                {{
                                                    form.title ||
                                                    "Nouveau rendez-vous"
                                                }}
                                            </p>

                                            <p
                                                class="mt-0.5 text-[10px] text-white/80"
                                            >
                                                {{
                                                    String(
                                                        selectedHour,
                                                    ).padStart(2, "0")
                                                }}:{{
                                                    String(
                                                        selectedMinute,
                                                    ).padStart(2, "0")
                                                }}
                                                ·
                                                {{
                                                    selectedType?.duration_minutes ||
                                                    60
                                                }}
                                                min
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
