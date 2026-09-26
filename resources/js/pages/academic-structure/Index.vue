<script setup lang="ts">
import { Head, router } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import {
    BookOpen,
    Building2,
    CalendarDays,
    Check,
    ChevronDown,
    ChevronRight,
    CirclePlus,
    GraduationCap,
    LayoutDashboard,
    Layers3,
    Pencil,
    Plus,
    Search,
    School,
    Trash2,
    Users,
    X,
} from "@lucide/vue";

/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

type SchoolClass = {
    id: number;
    name: string;
    code: string;
    capacity: number | null;
    active: boolean;
};

type Level = {
    id: number;
    name: string;
    code: string;
    sort_order: number;
    active: boolean;
    classes: SchoolClass[];
};

type Cycle = {
    id: number;
    name: string;
    code: string;
    sort_order: number;
    active: boolean;
    levels: Level[];
};

type SchoolYear = {
    id: number;
    name: string;
    start_date?: string | null;
    end_date?: string | null;
    is_current: boolean;
    active: boolean;
};

type Environment = {
    id: number;
    name: string;
    code: string;
};

type Tab = "overview" | "cycles" | "levels" | "classes";

type ModalType =
    | "cycle-create"
    | "cycle-edit"
    | "level-create"
    | "level-edit"
    | "class-create"
    | "class-edit"
    | null;

/*
|--------------------------------------------------------------------------
| Props
|--------------------------------------------------------------------------
*/

const props = defineProps<{
    currentEnvironment: Environment | null;
    schoolYears: SchoolYear[];
    selectedSchoolYear: SchoolYear | null;
    cycles: Cycle[];
}>();

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const activeTab = ref<Tab>("overview");
const searchQuery = ref("");
const processing = ref(false);

const expandedCycles = ref<number[]>(props.cycles.map((cycle) => cycle.id));

const modalType = ref<ModalType>(null);

const selectedCycle = ref<Cycle | null>(null);
const selectedLevel = ref<Level | null>(null);
const selectedClass = ref<SchoolClass | null>(null);

const form = ref({
    name: "",
    code: "",
    sort_order: 0,
    capacity: null as number | null,
    active: true,
});

/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const totalCycles = computed(() => props.cycles.length);

const totalLevels = computed(() =>
    props.cycles.reduce((total, cycle) => total + cycle.levels.length, 0),
);

const totalClasses = computed(() =>
    props.cycles.reduce(
        (total, cycle) =>
            total +
            cycle.levels.reduce((sum, level) => sum + level.classes.length, 0),
        0,
    ),
);

const totalCapacity = computed(() =>
    props.cycles.reduce(
        (total, cycle) =>
            total +
            cycle.levels.reduce(
                (levelTotal, level) =>
                    levelTotal +
                    level.classes.reduce(
                        (classTotal, schoolClass) =>
                            classTotal + Number(schoolClass.capacity ?? 0),
                        0,
                    ),
                0,
            ),
        0,
    ),
);

/*
|--------------------------------------------------------------------------
| Flattened data
|--------------------------------------------------------------------------
*/

const allLevels = computed(() =>
    props.cycles.flatMap((cycle) =>
        cycle.levels.map((level) => ({
            ...level,
            cycle,
        })),
    ),
);

const allClasses = computed(() =>
    props.cycles.flatMap((cycle) =>
        cycle.levels.flatMap((level) =>
            level.classes.map((schoolClass) => ({
                ...schoolClass,
                cycle,
                level,
            })),
        ),
    ),
);

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const normalizedSearch = computed(() => searchQuery.value.trim().toLowerCase());

const filteredCycles = computed(() => {
    if (!normalizedSearch.value) {
        return props.cycles;
    }

    return props.cycles.filter((cycle) =>
        [cycle.name, cycle.code].some((value) =>
            value.toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

const filteredLevels = computed(() => {
    if (!normalizedSearch.value) {
        return allLevels.value;
    }

    return allLevels.value.filter((item) =>
        [item.name, item.code, item.cycle.name, item.cycle.code].some((value) =>
            value.toLowerCase().includes(normalizedSearch.value),
        ),
    );
});

const filteredClasses = computed(() => {
    if (!normalizedSearch.value) {
        return allClasses.value;
    }

    return allClasses.value.filter((item) =>
        [
            item.name,
            item.code,
            item.level.name,
            item.level.code,
            item.cycle.name,
            item.cycle.code,
        ].some((value) => value.toLowerCase().includes(normalizedSearch.value)),
    );
});

/*
|--------------------------------------------------------------------------
| Tabs
|--------------------------------------------------------------------------
*/

const tabs = [
    {
        id: "overview" as Tab,
        label: "Vue d'ensemble",
        icon: LayoutDashboard,
    },
    {
        id: "cycles" as Tab,
        label: "Cycles",
        icon: Layers3,
    },
    {
        id: "levels" as Tab,
        label: "Niveaux",
        icon: BookOpen,
    },
    {
        id: "classes" as Tab,
        label: "Classes",
        icon: School,
    },
];

/*
|--------------------------------------------------------------------------
| Cycle colors
|--------------------------------------------------------------------------
*/

const cyclePalette = (index: number) => {
    const palettes = [
        {
            soft: "bg-emerald-50",
            text: "text-emerald-700",
            border: "border-emerald-200",
            gradient: "from-emerald-500 to-teal-400",
            dot: "bg-emerald-500",
        },
        {
            soft: "bg-blue-50",
            text: "text-blue-700",
            border: "border-blue-200",
            gradient: "from-blue-500 to-cyan-400",
            dot: "bg-blue-500",
        },
        {
            soft: "bg-violet-50",
            text: "text-violet-700",
            border: "border-violet-200",
            gradient: "from-violet-500 to-fuchsia-400",
            dot: "bg-violet-500",
        },
        {
            soft: "bg-amber-50",
            text: "text-amber-700",
            border: "border-amber-200",
            gradient: "from-amber-500 to-orange-400",
            dot: "bg-amber-500",
        },
        {
            soft: "bg-rose-50",
            text: "text-rose-700",
            border: "border-rose-200",
            gradient: "from-rose-500 to-pink-400",
            dot: "bg-rose-500",
        },
    ];

    return palettes[index % palettes.length];
};

/*
|--------------------------------------------------------------------------
| Expand
|--------------------------------------------------------------------------
*/

const isCycleExpanded = (id: number) => expandedCycles.value.includes(id);

const toggleCycle = (id: number) => {
    if (isCycleExpanded(id)) {
        expandedCycles.value = expandedCycles.value.filter(
            (cycleId) => cycleId !== id,
        );
    } else {
        expandedCycles.value.push(id);
    }
};

/*
|--------------------------------------------------------------------------
| School year
|--------------------------------------------------------------------------
*/

const changeSchoolYear = (event: Event) => {
    const target = event.target as HTMLSelectElement;

    router.get(
        "/academic-structure",
        {
            school_year_id: target.value,
        },
        {
            preserveScroll: true,
            preserveState: false,
        },
    );
};

/*
|--------------------------------------------------------------------------
| Modal
|--------------------------------------------------------------------------
*/

const resetForm = () => {
    form.value = {
        name: "",
        code: "",
        sort_order: 0,
        capacity: null,
        active: true,
    };
};

const closeModal = () => {
    modalType.value = null;
    selectedCycle.value = null;
    selectedLevel.value = null;
    selectedClass.value = null;

    resetForm();
};

const openCreateCycle = () => {
    resetForm();

    form.value.sort_order =
        props.cycles.length > 0
            ? Math.max(...props.cycles.map((cycle) => cycle.sort_order ?? 0)) +
              1
            : 1;

    modalType.value = "cycle-create";
};

const openEditCycle = (cycle: Cycle) => {
    selectedCycle.value = cycle;

    form.value = {
        name: cycle.name,
        code: cycle.code,
        sort_order: cycle.sort_order ?? 0,
        capacity: null,
        active: cycle.active,
    };

    modalType.value = "cycle-edit";
};

const openCreateLevel = (cycle: Cycle) => {
    selectedCycle.value = cycle;

    resetForm();

    form.value.sort_order =
        cycle.levels.length > 0
            ? Math.max(...cycle.levels.map((level) => level.sort_order ?? 0)) +
              1
            : 1;

    modalType.value = "level-create";
};

const openEditLevel = (cycle: Cycle, level: Level) => {
    selectedCycle.value = cycle;
    selectedLevel.value = level;

    form.value = {
        name: level.name,
        code: level.code,
        sort_order: level.sort_order ?? 0,
        capacity: null,
        active: level.active,
    };

    modalType.value = "level-edit";
};

const openCreateClass = (cycle: Cycle, level: Level) => {
    selectedCycle.value = cycle;
    selectedLevel.value = level;

    resetForm();

    modalType.value = "class-create";
};

const openEditClass = (
    cycle: Cycle,
    level: Level,
    schoolClass: SchoolClass,
) => {
    selectedCycle.value = cycle;
    selectedLevel.value = level;
    selectedClass.value = schoolClass;

    form.value = {
        name: schoolClass.name,
        code: schoolClass.code,
        sort_order: 0,
        capacity: schoolClass.capacity,
        active: schoolClass.active,
    };

    modalType.value = "class-edit";
};

/*
|--------------------------------------------------------------------------
| Modal labels
|--------------------------------------------------------------------------
*/

const modalTitle = computed(() => {
    switch (modalType.value) {
        case "cycle-create":
            return "Ajouter un cycle";

        case "cycle-edit":
            return "Modifier le cycle";

        case "level-create":
            return "Ajouter un niveau";

        case "level-edit":
            return "Modifier le niveau";

        case "class-create":
            return "Ajouter une classe";

        case "class-edit":
            return "Modifier la classe";

        default:
            return "";
    }
});

const modalDescription = computed(() => {
    switch (modalType.value) {
        case "cycle-create":
            return "Créez un nouveau cycle pédagogique.";

        case "cycle-edit":
            return "Modifiez les informations du cycle.";

        case "level-create":
            return selectedCycle.value
                ? `Ajouter un niveau au cycle ${selectedCycle.value.name}.`
                : "";

        case "level-edit":
            return "Modifiez les informations du niveau.";

        case "class-create":
            return selectedLevel.value
                ? `Ajouter une classe au niveau ${selectedLevel.value.name}.`
                : "";

        case "class-edit":
            return "Modifiez les informations de la classe.";

        default:
            return "";
    }
});

const isClassModal = computed(() =>
    ["class-create", "class-edit"].includes(modalType.value ?? ""),
);

const isEditModal = computed(() =>
    ["cycle-edit", "level-edit", "class-edit"].includes(modalType.value ?? ""),
);

/*
|--------------------------------------------------------------------------
| Submit
|--------------------------------------------------------------------------
*/

const submitForm = () => {
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

    if (modalType.value === "cycle-create") {
        router.post(
            "/cycles",
            {
                name: form.value.name,
                code: form.value.code,
                sort_order: form.value.sort_order,
                active: form.value.active,
            },
            options,
        );

        return;
    }

    if (modalType.value === "cycle-edit" && selectedCycle.value) {
        router.put(
            `/cycles/${selectedCycle.value.id}`,
            {
                name: form.value.name,
                code: form.value.code,
                sort_order: form.value.sort_order,
                active: form.value.active,
            },
            options,
        );

        return;
    }

    if (modalType.value === "level-create" && selectedCycle.value) {
        router.post(
            "/levels",
            {
                cycle_id: selectedCycle.value.id,
                name: form.value.name,
                code: form.value.code,
                sort_order: form.value.sort_order,
                active: form.value.active,
            },
            options,
        );

        return;
    }

    if (modalType.value === "level-edit" && selectedLevel.value) {
        router.put(
            `/levels/${selectedLevel.value.id}`,
            {
                name: form.value.name,
                code: form.value.code,
                sort_order: form.value.sort_order,
                active: form.value.active,
            },
            options,
        );

        return;
    }

    if (
        modalType.value === "class-create" &&
        selectedLevel.value &&
        props.selectedSchoolYear
    ) {
        router.post(
            "/school-classes",
            {
                level_id: selectedLevel.value.id,
                school_year_id: props.selectedSchoolYear.id,
                name: form.value.name,
                code: form.value.code,
                capacity: form.value.capacity,
                active: form.value.active,
            },
            options,
        );

        return;
    }

    if (modalType.value === "class-edit" && selectedClass.value) {
        router.put(
            `/school-classes/${selectedClass.value.id}`,
            {
                name: form.value.name,
                code: form.value.code,
                capacity: form.value.capacity,
                active: form.value.active,
            },
            options,
        );
    }
};

/*
|--------------------------------------------------------------------------
| Delete
|--------------------------------------------------------------------------
*/

const deleteCycle = (cycle: Cycle) => {
    if (
        !confirm(
            `Supprimer le cycle "${cycle.name}" ?\n\nLes niveaux et les classes associés seront également supprimés.`,
        )
    ) {
        return;
    }

    router.delete(`/cycles/${cycle.id}`, {
        preserveScroll: true,
    });
};

const deleteLevel = (level: Level) => {
    if (!confirm(`Supprimer le niveau "${level.name}" ?`)) {
        return;
    }

    router.delete(`/levels/${level.id}`, {
        preserveScroll: true,
    });
};

const deleteClass = (schoolClass: SchoolClass) => {
    if (!confirm(`Supprimer la classe "${schoolClass.name}" ?`)) {
        return;
    }

    router.delete(`/school-classes/${schoolClass.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Structure pédagogique" />

    <div class="min-h-screen bg-slate-50/70 pb-12">
        <div class="mx-auto max-w-[1650px] space-y-6 p-4 sm:p-6 lg:p-8">
            <!-- ================================================= -->
            <!-- HERO -->
            <!-- ================================================= -->

            <section
                class="relative overflow-hidden rounded-[32px] bg-slate-950 text-white shadow-xl"
            >
                <!-- Decorative -->
                <div
                    class="absolute -right-32 -top-32 size-96 rounded-full bg-emerald-500/20 blur-3xl"
                />

                <div
                    class="absolute -bottom-40 left-20 size-96 rounded-full bg-blue-500/10 blur-3xl"
                />

                <div class="relative p-6 lg:p-8">
                    <div
                        class="flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between"
                    >
                        <!-- Left -->

                        <div class="max-w-3xl">
                            <div class="mb-4 flex items-center gap-2">
                                <span
                                    class="size-2 rounded-full bg-emerald-400"
                                />

                                <span
                                    class="text-[10px] font-black uppercase tracking-[0.22em] text-emerald-300"
                                >
                                    Academic Structure
                                </span>
                            </div>

                            <h1
                                class="text-3xl font-black tracking-tight sm:text-4xl"
                            >
                                Structure pédagogique
                            </h1>

                            <p
                                class="mt-3 max-w-2xl text-sm leading-6 text-slate-300"
                            >
                                Organisez facilement les cycles, niveaux et
                                classes de votre établissement.
                            </p>

                            <div class="mt-5 flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-xs font-semibold"
                                >
                                    <Building2
                                        class="size-3.5 text-emerald-300"
                                    />

                                    {{
                                        currentEnvironment?.name ??
                                        "Établissement"
                                    }}
                                </span>

                                <span
                                    v-if="selectedSchoolYear"
                                    class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/10 px-3 py-1.5 text-xs font-semibold"
                                >
                                    <CalendarDays
                                        class="size-3.5 text-blue-300"
                                    />

                                    {{ selectedSchoolYear.name }}
                                </span>
                            </div>
                        </div>

                        <!-- Right -->

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div v-if="schoolYears.length" class="relative">
                                <select
                                    :value="selectedSchoolYear?.id ?? ''"
                                    class="h-12 min-w-[220px] appearance-none rounded-2xl border border-white/10 bg-white/10 pl-4 pr-11 text-sm font-bold text-white outline-none transition hover:bg-white/15"
                                    @change="changeSchoolYear"
                                >
                                    <option
                                        v-for="year in schoolYears"
                                        :key="year.id"
                                        :value="year.id"
                                        class="text-slate-900"
                                    >
                                        {{ year.name }}
                                        {{
                                            year.is_current ? " • Courante" : ""
                                        }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-4 top-1/2 size-4 -translate-y-1/2 text-slate-300"
                                />
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-12 items-center justify-center gap-2 rounded-2xl bg-emerald-500 px-5 text-sm font-black text-white shadow-lg shadow-emerald-950/20 transition hover:bg-emerald-400"
                                @click="openCreateCycle"
                            >
                                <Plus class="size-4" />

                                Nouveau cycle
                            </button>
                        </div>
                    </div>

                    <!-- Stats -->

                    <div class="mt-8 grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-black text-white">
                                    {{ totalCycles }}
                                </p>

                                <Layers3 class="size-5 text-emerald-300" />
                            </div>

                            <p
                                class="mt-1 text-xs font-semibold text-slate-400"
                            >
                                Cycles
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-black text-blue-300">
                                    {{ totalLevels }}
                                </p>

                                <BookOpen class="size-5 text-blue-300" />
                            </div>

                            <p
                                class="mt-1 text-xs font-semibold text-slate-400"
                            >
                                Niveaux
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-black text-violet-300">
                                    {{ totalClasses }}
                                </p>

                                <School class="size-5 text-violet-300" />
                            </div>

                            <p
                                class="mt-1 text-xs font-semibold text-slate-400"
                            >
                                Classes
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-white/10 bg-white/[0.06] p-4"
                        >
                            <div class="flex items-center justify-between">
                                <p class="text-2xl font-black text-amber-300">
                                    {{ totalCapacity }}
                                </p>

                                <Users class="size-5 text-amber-300" />
                            </div>

                            <p
                                class="mt-1 text-xs font-semibold text-slate-400"
                            >
                                Capacité totale
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ================================================= -->
            <!-- TABS -->
            <!-- ================================================= -->

            <section
                class="rounded-[24px] border border-slate-200 bg-white p-2 shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between"
                >
                    <nav class="grid grid-cols-2 gap-1 sm:flex">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            type="button"
                            class="flex items-center justify-center gap-2 rounded-2xl px-4 py-3 text-sm font-bold transition"
                            :class="
                                activeTab === tab.id
                                    ? 'bg-slate-950 text-white shadow-lg'
                                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'
                            "
                            @click="activeTab = tab.id"
                        >
                            <component :is="tab.icon" class="size-4" />

                            {{ tab.label }}
                        </button>
                    </nav>

                    <div
                        v-if="activeTab !== 'overview'"
                        class="relative xl:w-80"
                    >
                        <Search
                            class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                        />

                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Rechercher..."
                            class="h-11 w-full rounded-2xl border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm outline-none transition focus:border-emerald-300 focus:ring-4 focus:ring-emerald-100"
                        />
                    </div>
                </div>
            </section>

            <!-- ================================================= -->
            <!-- OVERVIEW -->
            <!-- ================================================= -->

            <section v-if="activeTab === 'overview'" class="space-y-5">
                <div
                    class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between"
                >
                    <div>
                        <p
                            class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-600"
                        >
                            Cartographie académique
                        </p>

                        <h2 class="mt-1 text-xl font-black text-slate-950">
                            Organisation de l'établissement
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Cycle → Niveau → Classe
                        </p>
                    </div>

                    <span class="text-xs font-bold text-slate-400">
                        {{ totalCycles }} cycles · {{ totalLevels }} niveaux ·
                        {{ totalClasses }} classes
                    </span>
                </div>

                <!-- Empty -->

                <div
                    v-if="cycles.length === 0"
                    class="rounded-[28px] border-2 border-dashed border-slate-200 bg-white px-6 py-16 text-center"
                >
                    <div
                        class="mx-auto flex size-16 items-center justify-center rounded-3xl bg-emerald-50 text-emerald-600"
                    >
                        <GraduationCap class="size-8" />
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-900">
                        Aucun cycle
                    </h3>

                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                        Commencez par créer le premier cycle pédagogique.
                    </p>

                    <button
                        type="button"
                        class="mt-5 inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white"
                        @click="openCreateCycle"
                    >
                        <Plus class="size-4" />
                        Ajouter un cycle
                    </button>
                </div>

                <!-- Cycle -->

                <article
                    v-for="(cycle, cycleIndex) in cycles"
                    :key="cycle.id"
                    class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="h-1.5 bg-gradient-to-r"
                        :class="cyclePalette(cycleIndex).gradient"
                    />

                    <!-- Cycle header -->

                    <div class="flex items-center gap-3 p-4 sm:p-5">
                        <button
                            type="button"
                            class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200"
                            @click="toggleCycle(cycle.id)"
                        >
                            <ChevronDown
                                v-if="isCycleExpanded(cycle.id)"
                                class="size-4"
                            />

                            <ChevronRight v-else class="size-4" />
                        </button>

                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-2xl"
                            :class="[
                                cyclePalette(cycleIndex).soft,
                                cyclePalette(cycleIndex).text,
                            ]"
                        >
                            <GraduationCap class="size-6" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3
                                    class="truncate text-lg font-black text-slate-950"
                                >
                                    {{ cycle.name }}
                                </h3>

                                <span
                                    class="rounded-lg px-2 py-1 text-[10px] font-black"
                                    :class="[
                                        cyclePalette(cycleIndex).soft,
                                        cyclePalette(cycleIndex).text,
                                    ]"
                                >
                                    {{ cycle.code }}
                                </span>

                                <span
                                    class="rounded-full px-2 py-1 text-[9px] font-black"
                                    :class="
                                        cycle.active
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    {{ cycle.active ? "ACTIF" : "INACTIF" }}
                                </span>
                            </div>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ cycle.levels.length }}
                                niveaux ·
                                {{
                                    cycle.levels.reduce(
                                        (sum, level) =>
                                            sum + level.classes.length,
                                        0,
                                    )
                                }}
                                classes
                            </p>
                        </div>

                        <button
                            type="button"
                            class="hidden items-center gap-2 rounded-xl border px-4 py-2.5 text-xs font-black transition hover:shadow-sm sm:inline-flex"
                            :class="[
                                cyclePalette(cycleIndex).border,
                                cyclePalette(cycleIndex).soft,
                                cyclePalette(cycleIndex).text,
                            ]"
                            @click="openCreateLevel(cycle)"
                        >
                            <Plus class="size-4" />
                            Niveau
                        </button>

                        <button
                            type="button"
                            title="Modifier le cycle"
                            class="flex size-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-900"
                            @click="openEditCycle(cycle)"
                        >
                            <Pencil class="size-4" />
                        </button>

                        <button
                            type="button"
                            title="Supprimer le cycle"
                            class="flex size-10 items-center justify-center rounded-xl text-slate-300 transition hover:bg-red-50 hover:text-red-600"
                            @click="deleteCycle(cycle)"
                        >
                            <Trash2 class="size-4" />
                        </button>
                    </div>

                    <!-- Levels -->

                    <div
                        v-if="isCycleExpanded(cycle.id)"
                        class="border-t border-slate-100 bg-slate-50/70 p-4 sm:p-6"
                    >
                        <div
                            v-if="cycle.levels.length"
                            class="grid gap-4 lg:grid-cols-2 2xl:grid-cols-3"
                        >
                            <div
                                v-for="level in cycle.levels"
                                :key="level.id"
                                class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                            >
                                <!-- Level header -->

                                <div class="flex items-center gap-3 p-4">
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                    >
                                        <BookOpen class="size-5" />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <p
                                                class="truncate font-black text-slate-900"
                                            >
                                                {{ level.name }}
                                            </p>

                                            <span
                                                v-if="!level.active"
                                                class="rounded-full bg-slate-100 px-2 py-0.5 text-[8px] font-black text-slate-400"
                                            >
                                                INACTIF
                                            </span>
                                        </div>

                                        <p
                                            class="mt-0.5 text-[11px] font-medium text-slate-400"
                                        >
                                            {{ level.code }}
                                            ·
                                            {{ level.classes.length }}
                                            classes
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-blue-50 hover:text-blue-600"
                                        @click="openEditLevel(cycle, level)"
                                    >
                                        <Pencil class="size-3.5" />
                                    </button>

                                    <button
                                        type="button"
                                        class="flex size-8 items-center justify-center rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-600"
                                        @click="deleteLevel(level)"
                                    >
                                        <Trash2 class="size-3.5" />
                                    </button>
                                </div>

                                <!-- Classes -->

                                <div class="border-t border-slate-100 p-3">
                                    <div
                                        v-if="level.classes.length"
                                        class="flex flex-wrap gap-2"
                                    >
                                        <button
                                            v-for="schoolClass in level.classes"
                                            :key="schoolClass.id"
                                            type="button"
                                            class="group/class inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-800"
                                            @click="
                                                openEditClass(
                                                    cycle,
                                                    level,
                                                    schoolClass,
                                                )
                                            "
                                        >
                                            <span
                                                class="size-2 rounded-full"
                                                :class="
                                                    schoolClass.active
                                                        ? 'bg-emerald-500'
                                                        : 'bg-slate-300'
                                                "
                                            />

                                            {{ schoolClass.name }}

                                            <span
                                                v-if="schoolClass.capacity"
                                                class="text-[10px] font-semibold text-slate-400"
                                            >
                                                {{ schoolClass.capacity }}
                                            </span>
                                        </button>
                                    </div>

                                    <p
                                        v-else
                                        class="py-2 text-center text-xs text-slate-400"
                                    >
                                        Aucune classe
                                    </p>

                                    <button
                                        type="button"
                                        class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-slate-300 py-2.5 text-xs font-bold text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-700"
                                        @click="openCreateClass(cycle, level)"
                                    >
                                        <CirclePlus class="size-4" />

                                        Ajouter une classe
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Add level -->

                        <button
                            type="button"
                            class="mt-4 flex w-full items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-slate-200 bg-white py-3.5 text-sm font-bold text-slate-500 transition hover:border-blue-300 hover:bg-blue-50/50 hover:text-blue-700"
                            @click="openCreateLevel(cycle)"
                        >
                            <Plus class="size-4" />

                            Ajouter un niveau à
                            {{ cycle.name }}
                        </button>
                    </div>
                </article>

                <!-- Add cycle -->

                <button
                    type="button"
                    class="flex w-full items-center justify-center gap-2 rounded-[22px] border-2 border-dashed border-slate-200 bg-white py-5 text-sm font-black text-slate-500 transition hover:border-emerald-300 hover:bg-emerald-50/50 hover:text-emerald-700"
                    @click="openCreateCycle"
                >
                    <Plus class="size-4" />

                    Ajouter un nouveau cycle
                </button>
            </section>

            <!-- ================================================= -->
            <!-- CYCLES TAB -->
            <!-- ================================================= -->

            <section v-else-if="activeTab === 'cycles'" class="space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-black text-slate-950">
                            Cycles pédagogiques
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            {{ filteredCycles.length }}
                            cycle(s)
                        </p>
                    </div>

                    <button
                        type="button"
                        class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-xs font-bold text-white"
                        @click="openCreateCycle"
                    >
                        <Plus class="size-4" />
                        Ajouter
                    </button>
                </div>

                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="(cycle, index) in filteredCycles"
                        :key="cycle.id"
                        class="overflow-hidden rounded-[26px] border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <div
                            class="h-2 bg-gradient-to-r"
                            :class="cyclePalette(index).gradient"
                        />

                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div
                                    class="flex size-12 items-center justify-center rounded-2xl"
                                    :class="[
                                        cyclePalette(index).soft,
                                        cyclePalette(index).text,
                                    ]"
                                >
                                    <Layers3 class="size-6" />
                                </div>

                                <span
                                    class="rounded-full px-2.5 py-1 text-[9px] font-black"
                                    :class="
                                        cycle.active
                                            ? 'bg-emerald-50 text-emerald-700'
                                            : 'bg-slate-100 text-slate-400'
                                    "
                                >
                                    {{ cycle.active ? "ACTIF" : "INACTIF" }}
                                </span>
                            </div>

                            <h3 class="mt-5 text-xl font-black text-slate-950">
                                {{ cycle.name }}
                            </h3>

                            <p class="mt-1 text-xs font-bold text-slate-400">
                                {{ cycle.code }}
                            </p>

                            <div class="mt-5 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <b class="text-lg text-slate-900">
                                        {{ cycle.levels.length }}
                                    </b>

                                    <p class="text-[11px] text-slate-400">
                                        Niveaux
                                    </p>
                                </div>

                                <div class="rounded-xl bg-slate-50 p-3">
                                    <b class="text-lg text-slate-900">
                                        {{
                                            cycle.levels.reduce(
                                                (sum, level) =>
                                                    sum + level.classes.length,
                                                0,
                                            )
                                        }}
                                    </b>

                                    <p class="text-[11px] text-slate-400">
                                        Classes
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 flex gap-2">
                                <button
                                    type="button"
                                    class="flex-1 rounded-xl bg-slate-950 py-2.5 text-xs font-bold text-white transition hover:bg-emerald-600"
                                    @click="openCreateLevel(cycle)"
                                >
                                    + Niveau
                                </button>

                                <button
                                    type="button"
                                    class="flex size-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50"
                                    @click="openEditCycle(cycle)"
                                >
                                    <Pencil class="size-4" />
                                </button>

                                <button
                                    type="button"
                                    class="flex size-10 items-center justify-center rounded-xl border border-red-100 text-red-400 hover:bg-red-50 hover:text-red-600"
                                    @click="deleteCycle(cycle)"
                                >
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>
                    </article>
                </div>
            </section>

            <!-- ================================================= -->
            <!-- LEVELS TAB -->
            <!-- ================================================= -->

            <section v-else-if="activeTab === 'levels'" class="space-y-4">
                <div>
                    <h2 class="text-xl font-black text-slate-950">
                        Tous les niveaux
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ filteredLevels.length }}
                        niveau(x)
                    </p>
                </div>

                <div
                    v-for="item in filteredLevels"
                    :key="item.id"
                    class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center"
                >
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                    >
                        <BookOpen class="size-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-black text-slate-900">
                                {{ item.name }}
                            </h3>

                            <span
                                class="rounded-lg bg-blue-50 px-2 py-1 text-[10px] font-black text-blue-600"
                            >
                                {{ item.code }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ item.cycle.name }} ·
                            {{ item.classes.length }}
                            classes
                        </p>
                    </div>

                    <button
                        type="button"
                        class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700"
                        @click="openCreateClass(item.cycle, item)"
                    >
                        + Classe
                    </button>

                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100"
                        @click="openEditLevel(item.cycle, item)"
                    >
                        <Pencil class="size-4" />
                    </button>

                    <button
                        type="button"
                        class="flex size-9 items-center justify-center rounded-xl text-red-400 hover:bg-red-50"
                        @click="deleteLevel(item)"
                    >
                        <Trash2 class="size-4" />
                    </button>
                </div>
            </section>

            <!-- ================================================= -->
            <!-- CLASSES TAB -->
            <!-- ================================================= -->

            <section v-else class="space-y-5">
                <div>
                    <h2 class="text-xl font-black text-slate-950">
                        Toutes les classes
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ filteredClasses.length }}
                        classe(s)
                    </p>
                </div>

                <div
                    class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                >
                    <article
                        v-for="item in filteredClasses"
                        :key="item.id"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                    >
                        <div class="flex items-start gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                            >
                                <School class="size-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-black text-slate-900">
                                    {{ item.name }}
                                </h3>

                                <p
                                    class="mt-0.5 text-[11px] font-bold text-slate-400"
                                >
                                    {{ item.code }}
                                </p>
                            </div>

                            <button
                                type="button"
                                class="flex size-8 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100"
                                @click="
                                    openEditClass(item.cycle, item.level, item)
                                "
                            >
                                <Pencil class="size-4" />
                            </button>

                            <button
                                type="button"
                                class="flex size-8 items-center justify-center rounded-lg text-red-400 hover:bg-red-50"
                                @click="deleteClass(item)"
                            >
                                <Trash2 class="size-4" />
                            </button>
                        </div>

                        <div class="mt-4 rounded-xl bg-slate-50 p-3">
                            <p class="text-xs font-black text-slate-700">
                                {{ item.level.name }}
                            </p>

                            <p class="mt-1 text-[11px] text-slate-400">
                                {{ item.cycle.name }}
                            </p>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div
                                class="flex items-center gap-2 text-xs text-slate-400"
                            >
                                <Users class="size-3.5" />
                                Capacité
                            </div>

                            <b class="text-sm text-slate-900">
                                {{ item.capacity ?? "—" }}
                            </b>
                        </div>

                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-xs text-slate-400"> Statut </span>

                            <span
                                class="rounded-full px-2 py-1 text-[9px] font-black"
                                :class="
                                    item.active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-100 text-slate-400'
                                "
                            >
                                {{ item.active ? "ACTIF" : "INACTIF" }}
                            </span>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </div>

    <!-- ========================================================= -->
    <!-- MODAL -->
    <!-- ========================================================= -->

    <Teleport to="body">
        <div
            v-if="modalType"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        >
            <button
                type="button"
                class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                @click="closeModal"
            />

            <div
                class="relative z-10 w-full max-w-lg overflow-hidden rounded-[28px] bg-white shadow-2xl"
            >
                <!-- Modal Header -->

                <div class="border-b border-slate-100 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex gap-3">
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                            >
                                <School v-if="isClassModal" class="size-5" />

                                <GraduationCap v-else class="size-5" />
                            </div>

                            <div>
                                <h2 class="text-lg font-black text-slate-950">
                                    {{ modalTitle }}
                                </h2>

                                <p
                                    class="mt-1 text-xs leading-5 text-slate-500"
                                >
                                    {{ modalDescription }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500 transition hover:bg-slate-200"
                            @click="closeModal"
                        >
                            <X class="size-4" />
                        </button>
                    </div>
                </div>

                <!-- Form -->

                <form class="space-y-5 p-6" @submit.prevent="submitForm">
                    <!-- Name -->

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Nom
                            <span class="text-red-500"> * </span>
                        </label>

                        <input
                            v-model="form.name"
                            type="text"
                            required
                            :placeholder="
                                isClassModal
                                    ? 'Ex. CP-A'
                                    : modalType?.includes('level')
                                      ? 'Ex. CP'
                                      : 'Ex. Primaire'
                            "
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none transition placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        />
                    </div>

                    <!-- Code -->

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Code
                            <span class="text-red-500"> * </span>
                        </label>

                        <input
                            v-model="form.code"
                            type="text"
                            required
                            placeholder="Ex. CP-A"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm uppercase text-slate-800 outline-none transition placeholder:normal-case placeholder:text-slate-300 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        />
                    </div>

                    <!-- Sort order -->

                    <div v-if="!isClassModal" class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Ordre d'affichage
                        </label>

                        <input
                            v-model.number="form.sort_order"
                            type="number"
                            min="0"
                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                        />
                    </div>

                    <!-- Capacity -->

                    <div v-if="isClassModal" class="space-y-2">
                        <label class="text-xs font-bold text-slate-700">
                            Capacité
                        </label>

                        <div class="relative">
                            <Users
                                class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                            />

                            <input
                                v-model.number="form.capacity"
                                type="number"
                                min="0"
                                placeholder="Ex. 30"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 text-sm outline-none transition focus:border-emerald-400 focus:ring-4 focus:ring-emerald-100"
                            />
                        </div>
                    </div>

                    <!-- Active -->

                    <label
                        class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200 bg-slate-50 px-4 py-3"
                    >
                        <div>
                            <p class="text-sm font-bold text-slate-700">
                                Élément actif
                            </p>

                            <p class="mt-0.5 text-[11px] text-slate-400">
                                Disponible dans School Up
                            </p>
                        </div>

                        <input
                            v-model="form.active"
                            type="checkbox"
                            class="size-4 accent-emerald-600"
                        />
                    </label>

                    <!-- Buttons -->

                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-100 pt-5"
                    >
                        <button
                            type="button"
                            class="h-11 rounded-xl border border-slate-200 px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                            @click="closeModal"
                        >
                            Annuler
                        </button>

                        <button
                            type="submit"
                            :disabled="processing"
                            class="inline-flex h-11 min-w-[140px] items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 text-sm font-bold text-white shadow-lg transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                v-if="processing"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            <Check v-else class="size-4" />

                            {{
                                processing
                                    ? "Enregistrement..."
                                    : isEditModal
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
