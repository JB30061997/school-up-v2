<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    ArrowLeft,
    ArrowRight,
    BookOpen,
    Building2,
    Check,
    GraduationCap,
    LockKeyhole,
    Mail,
    School,
    ShieldCheck,
    Sparkles,
    User,
    UserCog,
    Users,
} from "lucide-vue-next";

/* ==========================================================================
   TYPES
   ========================================================================== */

type RegistrationRole = {
    code: "student" | "parent" | "teacher" | "admin";
    name: string;
    requires_approval: boolean;
};

type Environment = {
    id: number;
    name: string;
    code: string;
    app_name?: string | null;
    logo?: string | null;
    primary_color?: string | null;
};

type SchoolYear = {
    id: number;
    name: string;
    start_date?: string | null;
    end_date?: string | null;
    is_current?: boolean;
};

type Cycle = {
    id: number;
    name: string;
    code: string;
    sort_order?: number;
};

type Level = {
    id: number;
    name: string;
    code: string;
    sort_order?: number;
};

type SchoolClass = {
    id: number;
    name: string;
    code: string;
    capacity?: number | null;
};

/* ==========================================================================
   PROPS
   ========================================================================== */

const props = defineProps<{
    passwordRules?: string;
    environments: Environment[];
    registrationRoles: RegistrationRole[];
}>();

/* ==========================================================================
   FORM
   ========================================================================== */

const form = useForm({
    role: "",

    environment_id: null as number | null,

    school_year_id: null as number | null,
    cycle_id: null as number | null,
    level_id: null as number | null,
    school_class_id: null as number | null,

    first_name: "",
    last_name: "",
    username: "",
    email: "",
    phone: "",

    password: "",
    password_confirmation: "",
});

/* ==========================================================================
   STATE
   ========================================================================== */

const currentStep = ref(0);

const schoolYears = ref<SchoolYear[]>([]);
const cycles = ref<Cycle[]>([]);
const levels = ref<Level[]>([]);
const classes = ref<SchoolClass[]>([]);

const loading = ref(false);
const loadingMessage = ref("");
const structureError = ref<string | null>(null);

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

/* ==========================================================================
   ROLE CONFIG
   ========================================================================== */

const roleConfig = {
    student: {
        title: "Élève",
        description: "Accédez à votre espace scolaire.",
        icon: GraduationCap,
    },

    parent: {
        title: "Parent",
        description: "Suivez la scolarité de vos enfants.",
        icon: Users,
    },

    teacher: {
        title: "Professeur",
        description: "Accédez à votre espace enseignant.",
        icon: BookOpen,
    },

    admin: {
        title: "Administrateur",
        description: "Accédez aux outils de gestion.",
        icon: UserCog,
    },
} as const;

/* ==========================================================================
   STEPS
   ========================================================================== */

const studentSteps = [
    { key: "role", label: "Profil" },
    { key: "environment", label: "Établissement" },
    { key: "school_year", label: "Année" },
    { key: "cycle", label: "Cycle" },
    { key: "level", label: "Niveau" },
    { key: "class", label: "Classe" },
    { key: "account", label: "Compte" },
];

const standardSteps = [
    { key: "role", label: "Profil" },
    { key: "environment", label: "Établissement" },
    { key: "account", label: "Compte" },
];

const steps = computed(() => {
    return form.role === "student" ? studentSteps : standardSteps;
});

const currentStepDefinition = computed(() => {
    return steps.value[currentStep.value];
});

const currentStepKey = computed(() => {
    return currentStepDefinition.value?.key ?? "role";
});

const progress = computed(() => {
    if (steps.value.length <= 1) {
        return 0;
    }

    return (currentStep.value / (steps.value.length - 1)) * 100;
});

/* ==========================================================================
   SELECTED VALUES
   ========================================================================== */

const selectedRole = computed(() => {
    return props.registrationRoles.find((role) => role.code === form.role);
});

const selectedEnvironment = computed(() => {
    return props.environments.find(
        (environment) => environment.id === form.environment_id,
    );
});

const selectedSchoolYear = computed(() => {
    return schoolYears.value.find((year) => year.id === form.school_year_id);
});

const selectedCycle = computed(() => {
    return cycles.value.find((cycle) => cycle.id === form.cycle_id);
});

const selectedLevel = computed(() => {
    return levels.value.find((level) => level.id === form.level_id);
});

const selectedClass = computed(() => {
    return classes.value.find((item) => item.id === form.school_class_id);
});

/* ==========================================================================
   HELPERS
   ========================================================================== */

const initials = (value: string) => {
    return value
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
};

const clearStructureAfterEnvironment = () => {
    form.school_year_id = null;
    form.cycle_id = null;
    form.level_id = null;
    form.school_class_id = null;

    schoolYears.value = [];
    cycles.value = [];
    levels.value = [];
    classes.value = [];
};

const clearStructureAfterYear = () => {
    form.cycle_id = null;
    form.level_id = null;
    form.school_class_id = null;

    cycles.value = [];
    levels.value = [];
    classes.value = [];
};

const clearStructureAfterCycle = () => {
    form.level_id = null;
    form.school_class_id = null;

    levels.value = [];
    classes.value = [];
};

const clearStructureAfterLevel = () => {
    form.school_class_id = null;
    classes.value = [];
};

/* ==========================================================================
   API
   ========================================================================== */

const fetchJson = async <T,>(url: string, message: string): Promise<T[]> => {
    loading.value = true;
    loadingMessage.value = message;
    structureError.value = null;

    try {
        const response = await fetch(url, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
        });

        if (!response.ok) {
            throw new Error(`Erreur HTTP ${response.status}`);
        }

        const result = await response.json();

        return result.data ?? [];
    } catch (error) {
        console.error(error);

        structureError.value =
            "Impossible de charger les informations. Veuillez réessayer.";

        return [];
    } finally {
        loading.value = false;
        loadingMessage.value = "";
    }
};

const loadSchoolYears = async () => {
    if (!form.environment_id) {
        return false;
    }

    clearStructureAfterEnvironment();

    schoolYears.value = await fetchJson<SchoolYear>(
        `/registration/environments/${form.environment_id}/school-years`,
        "Chargement des années scolaires...",
    );

    const current = schoolYears.value.find((year) => year.is_current);

    if (current) {
        form.school_year_id = current.id;
    } else if (schoolYears.value.length === 1) {
        form.school_year_id = schoolYears.value[0].id;
    }

    return schoolYears.value.length > 0;
};

const loadCycles = async () => {
    if (!form.environment_id || !form.school_year_id) {
        return false;
    }

    clearStructureAfterYear();

    cycles.value = await fetchJson<Cycle>(
        `/registration/environments/${form.environment_id}/school-years/${form.school_year_id}/cycles`,
        "Chargement des cycles...",
    );

    return cycles.value.length > 0;
};

const loadLevels = async () => {
    if (!form.environment_id || !form.school_year_id || !form.cycle_id) {
        return false;
    }

    clearStructureAfterCycle();

    levels.value = await fetchJson<Level>(
        `/registration/environments/${form.environment_id}/school-years/${form.school_year_id}/cycles/${form.cycle_id}/levels`,
        "Chargement des niveaux...",
    );

    return levels.value.length > 0;
};

const loadClasses = async () => {
    if (
        !form.environment_id ||
        !form.school_year_id ||
        !form.cycle_id ||
        !form.level_id
    ) {
        return false;
    }

    clearStructureAfterLevel();

    classes.value = await fetchJson<SchoolClass>(
        `/registration/environments/${form.environment_id}/school-years/${form.school_year_id}/cycles/${form.cycle_id}/levels/${form.level_id}/classes`,
        "Chargement des classes...",
    );

    return classes.value.length > 0;
};

/* ==========================================================================
   SELECT
   ========================================================================== */

const selectRole = (role: RegistrationRole) => {
    form.role = role.code;

    if (role.code !== "student") {
        form.school_year_id = null;
        form.cycle_id = null;
        form.level_id = null;
        form.school_class_id = null;
    }
};

const selectEnvironment = (environment: Environment) => {
    if (form.environment_id !== environment.id) {
        clearStructureAfterEnvironment();
    }

    form.environment_id = environment.id;
};

const selectSchoolYear = (year: SchoolYear) => {
    if (form.school_year_id !== year.id) {
        clearStructureAfterYear();
    }

    form.school_year_id = year.id;
};

const selectCycle = (cycle: Cycle) => {
    if (form.cycle_id !== cycle.id) {
        clearStructureAfterCycle();
    }

    form.cycle_id = cycle.id;
};

const selectLevel = (level: Level) => {
    if (form.level_id !== level.id) {
        clearStructureAfterLevel();
    }

    form.level_id = level.id;
};

const selectClass = (item: SchoolClass) => {
    form.school_class_id = item.id;
};

/* ==========================================================================
   VALIDATION
   ========================================================================== */

const canContinue = computed(() => {
    switch (currentStepKey.value) {
        case "role":
            return Boolean(form.role);

        case "environment":
            return Boolean(form.environment_id);

        case "school_year":
            return Boolean(form.school_year_id);

        case "cycle":
            return Boolean(form.cycle_id);

        case "level":
            return Boolean(form.level_id);

        case "class":
            return Boolean(form.school_class_id);

        case "account":
            return (
                form.first_name.trim().length > 0 &&
                form.last_name.trim().length > 0 &&
                form.username.trim().length >= 3 &&
                form.email.trim().length > 0 &&
                form.password.length >= 8 &&
                form.password_confirmation.length >= 8 &&
                form.password === form.password_confirmation
            );

        default:
            return false;
    }
});

/* ==========================================================================
   NAVIGATION
   ========================================================================== */

const next = async () => {
    structureError.value = null;

    if (!canContinue.value || loading.value) {
        return;
    }

    if (currentStepKey.value === "role") {
        currentStep.value++;
        return;
    }

    if (currentStepKey.value === "environment") {
        if (form.role !== "student") {
            currentStep.value++;
            return;
        }

        const loaded = await loadSchoolYears();

        if (!loaded) {
            structureError.value =
                "Aucune année scolaire active n'est disponible pour cet établissement.";
            return;
        }

        currentStep.value++;
        return;
    }

    if (currentStepKey.value === "school_year") {
        const loaded = await loadCycles();

        if (!loaded) {
            structureError.value =
                "Aucun cycle disponible pour cette année scolaire.";
            return;
        }

        currentStep.value++;
        return;
    }

    if (currentStepKey.value === "cycle") {
        const loaded = await loadLevels();

        if (!loaded) {
            structureError.value = "Aucun niveau disponible pour ce cycle.";
            return;
        }

        currentStep.value++;
        return;
    }

    if (currentStepKey.value === "level") {
        const loaded = await loadClasses();

        if (!loaded) {
            structureError.value = "Aucune classe disponible pour ce niveau.";
            return;
        }

        currentStep.value++;
        return;
    }

    if (currentStepKey.value === "class") {
        currentStep.value++;
    }
};

const previous = () => {
    if (currentStep.value > 0) {
        structureError.value = null;
        currentStep.value--;
    }
};

const goToStep = (index: number) => {
    if (index < currentStep.value) {
        structureError.value = null;
        currentStep.value = index;
    }
};

/* ==========================================================================
   SUBMIT
   ========================================================================== */

const submit = () => {
    if (!canContinue.value) {
        return;
    }

    form.post("/register", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Créer un compte" />

    <div class="h-screen overflow-hidden bg-[#f7f8fc] text-slate-900">
        <div class="grid h-full lg:grid-cols-[400px_1fr]">
            <!-- ========================================================= -->
            <!-- SIDEBAR                                                   -->
            <!-- ========================================================= -->

            <aside
                class="relative hidden h-screen overflow-hidden bg-[#070b18] px-8 py-6 text-white lg:flex lg:flex-col"
            >
                <!-- Decorative -->

                <div
                    class="pointer-events-none absolute -left-40 -top-40 size-[400px] rounded-full bg-violet-600/20 blur-[100px]"
                />

                <div
                    class="pointer-events-none absolute -bottom-48 -right-40 size-[450px] rounded-full bg-indigo-600/20 blur-[120px]"
                />

                <!-- Brand -->

                <div class="relative z-10">
                    <Link href="/" class="inline-flex items-center gap-3">
                        <div
                            class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 shadow-lg shadow-violet-950/30"
                        >
                            <School class="size-5" />
                        </div>

                        <div>
                            <p class="text-base font-black tracking-tight">
                                School Up
                            </p>

                            <p
                                class="text-[9px] font-bold uppercase tracking-[0.18em] text-slate-500"
                            >
                                Espace scolaire
                            </p>
                        </div>
                    </Link>

                    <div class="mt-7">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[9px] font-bold uppercase tracking-[0.14em] text-violet-300"
                        >
                            <Sparkles class="size-3" />
                            Inscription
                        </span>

                        <h1
                            class="mt-4 text-[30px] font-black leading-[1.08] tracking-tight"
                        >
                            Votre espace scolaire,
                            <span class="text-violet-400">
                                en quelques étapes.
                            </span>
                        </h1>

                        <p
                            class="mt-3 max-w-sm text-[13px] leading-6 text-slate-400"
                        >
                            Sélectionnez votre profil et votre établissement.
                            School Up adapte automatiquement votre parcours
                            d'inscription.
                        </p>
                    </div>
                </div>

                <!-- Steps -->

                <div class="relative z-10 mt-5 space-y-1">
                    <button
                        v-for="(step, index) in steps"
                        :key="step.key"
                        type="button"
                        class="group flex w-full items-center gap-3 rounded-xl px-4 py-2 text-left transition"
                        :class="[
                            index === currentStep ? 'bg-white/10' : '',
                            index < currentStep
                                ? 'cursor-pointer hover:bg-white/5'
                                : 'cursor-default',
                        ]"
                        @click="goToStep(index)"
                    >
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg border text-[11px] font-black transition"
                            :class="
                                index < currentStep
                                    ? 'border-emerald-400/30 bg-emerald-400/10 text-emerald-400'
                                    : index === currentStep
                                      ? 'border-violet-400 bg-violet-500 text-white shadow-md shadow-violet-950/30'
                                      : 'border-white/10 bg-white/5 text-slate-600'
                            "
                        >
                            <Check v-if="index < currentStep" class="size-4" />

                            <span v-else>
                                {{ index + 1 }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <p
                                class="text-[8px] font-bold uppercase tracking-[0.14em]"
                                :class="
                                    index === currentStep
                                        ? 'text-violet-300'
                                        : 'text-slate-600'
                                "
                            >
                                Étape {{ index + 1 }}
                            </p>

                            <p
                                class="mt-0.5 text-[13px] font-bold"
                                :class="
                                    index <= currentStep
                                        ? 'text-white'
                                        : 'text-slate-600'
                                "
                            >
                                {{ step.label }}
                            </p>
                        </div>
                    </button>
                </div>

                <!-- Security -->

                <div class="relative z-10 mt-auto pt-3">
                    <div
                        class="rounded-2xl border border-white/10 bg-white/[0.04] p-4"
                    >
                        <div class="flex items-start gap-3">
                            <ShieldCheck
                                class="mt-0.5 size-4 shrink-0 text-emerald-400"
                            />

                            <div>
                                <p class="text-[11px] font-bold text-white">
                                    Inscription sécurisée
                                </p>

                                <p
                                    class="mt-1 text-[10px] leading-4 text-slate-500"
                                >
                                    Les accès sensibles nécessitent une
                                    validation avant activation.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- ========================================================= -->
            <!-- MAIN                                                      -->
            <!-- ========================================================= -->

            <main
                class="relative flex h-screen min-h-0 flex-col overflow-hidden"
            >
                <!-- Mobile header -->

                <header
                    class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-5 py-3 lg:hidden"
                >
                    <Link href="/" class="flex items-center gap-3">
                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-[#070b18] text-white"
                        >
                            <School class="size-4" />
                        </div>

                        <span class="font-black"> School Up </span>
                    </Link>

                    <span
                        class="rounded-full bg-violet-50 px-3 py-1.5 text-[10px] font-black text-violet-700"
                    >
                        {{ currentStep + 1 }}/{{ steps.length }}
                    </span>
                </header>

                <!-- Mobile progress -->

                <div class="h-1 shrink-0 bg-slate-100 lg:hidden">
                    <div
                        class="h-full bg-violet-600 transition-all duration-500"
                        :style="{
                            width: `${progress}%`,
                        }"
                    />
                </div>

                <!-- Content -->

                <div
                    class="flex min-h-0 flex-1 items-center justify-center overflow-y-auto px-5 py-4 sm:px-8 lg:overflow-hidden lg:px-10 lg:py-4"
                >
                    <div class="w-full max-w-[900px]">
                        <!-- Progress desktop -->

                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <p
                                    class="text-[10px] font-black uppercase tracking-[0.16em] text-violet-600"
                                >
                                    Étape {{ currentStep + 1 }} sur
                                    {{ steps.length }}
                                </p>

                                <p class="text-[10px] font-bold text-slate-400">
                                    {{ currentStepDefinition?.label }}
                                </p>
                            </div>

                            <div
                                class="mt-2 h-1 overflow-hidden rounded-full bg-slate-200"
                            >
                                <div
                                    class="h-full rounded-full bg-gradient-to-r from-violet-600 to-indigo-500 transition-all duration-500"
                                    :style="{
                                        width: `${progress}%`,
                                    }"
                                />
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- ROLE                                              -->
                        <!-- ================================================= -->

                        <section v-if="currentStepKey === 'role'">
                            <h2
                                class="text-3xl font-black tracking-tight text-slate-950"
                            >
                                Vous êtes ?
                            </h2>

                            <p
                                class="mt-2 text-[13px] leading-5 text-slate-500"
                            >
                                Choisissez le profil qui correspond à votre
                                utilisation de School Up.
                            </p>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <button
                                    v-for="role in registrationRoles"
                                    :key="role.code"
                                    type="button"
                                    class="group relative rounded-[20px] border bg-white p-5 text-left shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                    :class="
                                        form.role === role.code
                                            ? 'border-violet-500 ring-4 ring-violet-50'
                                            : 'border-slate-200 hover:border-violet-200'
                                    "
                                    @click="selectRole(role)"
                                >
                                    <div
                                        v-if="form.role === role.code"
                                        class="absolute right-4 top-4 flex size-6 items-center justify-center rounded-full bg-violet-600 text-white"
                                    >
                                        <Check class="size-3.5" />
                                    </div>

                                    <div
                                        class="flex size-11 items-center justify-center rounded-xl"
                                        :class="{
                                            'bg-violet-50 text-violet-600':
                                                role.code === 'student',
                                            'bg-sky-50 text-sky-600':
                                                role.code === 'parent',
                                            'bg-emerald-50 text-emerald-600':
                                                role.code === 'teacher',
                                            'bg-amber-50 text-amber-600':
                                                role.code === 'admin',
                                        }"
                                    >
                                        <component
                                            :is="roleConfig[role.code].icon"
                                            class="size-5"
                                        />
                                    </div>

                                    <h3 class="mt-3 text-base font-black">
                                        {{ role.name }}
                                    </h3>

                                    <p
                                        class="mt-1 text-xs leading-5 text-slate-500"
                                    >
                                        {{ roleConfig[role.code].description }}
                                    </p>

                                    <div
                                        v-if="role.requires_approval"
                                        class="mt-3 inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-[9px] font-black text-amber-700"
                                    >
                                        Validation requise
                                    </div>
                                </button>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- ENVIRONMENT                                       -->
                        <!-- ================================================= -->

                        <section v-else-if="currentStepKey === 'environment'">
                            <h2
                                class="text-3xl font-black tracking-tight text-slate-950"
                            >
                                Votre établissement
                            </h2>

                            <p class="mt-2 text-[13px] text-slate-500">
                                Sélectionnez l'école à laquelle votre compte
                                sera rattaché.
                            </p>

                            <div
                                v-if="environments.length"
                                class="mt-5 grid gap-3 sm:grid-cols-2"
                            >
                                <button
                                    v-for="environment in environments"
                                    :key="environment.id"
                                    type="button"
                                    class="relative flex items-center gap-4 rounded-[20px] border bg-white p-4 text-left shadow-sm transition hover:shadow-md"
                                    :class="
                                        form.environment_id === environment.id
                                            ? 'border-violet-500 ring-4 ring-violet-50'
                                            : 'border-slate-200 hover:border-violet-200'
                                    "
                                    @click="selectEnvironment(environment)"
                                >
                                    <div
                                        class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-slate-50"
                                    >
                                        <img
                                            v-if="environment.logo"
                                            :src="`/storage/${environment.logo}`"
                                            class="max-h-9 max-w-9 object-contain"
                                        />

                                        <span
                                            v-else
                                            class="text-xs font-black text-violet-600"
                                        >
                                            {{ initials(environment.name) }}
                                        </span>
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <h3 class="truncate text-sm font-black">
                                            {{ environment.name }}
                                        </h3>

                                        <p
                                            class="mt-1 font-mono text-[9px] font-bold text-slate-400"
                                        >
                                            {{ environment.code }}
                                        </p>
                                    </div>

                                    <Check
                                        v-if="
                                            form.environment_id ===
                                            environment.id
                                        "
                                        class="size-5 text-violet-600"
                                    />
                                </button>
                            </div>

                            <div
                                v-else
                                class="mt-5 rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center"
                            >
                                <Building2
                                    class="mx-auto size-7 text-slate-300"
                                />

                                <p
                                    class="mt-3 text-sm font-bold text-slate-600"
                                >
                                    Aucun établissement disponible.
                                </p>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- SCHOOL YEAR                                       -->
                        <!-- ================================================= -->

                        <section v-else-if="currentStepKey === 'school_year'">
                            <h2 class="text-3xl font-black tracking-tight">
                                Année scolaire
                            </h2>

                            <p class="mt-2 text-[13px] text-slate-500">
                                Sélectionnez votre année scolaire.
                            </p>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <button
                                    v-for="year in schoolYears"
                                    :key="year.id"
                                    type="button"
                                    class="relative rounded-[20px] border bg-white p-5 text-left shadow-sm transition"
                                    :class="
                                        form.school_year_id === year.id
                                            ? 'border-violet-500 ring-4 ring-violet-50'
                                            : 'border-slate-200 hover:border-violet-200'
                                    "
                                    @click="selectSchoolYear(year)"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div
                                            class="flex size-11 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
                                        >
                                            <School class="size-5" />
                                        </div>

                                        <span
                                            v-if="year.is_current"
                                            class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black uppercase text-emerald-700"
                                        >
                                            Actuelle
                                        </span>
                                    </div>

                                    <h3 class="mt-4 text-lg font-black">
                                        {{ year.name }}
                                    </h3>
                                </button>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- CYCLE                                             -->
                        <!-- ================================================= -->

                        <section v-else-if="currentStepKey === 'cycle'">
                            <h2 class="text-3xl font-black tracking-tight">
                                Votre cycle
                            </h2>

                            <p class="mt-2 text-[13px] text-slate-500">
                                Dans quel cycle êtes-vous inscrit ?
                            </p>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <button
                                    v-for="cycle in cycles"
                                    :key="cycle.id"
                                    type="button"
                                    class="rounded-[20px] border bg-white p-5 text-left shadow-sm transition"
                                    :class="
                                        form.cycle_id === cycle.id
                                            ? 'border-violet-500 ring-4 ring-violet-50'
                                            : 'border-slate-200 hover:border-violet-200'
                                    "
                                    @click="selectCycle(cycle)"
                                >
                                    <div
                                        class="flex size-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                                    >
                                        <BookOpen class="size-5" />
                                    </div>

                                    <h3 class="mt-3 text-base font-black">
                                        {{ cycle.name }}
                                    </h3>

                                    <p
                                        class="mt-1 font-mono text-[9px] font-bold text-slate-400"
                                    >
                                        {{ cycle.code }}
                                    </p>
                                </button>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- LEVEL                                             -->
                        <!-- ================================================= -->

                        <section v-else-if="currentStepKey === 'level'">
                            <h2 class="text-3xl font-black tracking-tight">
                                Votre niveau
                            </h2>

                            <p class="mt-2 text-[13px] text-slate-500">
                                Sélectionnez votre niveau scolaire.
                            </p>

                            <div
                                class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <button
                                    v-for="level in levels"
                                    :key="level.id"
                                    type="button"
                                    class="rounded-[18px] border bg-white p-4 text-left shadow-sm transition"
                                    :class="
                                        form.level_id === level.id
                                            ? 'border-violet-500 ring-4 ring-violet-50'
                                            : 'border-slate-200 hover:border-violet-200'
                                    "
                                    @click="selectLevel(level)"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div
                                            class="flex size-9 items-center justify-center rounded-xl bg-sky-50 text-sky-600"
                                        >
                                            <GraduationCap class="size-4" />
                                        </div>

                                        <Check
                                            v-if="form.level_id === level.id"
                                            class="size-4 text-violet-600"
                                        />
                                    </div>

                                    <h3 class="mt-3 text-sm font-black">
                                        {{ level.name }}
                                    </h3>

                                    <p
                                        class="mt-1 font-mono text-[9px] font-bold text-slate-400"
                                    >
                                        {{ level.code }}
                                    </p>
                                </button>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- CLASS                                             -->
                        <!-- ================================================= -->

                        <section v-else-if="currentStepKey === 'class'">
                            <h2 class="text-3xl font-black tracking-tight">
                                Votre classe
                            </h2>

                            <p class="mt-2 text-[13px] text-slate-500">
                                Dernière étape de votre rattachement scolaire.
                            </p>

                            <div
                                class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                            >
                                <button
                                    v-for="schoolClass in classes"
                                    :key="schoolClass.id"
                                    type="button"
                                    class="rounded-[18px] border bg-white p-4 text-left shadow-sm transition"
                                    :class="
                                        form.school_class_id === schoolClass.id
                                            ? 'border-violet-500 ring-4 ring-violet-50'
                                            : 'border-slate-200 hover:border-violet-200'
                                    "
                                    @click="selectClass(schoolClass)"
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <div
                                            class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                        >
                                            <School class="size-4" />
                                        </div>

                                        <Check
                                            v-if="
                                                form.school_class_id ===
                                                schoolClass.id
                                            "
                                            class="size-4 text-violet-600"
                                        />
                                    </div>

                                    <h3 class="mt-3 text-sm font-black">
                                        {{ schoolClass.name }}
                                    </h3>

                                    <p
                                        class="mt-1 font-mono text-[9px] font-bold text-slate-400"
                                    >
                                        {{ schoolClass.code }}
                                    </p>
                                </button>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- ACCOUNT                                           -->
                        <!-- ================================================= -->

                        <section v-else>
                            <!-- Heading -->

                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h2
                                        class="text-3xl font-black tracking-tight text-slate-950"
                                    >
                                        Créez votre compte
                                    </h2>

                                    <p
                                        class="mt-1.5 text-[13px] text-slate-500"
                                    >
                                        Renseignez vos informations de
                                        connexion.
                                    </p>
                                </div>

                                <div
                                    v-if="selectedRole?.requires_approval"
                                    class="shrink-0 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2"
                                >
                                    <div class="flex items-center gap-2">
                                        <ShieldCheck
                                            class="size-3.5 text-amber-600"
                                        />

                                        <span
                                            class="text-[10px] font-black text-amber-800"
                                        >
                                            Validation requise
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Summary -->

                            <div class="mt-3 flex flex-wrap gap-1.5">
                                <span
                                    v-if="selectedRole"
                                    class="rounded-full bg-violet-50 px-2.5 py-1 text-[9px] font-black text-violet-700"
                                >
                                    {{ selectedRole.name }}
                                </span>

                                <span
                                    v-if="selectedEnvironment"
                                    class="rounded-full bg-slate-100 px-2.5 py-1 text-[9px] font-black text-slate-600"
                                >
                                    {{ selectedEnvironment.name }}
                                </span>

                                <span
                                    v-if="selectedSchoolYear"
                                    class="rounded-full bg-indigo-50 px-2.5 py-1 text-[9px] font-black text-indigo-700"
                                >
                                    {{ selectedSchoolYear.name }}
                                </span>

                                <span
                                    v-if="selectedCycle"
                                    class="rounded-full bg-sky-50 px-2.5 py-1 text-[9px] font-black text-sky-700"
                                >
                                    {{ selectedCycle.name }}
                                </span>

                                <span
                                    v-if="selectedLevel"
                                    class="rounded-full bg-emerald-50 px-2.5 py-1 text-[9px] font-black text-emerald-700"
                                >
                                    {{ selectedLevel.name }}
                                </span>

                                <span
                                    v-if="selectedClass"
                                    class="rounded-full bg-amber-50 px-2.5 py-1 text-[9px] font-black text-amber-700"
                                >
                                    {{ selectedClass.name }}
                                </span>
                            </div>

                            <!-- Form card -->

                            <div
                                class="mt-4 rounded-[22px] border border-slate-200 bg-white p-5 shadow-sm"
                            >
                                <div
                                    class="grid gap-x-5 gap-y-3 sm:grid-cols-2"
                                >
                                    <!-- First name -->

                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Prénom *
                                        </label>

                                        <input
                                            v-model="form.first_name"
                                            type="text"
                                            autocomplete="given-name"
                                            placeholder="Votre prénom"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                        />

                                        <p
                                            v-if="form.errors.first_name"
                                            class="mt-1 text-[10px] font-bold text-rose-500"
                                        >
                                            {{ form.errors.first_name }}
                                        </p>
                                    </div>

                                    <!-- Last name -->

                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Nom *
                                        </label>

                                        <input
                                            v-model="form.last_name"
                                            type="text"
                                            autocomplete="family-name"
                                            placeholder="Votre nom"
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                        />

                                        <p
                                            v-if="form.errors.last_name"
                                            class="mt-1 text-[10px] font-bold text-rose-500"
                                        >
                                            {{ form.errors.last_name }}
                                        </p>
                                    </div>

                                    <!-- Username -->

                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Identifiant *
                                        </label>

                                        <div class="relative">
                                            <User
                                                class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <input
                                                v-model="form.username"
                                                type="text"
                                                autocomplete="username"
                                                placeholder="ex. jaouad.braouz"
                                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-11 pr-4 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                            />
                                        </div>

                                        <p
                                            v-if="form.errors.username"
                                            class="mt-1 text-[10px] font-bold text-rose-500"
                                        >
                                            {{ form.errors.username }}
                                        </p>
                                    </div>

                                    <!-- Email -->

                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Adresse e-mail *
                                        </label>

                                        <div class="relative">
                                            <Mail
                                                class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <input
                                                v-model="form.email"
                                                type="email"
                                                autocomplete="email"
                                                placeholder="nom@exemple.com"
                                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-11 pr-4 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                            />
                                        </div>

                                        <p
                                            v-if="form.errors.email"
                                            class="mt-1 text-[10px] font-bold text-rose-500"
                                        >
                                            {{ form.errors.email }}
                                        </p>
                                    </div>

                                    <!-- Phone -->

                                    <div class="sm:col-span-2">
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Téléphone
                                        </label>

                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            autocomplete="tel"
                                            placeholder="+212 ..."
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-4 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                        />

                                        <p
                                            v-if="form.errors.phone"
                                            class="mt-1 text-[10px] font-bold text-rose-500"
                                        >
                                            {{ form.errors.phone }}
                                        </p>
                                    </div>

                                    <!-- Password -->

                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Mot de passe *
                                        </label>

                                        <div class="relative">
                                            <LockKeyhole
                                                class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <input
                                                v-model="form.password"
                                                :type="
                                                    showPassword
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                autocomplete="new-password"
                                                placeholder="••••••••"
                                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-11 pr-16 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                            />

                                            <button
                                                type="button"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-violet-600"
                                                @click="
                                                    showPassword = !showPassword
                                                "
                                            >
                                                {{
                                                    showPassword
                                                        ? "Masquer"
                                                        : "Afficher"
                                                }}
                                            </button>
                                        </div>

                                        <p
                                            v-if="form.errors.password"
                                            class="mt-1 text-[10px] font-bold text-rose-500"
                                        >
                                            {{ form.errors.password }}
                                        </p>
                                    </div>

                                    <!-- Password confirmation -->

                                    <div>
                                        <label
                                            class="mb-1.5 block text-[11px] font-black text-slate-700"
                                        >
                                            Confirmer le mot de passe *
                                        </label>

                                        <div class="relative">
                                            <LockKeyhole
                                                class="absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <input
                                                v-model="
                                                    form.password_confirmation
                                                "
                                                :type="
                                                    showPasswordConfirmation
                                                        ? 'text'
                                                        : 'password'
                                                "
                                                autocomplete="new-password"
                                                placeholder="••••••••"
                                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 pl-11 pr-16 text-[13px] font-semibold outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-50"
                                            />

                                            <button
                                                type="button"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-[9px] font-black text-violet-600"
                                                @click="
                                                    showPasswordConfirmation =
                                                        !showPasswordConfirmation
                                                "
                                            >
                                                {{
                                                    showPasswordConfirmation
                                                        ? "Masquer"
                                                        : "Afficher"
                                                }}
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Password info -->

                                <div
                                    class="mt-3 flex items-center gap-2 text-[10px] text-slate-400"
                                >
                                    <ShieldCheck
                                        class="size-3.5 shrink-0 text-emerald-500"
                                    />

                                    <span>
                                        Utilisez au minimum 8 caractères pour
                                        sécuriser votre compte.
                                    </span>
                                </div>

                                <!-- Password mismatch -->

                                <p
                                    v-if="
                                        form.password_confirmation &&
                                        form.password !==
                                            form.password_confirmation
                                    "
                                    class="mt-2 text-[10px] font-bold text-rose-500"
                                >
                                    Les deux mots de passe ne correspondent pas.
                                </p>
                            </div>
                        </section>

                        <!-- ================================================= -->
                        <!-- ERROR                                             -->
                        <!-- ================================================= -->

                        <div
                            v-if="structureError"
                            class="mt-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-xs font-bold text-rose-700"
                        >
                            {{ structureError }}
                        </div>

                        <!-- ================================================= -->
                        <!-- LOADING                                           -->
                        <!-- ================================================= -->

                        <div
                            v-if="loading"
                            class="mt-3 flex items-center gap-3 rounded-xl border border-violet-100 bg-violet-50 px-4 py-3"
                        >
                            <div
                                class="size-4 animate-spin rounded-full border-2 border-violet-200 border-t-violet-600"
                            />

                            <p class="text-[11px] font-bold text-violet-700">
                                {{ loadingMessage }}
                            </p>
                        </div>

                        <!-- ================================================= -->
                        <!-- NAVIGATION                                        -->
                        <!-- ================================================= -->

                        <div
                            class="mt-5 flex items-center justify-between gap-4"
                        >
                            <button
                                v-if="currentStep > 0"
                                type="button"
                                class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-xs font-black text-slate-600 shadow-sm transition hover:bg-slate-50"
                                @click="previous"
                            >
                                <ArrowLeft class="size-4" />

                                Retour
                            </button>

                            <Link
                                v-else
                                href="/login"
                                class="inline-flex h-11 items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-xs font-black text-slate-600 shadow-sm"
                            >
                                <ArrowLeft class="size-4" />

                                Connexion
                            </Link>

                            <button
                                v-if="currentStepKey !== 'account'"
                                type="button"
                                :disabled="!canContinue || loading"
                                class="ml-auto inline-flex h-11 items-center gap-2 rounded-xl bg-[#070b18] px-6 text-xs font-black text-white shadow-lg shadow-slate-950/10 transition hover:bg-violet-600 disabled:cursor-not-allowed disabled:opacity-30"
                                @click="next"
                            >
                                Suivant

                                <ArrowRight class="size-4" />
                            </button>

                            <button
                                v-else
                                type="button"
                                :disabled="!canContinue || form.processing"
                                class="ml-auto inline-flex h-11 items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 px-6 text-xs font-black text-white shadow-lg shadow-violet-500/20 transition hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-40"
                                @click="submit"
                            >
                                <span
                                    v-if="form.processing"
                                    class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                                />

                                <Check v-else class="size-4" />

                                {{
                                    form.processing
                                        ? "Création..."
                                        : "Créer mon compte"
                                }}
                            </button>
                        </div>

                        <!-- Login -->

                        <p class="mt-3 text-center text-[10px] text-slate-400">
                            Vous avez déjà un compte ?

                            <Link
                                href="/login"
                                class="ml-1 font-black text-violet-600 hover:text-violet-700"
                            >
                                Se connecter
                            </Link>
                        </p>
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
