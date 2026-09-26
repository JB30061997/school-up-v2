<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    Activity,
    AlarmClock,
    CalendarClock,
    Check,
    CheckCircle2,
    ChevronRight,
    CircleDot,
    Clock3,
    FileInput,
    FolderCog,
    Pencil,
    Plus,
    Search,
    Settings2,
    ShieldCheck,
    Sparkles,
    Star,
    TicketCheck,
    Trash2,
    X,
} from "@lucide/vue";

type RequestType = {
    id: number;
    name: string;
    code: string;
    description: string | null;
    sla_minutes: number | null;
    requires_approval: boolean;
    sort_order: number;
    active: boolean;
};

type AppointmentType = {
    id: number;
    name: string;
    code: string;
    description: string | null;
    duration_minutes: number;
    sort_order: number;
    active: boolean;
};

type TicketStatus = {
    id: number;
    name: string;
    code: string;
    is_closed: boolean;
    is_default: boolean;
    sort_order: number;
    active: boolean;
    tickets_count?: number;
};

type Tab = "requests" | "appointments" | "statuses";

type ModalMode = "create" | "edit" | null;

const props = defineProps<{
    requestTypes: RequestType[];
    appointmentTypes: AppointmentType[];
    ticketStatuses: TicketStatus[];
}>();

const activeTab = ref<Tab>("requests");
const modalMode = ref<ModalMode>(null);
const search = ref("");
const processing = ref(false);

const selectedRequestType = ref<RequestType | null>(null);
const selectedAppointmentType = ref<AppointmentType | null>(null);
const selectedTicketStatus = ref<TicketStatus | null>(null);

const form = ref({
    name: "",
    code: "",
    description: "",
    sla_minutes: null as number | null,
    requires_approval: false,
    duration_minutes: 30,
    is_closed: false,
    is_default: false,
    sort_order: 0,
    active: true,
});

const tabs = [
    {
        id: "requests" as Tab,
        label: "Types de demandes",
        short: "Demandes",
        description:
            "Catégorisez les demandes internes et configurez leurs règles.",
        icon: FileInput,
        accent: "emerald",
    },
    {
        id: "appointments" as Tab,
        label: "Types de rendez-vous",
        short: "Rendez-vous",
        description: "Configurez les motifs et durées des rendez-vous.",
        icon: CalendarClock,
        accent: "blue",
    },
    {
        id: "statuses" as Tab,
        label: "Statuts des tickets",
        short: "Statuts",
        description: "Définissez le cycle de vie et les états des tickets.",
        icon: TicketCheck,
        accent: "violet",
    },
];

const currentTab = computed(
    () => tabs.find((tab) => tab.id === activeTab.value) ?? tabs[0],
);

const normalizedSearch = computed(() => search.value.trim().toLowerCase());

const filteredRequestTypes = computed(() => {
    if (!normalizedSearch.value) {
        return props.requestTypes;
    }

    return props.requestTypes.filter((item) =>
        [item.name, item.code, item.description ?? ""].some((value) =>
            value.toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

const filteredAppointmentTypes = computed(() => {
    if (!normalizedSearch.value) {
        return props.appointmentTypes;
    }

    return props.appointmentTypes.filter((item) =>
        [item.name, item.code, item.description ?? ""].some((value) =>
            value.toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

const filteredTicketStatuses = computed(() => {
    if (!normalizedSearch.value) {
        return props.ticketStatuses;
    }

    return props.ticketStatuses.filter((item) =>
        [item.name, item.code].some((value) =>
            value.toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

const totalItems = computed(
    () =>
        props.requestTypes.length +
        props.appointmentTypes.length +
        props.ticketStatuses.length,
);

const activeItems = computed(() => {
    return [
        ...props.requestTypes,
        ...props.appointmentTypes,
        ...props.ticketStatuses,
    ].filter((item) => item.active).length;
});

const inactiveItems = computed(() => totalItems.value - activeItems.value);

const currentItemsCount = computed(() => {
    if (activeTab.value === "requests") {
        return props.requestTypes.length;
    }

    if (activeTab.value === "appointments") {
        return props.appointmentTypes.length;
    }

    return props.ticketStatuses.length;
});

const resetForm = () => {
    form.value = {
        name: "",
        code: "",
        description: "",
        sla_minutes: null,
        requires_approval: false,
        duration_minutes: 30,
        is_closed: false,
        is_default: false,
        sort_order: 0,
        active: true,
    };

    selectedRequestType.value = null;
    selectedAppointmentType.value = null;
    selectedTicketStatus.value = null;
};

const nextSortOrder = computed(() => {
    let values: number[] = [];

    if (activeTab.value === "requests") {
        values = props.requestTypes.map((item) => item.sort_order ?? 0);
    }

    if (activeTab.value === "appointments") {
        values = props.appointmentTypes.map((item) => item.sort_order ?? 0);
    }

    if (activeTab.value === "statuses") {
        values = props.ticketStatuses.map((item) => item.sort_order ?? 0);
    }

    return values.length ? Math.max(...values) + 1 : 1;
});

const openCreate = () => {
    resetForm();

    form.value.sort_order = nextSortOrder.value;

    modalMode.value = "create";
};

const editRequestType = (item: RequestType) => {
    resetForm();

    selectedRequestType.value = item;

    form.value = {
        ...form.value,
        name: item.name,
        code: item.code,
        description: item.description ?? "",
        sla_minutes: item.sla_minutes,
        requires_approval: item.requires_approval,
        sort_order: item.sort_order,
        active: item.active,
    };

    modalMode.value = "edit";
};

const editAppointmentType = (item: AppointmentType) => {
    resetForm();

    selectedAppointmentType.value = item;

    form.value = {
        ...form.value,
        name: item.name,
        code: item.code,
        description: item.description ?? "",
        duration_minutes: item.duration_minutes,
        sort_order: item.sort_order,
        active: item.active,
    };

    modalMode.value = "edit";
};

const editTicketStatus = (item: TicketStatus) => {
    resetForm();

    selectedTicketStatus.value = item;

    form.value = {
        ...form.value,
        name: item.name,
        code: item.code,
        is_closed: item.is_closed,
        is_default: item.is_default,
        sort_order: item.sort_order,
        active: item.active,
    };

    modalMode.value = "edit";
};

const closeModal = () => {
    modalMode.value = null;
    resetForm();
};

const submit = () => {
    if (!form.value.name.trim() || !form.value.code.trim()) {
        return;
    }

    processing.value = true;

    const options = {
        preserveScroll: true,

        onSuccess: () => {
            closeModal();
        },

        onFinish: () => {
            processing.value = false;
        },
    };

    if (activeTab.value === "requests") {
        const payload = {
            name: form.value.name,
            code: form.value.code,
            description: form.value.description || null,
            sla_minutes: form.value.sla_minutes,
            requires_approval: form.value.requires_approval,
            sort_order: form.value.sort_order,
            active: form.value.active,
        };

        if (modalMode.value === "edit" && selectedRequestType.value) {
            router.put(
                `/request-types/${selectedRequestType.value.id}`,
                payload,
                options,
            );
        } else {
            router.post("/request-types", payload, options);
        }

        return;
    }

    if (activeTab.value === "appointments") {
        const payload = {
            name: form.value.name,
            code: form.value.code,
            description: form.value.description || null,
            duration_minutes: form.value.duration_minutes,
            sort_order: form.value.sort_order,
            active: form.value.active,
        };

        if (modalMode.value === "edit" && selectedAppointmentType.value) {
            router.put(
                `/appointment-types/${selectedAppointmentType.value.id}`,
                payload,
                options,
            );
        } else {
            router.post("/appointment-types", payload, options);
        }

        return;
    }

    const payload = {
        name: form.value.name,
        code: form.value.code,
        is_closed: form.value.is_closed,
        is_default: form.value.is_default,
        sort_order: form.value.sort_order,
        active: form.value.active,
    };

    if (modalMode.value === "edit" && selectedTicketStatus.value) {
        router.put(
            `/ticket-statuses/${selectedTicketStatus.value.id}`,
            payload,
            options,
        );
    } else {
        router.post("/ticket-statuses", payload, options);
    }
};

const toggleRequestType = (item: RequestType) => {
    router.post(
        `/request-types/${item.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const toggleAppointmentType = (item: AppointmentType) => {
    router.post(
        `/appointment-types/${item.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const toggleTicketStatus = (item: TicketStatus) => {
    router.post(
        `/ticket-statuses/${item.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const setDefaultStatus = (item: TicketStatus) => {
    if (item.is_default) {
        return;
    }

    router.post(
        `/ticket-statuses/${item.id}/set-default`,
        {},
        {
            preserveScroll: true,
        },
    );
};

const destroyRequestType = (item: RequestType) => {
    if (!confirm(`Supprimer le type "${item.name}" ?`)) {
        return;
    }

    router.delete(`/request-types/${item.id}`, {
        preserveScroll: true,
    });
};

const destroyAppointmentType = (item: AppointmentType) => {
    if (!confirm(`Supprimer le type "${item.name}" ?`)) {
        return;
    }

    router.delete(`/appointment-types/${item.id}`, {
        preserveScroll: true,
    });
};

const destroyTicketStatus = (item: TicketStatus) => {
    if (!confirm(`Supprimer le statut "${item.name}" ?`)) {
        return;
    }

    router.delete(`/ticket-statuses/${item.id}`, {
        preserveScroll: true,
    });
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) {
        return "Aucun SLA";
    }

    if (minutes < 60) {
        return `${minutes} min`;
    }

    if (minutes % 60 === 0) {
        return `${minutes / 60} h`;
    }

    const hours = Math.floor(minutes / 60);
    const rest = minutes % 60;

    return `${hours} h ${rest} min`;
};

const modalTitle = computed(() => {
    const action = modalMode.value === "edit" ? "Modifier" : "Ajouter";

    if (activeTab.value === "requests") {
        return `${action} un type de demande`;
    }

    if (activeTab.value === "appointments") {
        return `${action} un type de rendez-vous`;
    }

    return `${action} un statut de ticket`;
});
</script>

<template>
    <Head title="Référentiels" />

    <div class="min-h-screen bg-slate-50/70 pb-12">
        <div class="mx-auto max-w-[1650px] space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- HERO -->

            <section
                class="relative overflow-hidden rounded-[32px] bg-slate-950 text-white shadow-xl"
            >
                <div
                    class="absolute -right-20 -top-24 size-80 rounded-full bg-emerald-500/20 blur-3xl"
                />

                <div
                    class="absolute -bottom-28 left-1/3 size-80 rounded-full bg-violet-500/15 blur-3xl"
                />

                <div class="relative p-6 lg:p-8">
                    <div
                        class="flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between"
                    >
                        <div>
                            <div
                                class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5"
                            >
                                <Sparkles class="size-3.5 text-emerald-300" />

                                <span
                                    class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-300"
                                >
                                    Configuration School Up
                                </span>
                            </div>

                            <h1
                                class="text-3xl font-black tracking-tight sm:text-4xl"
                            >
                                Centre des référentiels
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-6 text-slate-300"
                            >
                                Centralisez les paramètres fonctionnels utilisés
                                par les modules de School Up.
                            </p>
                        </div>

                        <button
                            type="button"
                            class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 text-sm font-black text-white shadow-lg transition hover:bg-emerald-400"
                            @click="openCreate"
                        >
                            <Plus class="size-4" />

                            Ajouter
                        </button>
                    </div>

                    <!-- STATS -->

                    <div class="mt-8 grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <strong class="text-2xl font-black">
                                    {{ totalItems }}
                                </strong>

                                <FolderCog class="size-5 text-emerald-300" />
                            </div>

                            <p class="mt-1 text-xs text-slate-400">
                                Référentiels
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <strong
                                class="text-2xl font-black text-emerald-300"
                            >
                                {{ activeItems }}
                            </strong>

                            <p class="mt-1 text-xs text-slate-400">
                                Éléments actifs
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <strong class="text-2xl font-black text-slate-300">
                                {{ inactiveItems }}
                            </strong>

                            <p class="mt-1 text-xs text-slate-400">
                                Éléments inactifs
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <strong class="text-2xl font-black text-blue-300">
                                {{ currentItemsCount }}
                            </strong>

                            <p class="mt-1 text-xs text-slate-400">
                                Dans la vue actuelle
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- MAIN NAV -->

            <section
                class="rounded-[26px] border border-slate-200 bg-white p-2 shadow-sm"
            >
                <div class="grid gap-2 lg:grid-cols-3">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        class="group relative overflow-hidden rounded-[20px] p-4 text-left transition"
                        :class="
                            activeTab === tab.id
                                ? 'bg-slate-950 text-white shadow-lg'
                                : 'hover:bg-slate-50'
                        "
                        @click="
                            activeTab = tab.id;
                            search = '';
                        "
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl"
                                :class="
                                    activeTab === tab.id
                                        ? 'bg-white/10 text-emerald-300'
                                        : tab.id === 'requests'
                                          ? 'bg-emerald-50 text-emerald-600'
                                          : tab.id === 'appointments'
                                            ? 'bg-blue-50 text-blue-600'
                                            : 'bg-violet-50 text-violet-600'
                                "
                            >
                                <component :is="tab.icon" class="size-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p
                                    class="font-black"
                                    :class="
                                        activeTab === tab.id
                                            ? 'text-white'
                                            : 'text-slate-900'
                                    "
                                >
                                    {{ tab.label }}
                                </p>

                                <p
                                    class="mt-1 truncate text-xs"
                                    :class="
                                        activeTab === tab.id
                                            ? 'text-slate-400'
                                            : 'text-slate-400'
                                    "
                                >
                                    {{ tab.description }}
                                </p>
                            </div>

                            <ChevronRight
                                class="size-4"
                                :class="
                                    activeTab === tab.id
                                        ? 'text-emerald-300'
                                        : 'text-slate-300'
                                "
                            />
                        </div>
                    </button>
                </div>
            </section>

            <!-- TOOLBAR -->

            <section
                class="flex flex-col gap-4 rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between"
            >
                <div>
                    <p
                        class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600"
                    >
                        {{ currentTab.short }}
                    </p>

                    <h2 class="mt-1 text-xl font-black text-slate-950">
                        {{ currentTab.label }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ currentTab.description }}
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative">
                        <Search
                            class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Rechercher..."
                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100 sm:w-72"
                        />
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-bold text-white transition hover:bg-emerald-600"
                        @click="openCreate"
                    >
                        <Plus class="size-4" />

                        Ajouter
                    </button>
                </div>
            </section>

            <!-- REQUEST TYPES -->

            <section
                v-if="activeTab === 'requests'"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="item in filteredRequestTypes"
                    :key="item.id"
                    class="group rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-lg"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                        >
                            <FileInput class="size-6" />
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-900"
                                @click="editRequestType(item)"
                            >
                                <Pencil class="size-4" />
                            </button>

                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-xl text-red-300 hover:bg-red-50 hover:text-red-600"
                                @click="destroyRequestType(item)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-lg font-black text-slate-950">
                                {{ item.name }}
                            </h3>

                            <span
                                class="rounded-lg bg-slate-100 px-2 py-1 text-[9px] font-black text-slate-500"
                            >
                                {{ item.code }}
                            </span>
                        </div>

                        <p
                            class="mt-2 min-h-10 text-sm leading-5 text-slate-500"
                        >
                            {{ item.description || "Aucune description." }}
                        </p>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <div
                                class="flex items-center gap-2 text-[10px] font-bold uppercase text-slate-400"
                            >
                                <AlarmClock class="size-3.5" />

                                SLA
                            </div>

                            <p class="mt-1 text-sm font-black text-slate-800">
                                {{ formatDuration(item.sla_minutes) }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <div
                                class="text-[10px] font-bold uppercase text-slate-400"
                            >
                                Approbation
                            </div>

                            <p
                                class="mt-1 text-sm font-black"
                                :class="
                                    item.requires_approval
                                        ? 'text-amber-600'
                                        : 'text-slate-600'
                                "
                            >
                                {{
                                    item.requires_approval
                                        ? "Requise"
                                        : "Non requise"
                                }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"
                    >
                        <span
                            class="text-xs font-bold"
                            :class="
                                item.active
                                    ? 'text-emerald-600'
                                    : 'text-slate-400'
                            "
                        >
                            {{ item.active ? "Actif" : "Inactif" }}
                        </span>

                        <button
                            type="button"
                            class="relative h-6 w-11 rounded-full transition"
                            :class="
                                item.active ? 'bg-emerald-500' : 'bg-slate-200'
                            "
                            @click="toggleRequestType(item)"
                        >
                            <span
                                class="absolute top-1 size-4 rounded-full bg-white shadow transition-all"
                                :class="item.active ? 'left-6' : 'left-1'"
                            />
                        </button>
                    </div>
                </article>
            </section>

            <!-- APPOINTMENT TYPES -->

            <section
                v-else-if="activeTab === 'appointments'"
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >
                <article
                    v-for="item in filteredAppointmentTypes"
                    :key="item.id"
                    class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg"
                >
                    <div class="flex items-start justify-between">
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600"
                        >
                            <CalendarClock class="size-6" />
                        </div>

                        <div class="flex gap-1">
                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100"
                                @click="editAppointmentType(item)"
                            >
                                <Pencil class="size-4" />
                            </button>

                            <button
                                type="button"
                                class="flex size-9 items-center justify-center rounded-xl text-red-300 hover:bg-red-50 hover:text-red-600"
                                @click="destroyAppointmentType(item)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-950">
                        {{ item.name }}
                    </h3>

                    <span
                        class="mt-2 inline-flex rounded-lg bg-blue-50 px-2 py-1 text-[9px] font-black text-blue-600"
                    >
                        {{ item.code }}
                    </span>

                    <p class="mt-3 min-h-10 text-sm text-slate-500">
                        {{ item.description || "Aucune description." }}
                    </p>

                    <div
                        class="mt-5 flex items-center gap-3 rounded-2xl bg-blue-50/70 p-4"
                    >
                        <Clock3 class="size-5 text-blue-600" />

                        <div>
                            <p
                                class="text-[10px] font-bold uppercase text-blue-400"
                            >
                                Durée
                            </p>

                            <p class="text-sm font-black text-blue-900">
                                {{ formatDuration(item.duration_minutes) }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"
                    >
                        <span
                            class="text-xs font-bold"
                            :class="
                                item.active ? 'text-blue-600' : 'text-slate-400'
                            "
                        >
                            {{ item.active ? "Actif" : "Inactif" }}
                        </span>

                        <button
                            type="button"
                            class="relative h-6 w-11 rounded-full transition"
                            :class="
                                item.active ? 'bg-blue-500' : 'bg-slate-200'
                            "
                            @click="toggleAppointmentType(item)"
                        >
                            <span
                                class="absolute top-1 size-4 rounded-full bg-white shadow transition-all"
                                :class="item.active ? 'left-6' : 'left-1'"
                            />
                        </button>
                    </div>
                </article>
            </section>

            <!-- TICKET STATUSES -->

            <section v-else class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article
                    v-for="item in filteredTicketStatuses"
                    :key="item.id"
                    class="relative overflow-hidden rounded-[24px] border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg"
                    :class="
                        item.is_default
                            ? 'border-violet-300 ring-4 ring-violet-50'
                            : 'border-slate-200'
                    "
                >
                    <div
                        v-if="item.is_default"
                        class="absolute right-0 top-0 rounded-bl-2xl bg-violet-600 px-3 py-1.5 text-[9px] font-black uppercase text-white"
                    >
                        Par défaut
                    </div>

                    <div class="flex items-start gap-3">
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl"
                            :class="
                                item.is_closed
                                    ? 'bg-slate-100 text-slate-600'
                                    : 'bg-violet-50 text-violet-600'
                            "
                        >
                            <CheckCircle2
                                v-if="item.is_closed"
                                class="size-6"
                            />

                            <CircleDot v-else class="size-6" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-slate-950">
                                {{ item.name }}
                            </h3>

                            <p
                                class="mt-1 text-[10px] font-black text-slate-400"
                            >
                                {{ item.code }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100"
                            @click="editTicketStatus(item)"
                        >
                            <Pencil class="size-4" />
                        </button>

                        <button
                            type="button"
                            class="flex size-9 items-center justify-center rounded-xl text-red-300 hover:bg-red-50 hover:text-red-600"
                            @click="destroyTicketStatus(item)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-2">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p
                                class="text-[10px] font-bold uppercase text-slate-400"
                            >
                                Type
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-700">
                                {{ item.is_closed ? "Fermé" : "Ouvert" }}
                            </p>
                        </div>

                        <div class="rounded-xl bg-slate-50 p-3">
                            <p
                                class="text-[10px] font-bold uppercase text-slate-400"
                            >
                                Tickets
                            </p>

                            <p class="mt-1 text-sm font-black text-slate-700">
                                {{ item.tickets_count ?? 0 }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center gap-2">
                        <button
                            v-if="!item.is_default"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-violet-50 px-3 py-2 text-[11px] font-bold text-violet-700 hover:bg-violet-100"
                            @click="setDefaultStatus(item)"
                        >
                            <Star class="size-3.5" />

                            Définir par défaut
                        </button>

                        <div class="ml-auto">
                            <button
                                type="button"
                                class="relative h-6 w-11 rounded-full transition"
                                :class="
                                    item.active
                                        ? 'bg-violet-500'
                                        : 'bg-slate-200'
                                "
                                @click="toggleTicketStatus(item)"
                            >
                                <span
                                    class="absolute top-1 size-4 rounded-full bg-white shadow transition-all"
                                    :class="item.active ? 'left-6' : 'left-1'"
                                />
                            </button>
                        </div>
                    </div>
                </article>
            </section>

            <!-- EMPTY -->

            <div
                v-if="
                    (activeTab === 'requests' &&
                        filteredRequestTypes.length === 0) ||
                    (activeTab === 'appointments' &&
                        filteredAppointmentTypes.length === 0) ||
                    (activeTab === 'statuses' &&
                        filteredTicketStatuses.length === 0)
                "
                class="rounded-[28px] border-2 border-dashed border-slate-200 bg-white py-16 text-center"
            >
                <Settings2 class="mx-auto size-10 text-slate-300" />

                <h3 class="mt-4 font-black text-slate-800">
                    Aucun élément trouvé
                </h3>

                <p class="mt-1 text-sm text-slate-400">
                    Ajoutez votre premier élément ou modifiez votre recherche.
                </p>
            </div>
        </div>
    </div>

    <!-- MODAL -->

    <Teleport to="body">
        <div
            v-if="modalMode"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                @click="closeModal"
            />

            <div
                class="relative z-10 max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-[28px] bg-white shadow-2xl"
            >
                <div
                    class="sticky top-0 z-10 flex items-start justify-between border-b border-slate-100 bg-white px-6 py-5"
                >
                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600"
                        >
                            Référentiels
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-950">
                            {{ modalTitle }}
                        </h2>
                    </div>

                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500"
                        @click="closeModal"
                    >
                        <X class="size-4" />
                    </button>
                </div>

                <form class="space-y-5 p-6" @submit.prevent="submit">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">
                                Nom *
                            </label>

                            <input
                                v-model="form.name"
                                required
                                type="text"
                                placeholder="Nom"
                                class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700">
                                Code *
                            </label>

                            <input
                                v-model="form.code"
                                required
                                type="text"
                                placeholder="CODE"
                                class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm uppercase outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>
                    </div>

                    <div v-if="activeTab !== 'statuses'" class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Description
                        </label>

                        <textarea
                            v-model="form.description"
                            rows="3"
                            placeholder="Description..."
                            class="w-full resize-none rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        />
                    </div>

                    <div v-if="activeTab === 'requests'" class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            SLA en minutes
                        </label>

                        <input
                            v-model.number="form.sla_minutes"
                            type="number"
                            min="1"
                            placeholder="Ex. 120"
                            class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none focus:border-emerald-400"
                        />
                    </div>

                    <div v-if="activeTab === 'appointments'" class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Durée du rendez-vous
                        </label>

                        <div class="relative">
                            <Clock3
                                class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model.number="form.duration_minutes"
                                type="number"
                                min="5"
                                max="1440"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 pl-11 pr-4 text-sm outline-none focus:border-blue-400"
                            />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Ordre d'affichage
                        </label>

                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="h-11 w-full rounded-xl border border-slate-200 px-4 text-sm outline-none"
                        />
                    </div>

                    <!-- REQUEST APPROVAL -->

                    <label
                        v-if="activeTab === 'requests'"
                        class="flex cursor-pointer items-center justify-between rounded-2xl border border-amber-100 bg-amber-50/60 p-4"
                    >
                        <div class="flex items-center gap-3">
                            <ShieldCheck class="size-5 text-amber-600" />

                            <div>
                                <p class="text-sm font-black text-slate-800">
                                    Approbation requise
                                </p>

                                <p class="text-xs text-slate-500">
                                    Cette demande nécessite une validation.
                                </p>
                            </div>
                        </div>

                        <input
                            v-model="form.requires_approval"
                            type="checkbox"
                            class="size-4 accent-amber-600"
                        />
                    </label>

                    <!-- STATUS OPTIONS -->

                    <div
                        v-if="activeTab === 'statuses'"
                        class="grid gap-3 sm:grid-cols-2"
                    >
                        <label
                            class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 p-4"
                        >
                            <div>
                                <p class="text-sm font-black text-slate-800">
                                    Statut fermé
                                </p>

                                <p class="text-xs text-slate-400">
                                    Clôture le ticket.
                                </p>
                            </div>

                            <input
                                v-model="form.is_closed"
                                type="checkbox"
                                class="size-4 accent-violet-600"
                            />
                        </label>

                        <label
                            class="flex cursor-pointer items-center justify-between rounded-2xl border border-violet-100 bg-violet-50/50 p-4"
                        >
                            <div>
                                <p class="text-sm font-black text-slate-800">
                                    Par défaut
                                </p>

                                <p class="text-xs text-slate-400">
                                    Statut initial.
                                </p>
                            </div>

                            <input
                                v-model="form.is_default"
                                type="checkbox"
                                class="size-4 accent-violet-600"
                            />
                        </label>
                    </div>

                    <!-- ACTIVE -->

                    <label
                        class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div class="flex items-center gap-3">
                            <Activity class="size-5 text-emerald-600" />

                            <div>
                                <p class="text-sm font-black text-slate-800">
                                    Élément actif
                                </p>

                                <p class="text-xs text-slate-400">
                                    Disponible dans l'application.
                                </p>
                            </div>
                        </div>

                        <input
                            v-model="form.active"
                            type="checkbox"
                            class="size-4 accent-emerald-600"
                        />
                    </label>

                    <div
                        class="flex justify-end gap-3 border-t border-slate-100 pt-5"
                    >
                        <button
                            type="button"
                            class="h-11 rounded-xl border border-slate-200 px-5 text-sm font-bold text-slate-600"
                            @click="closeModal"
                        >
                            Annuler
                        </button>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="inline-flex h-11 min-w-[145px] items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-black text-white transition hover:bg-emerald-600 disabled:opacity-50"
                        >
                            <span
                                v-if="processing"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            <Check v-else class="size-4" />

                            {{
                                processing
                                    ? "Enregistrement..."
                                    : modalMode === "edit"
                                      ? "Enregistrer"
                                      : "Ajouter"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
