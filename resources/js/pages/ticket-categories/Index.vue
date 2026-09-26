<script setup lang="ts">
import { computed, ref, watch } from "vue";
import { Head, router, useForm } from "@inertiajs/vue3";
import {
    AlertCircle,
    Check,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Clock3,
    Edit3,
    FolderOpen,
    Hash,
    Layers3,
    MessageSquareText,
    MoreVertical,
    Plus,
    Search,
    SlidersHorizontal,
    Tag,
    Trash2,
    X,
    XCircle,
} from "lucide-vue-next";

import AppLayout from '@/layouts/AppLayout.vue';
/*
|--------------------------------------------------------------------------
| Types
|--------------------------------------------------------------------------
*/

interface Category {
    id: number;
    name: string;
    code: string;
    description: string | null;
    sla_minutes: number | null;
    active: boolean;
    sort_order: number;
    tickets_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedCategories {
    data: Category[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

interface CurrentEnvironment {
    id: number;
    name: string;
    code: string;
}

interface Filters {
    search?: string | null;
    active?: string | null;
}

const props = defineProps<{
    categories: PaginatedCategories;
    filters: Filters;
    currentEnvironment: CurrentEnvironment;
}>();

/*
|--------------------------------------------------------------------------
| Search / filters
|--------------------------------------------------------------------------
*/

const search = ref(props.filters.search ?? "");
const activeFilter = ref(props.filters.active ?? "");

let searchTimer: ReturnType<typeof setTimeout> | null = null;

watch(search, () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        applyFilters();
    }, 450);
});

function applyFilters() {
    router.get(
        "/ticket-categories",
        {
            search: search.value || undefined,
            active: activeFilter.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function changeActiveFilter(value: string) {
    activeFilter.value = value;
    applyFilters();
}

function resetFilters() {
    search.value = "";
    activeFilter.value = "";

    router.get(
        "/ticket-categories",
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

const visibleActiveCount = computed(() => {
    return props.categories.data.filter((category) => category.active).length;
});

const visibleInactiveCount = computed(() => {
    return props.categories.data.filter((category) => !category.active).length;
});

const totalTicketsVisible = computed(() => {
    return props.categories.data.reduce(
        (total, category) => total + Number(category.tickets_count || 0),
        0,
    );
});

/*
|--------------------------------------------------------------------------
| Create modal
|--------------------------------------------------------------------------
*/

const createModalOpen = ref(false);

const createForm = useForm({
    name: "",
    code: "",
    description: "",
    sla_minutes: null as number | null,
    active: true,
    sort_order: 0,
});

function openCreateModal() {
    createForm.reset();
    createForm.clearErrors();

    createForm.active = true;
    createForm.sort_order = props.categories.total + 1;

    createModalOpen.value = true;
}

function closeCreateModal() {
    if (createForm.processing) {
        return;
    }

    createModalOpen.value = false;
    createForm.clearErrors();
}

function submitCreate() {
    createForm.post("/ticket-categories", {
        preserveScroll: true,

        onSuccess: () => {
            createModalOpen.value = false;
            createForm.reset();
        },
    });
}

/*
|--------------------------------------------------------------------------
| Edit modal
|--------------------------------------------------------------------------
*/

const editModalOpen = ref(false);
const selectedCategory = ref<Category | null>(null);

const editForm = useForm({
    name: "",
    code: "",
    description: "",
    sla_minutes: null as number | null,
    active: true,
    sort_order: 0,
});

function openEditModal(category: Category) {
    selectedCategory.value = category;

    editForm.clearErrors();

    editForm.name = category.name;
    editForm.code = category.code;
    editForm.description = category.description ?? "";
    editForm.sla_minutes = category.sla_minutes;
    editForm.active = category.active;
    editForm.sort_order = category.sort_order;

    editModalOpen.value = true;
}

function closeEditModal() {
    if (editForm.processing) {
        return;
    }

    editModalOpen.value = false;
    selectedCategory.value = null;
    editForm.clearErrors();
}

function submitEdit() {
    if (!selectedCategory.value) {
        return;
    }

    editForm.put(`/ticket-categories/${selectedCategory.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            editModalOpen.value = false;
            selectedCategory.value = null;
        },
    });
}

/*
|--------------------------------------------------------------------------
| Toggle active
|--------------------------------------------------------------------------
*/

function toggleActive(category: Category) {
    router.post(
        `/ticket-categories/${category.id}/toggle-active`,
        {},
        {
            preserveScroll: true,
        },
    );
}

/*
|--------------------------------------------------------------------------
| Delete modal
|--------------------------------------------------------------------------
*/

const deleteModalOpen = ref(false);
const categoryToDelete = ref<Category | null>(null);
const deleting = ref(false);

function openDeleteModal(category: Category) {
    categoryToDelete.value = category;
    deleteModalOpen.value = true;
}

function closeDeleteModal() {
    if (deleting.value) {
        return;
    }

    deleteModalOpen.value = false;
    categoryToDelete.value = null;
}

function confirmDelete() {
    if (!categoryToDelete.value) {
        return;
    }

    deleting.value = true;

    router.delete(`/ticket-categories/${categoryToDelete.value.id}`, {
        preserveScroll: true,

        onSuccess: () => {
            deleteModalOpen.value = false;
            categoryToDelete.value = null;
        },

        onFinish: () => {
            deleting.value = false;
        },
    });
}

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function goToPage(url: string | null) {
    if (!url) {
        return;
    }

    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
    });
}

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function formatSla(minutes: number | null) {
    if (!minutes) {
        return "Non défini";
    }

    if (minutes < 60) {
        return `${minutes} min`;
    }

    if (minutes % 1440 === 0) {
        const days = minutes / 1440;

        return `${days} jour${days > 1 ? "s" : ""}`;
    }

    if (minutes % 60 === 0) {
        const hours = minutes / 60;

        return `${hours} h`;
    }

    const hours = Math.floor(minutes / 60);
    const remainingMinutes = minutes % 60;

    return `${hours} h ${remainingMinutes} min`;
}

function getInitials(name: string) {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word.charAt(0).toUpperCase())
        .join("");
}

function getCardStyle(index: number) {
    const styles = [
        {
            background: "from-cyan-500 to-teal-500",
            soft: "bg-cyan-50",
            text: "text-cyan-700",
            border: "border-cyan-100",
        },
        {
            background: "from-violet-500 to-purple-500",
            soft: "bg-violet-50",
            text: "text-violet-700",
            border: "border-violet-100",
        },
        {
            background: "from-orange-500 to-amber-500",
            soft: "bg-orange-50",
            text: "text-orange-700",
            border: "border-orange-100",
        },
        {
            background: "from-rose-500 to-pink-500",
            soft: "bg-rose-50",
            text: "text-rose-700",
            border: "border-rose-100",
        },
        {
            background: "from-blue-500 to-indigo-500",
            soft: "bg-blue-50",
            text: "text-blue-700",
            border: "border-blue-100",
        },
        {
            background: "from-emerald-500 to-green-500",
            soft: "bg-emerald-50",
            text: "text-emerald-700",
            border: "border-emerald-100",
        },
    ];

    return styles[index % styles.length];
}
</script>

<template>
    <Head title="Objets des réclamations" />

    <SchoolLayout>
        <div class="min-h-screen bg-[#f7f9fc]">
            <!-- ========================================================= -->
            <!-- HEADER -->
            <!-- ========================================================= -->

            <div
                class="border-b border-slate-200/80 bg-white px-6 py-6 lg:px-8"
            >
                <div
                    class="mx-auto flex max-w-[1600px] flex-col gap-5 xl:flex-row xl:items-center xl:justify-between"
                >
                    <div class="flex items-start gap-4">
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-500 text-white shadow-lg shadow-teal-500/20"
                        >
                            <MessageSquareText class="h-7 w-7" />
                        </div>

                        <div>
                            <div class="mb-1 flex flex-wrap items-center gap-2">
                                <h1
                                    class="text-2xl font-bold tracking-tight text-slate-900 lg:text-3xl"
                                >
                                    Objets des réclamations
                                </h1>

                                <span
                                    class="rounded-full border border-teal-100 bg-teal-50 px-3 py-1 text-xs font-bold text-teal-700"
                                >
                                    Référentiel
                                </span>
                            </div>

                            <p
                                class="max-w-2xl text-sm leading-6 text-slate-500"
                            >
                                Organisez les différents domaines utilisés pour
                                classer et traiter les réclamations.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 text-sm font-bold text-white shadow-lg shadow-slate-900/10 transition hover:-translate-y-0.5 hover:bg-slate-800"
                        @click="openCreateModal"
                    >
                        <Plus class="h-5 w-5" />

                        Nouvel objet
                    </button>
                </div>
            </div>

            <main class="px-6 py-7 lg:px-8">
                <div class="mx-auto max-w-[1600px]">
                    <!-- ================================================= -->
                    <!-- ENVIRONMENT + STATS -->
                    <!-- ================================================= -->

                    <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
                        <!-- Environment -->

                        <div
                            class="relative overflow-hidden rounded-2xl border border-teal-100 bg-gradient-to-br from-teal-50 via-white to-cyan-50 p-5 lg:col-span-1"
                        >
                            <div
                                class="absolute -right-8 -top-8 h-28 w-28 rounded-full bg-teal-100/60"
                            ></div>

                            <div class="relative flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-white text-teal-600 shadow-sm ring-1 ring-teal-100"
                                >
                                    <Layers3 class="h-6 w-6" />
                                </div>

                                <div class="min-w-0">
                                    <p
                                        class="text-xs font-bold uppercase tracking-[0.15em] text-teal-600"
                                    >
                                        Environnement
                                    </p>

                                    <p
                                        class="mt-1 truncate text-base font-bold text-slate-900"
                                    >
                                        {{ currentEnvironment.name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Total
                                    </p>

                                    <p
                                        class="mt-2 text-3xl font-black text-slate-900"
                                    >
                                        {{ categories.total }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        objets configurés
                                    </p>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                                >
                                    <FolderOpen class="h-6 w-6" />
                                </div>
                            </div>
                        </div>

                        <!-- Active -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Actifs affichés
                                    </p>

                                    <p
                                        class="mt-2 text-3xl font-black text-emerald-600"
                                    >
                                        {{ visibleActiveCount }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        sur cette page
                                    </p>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                                >
                                    <CheckCircle2 class="h-6 w-6" />
                                </div>
                            </div>
                        </div>

                        <!-- Tickets -->

                        <div
                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                        >
                            <div class="flex items-center justify-between">
                                <div>
                                    <p
                                        class="text-xs font-bold uppercase tracking-wider text-slate-400"
                                    >
                                        Réclamations
                                    </p>

                                    <p
                                        class="mt-2 text-3xl font-black text-violet-600"
                                    >
                                        {{ totalTicketsVisible }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        liées aux objets affichés
                                    </p>
                                </div>

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                                >
                                    <MessageSquareText class="h-6 w-6" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- FILTERS -->
                    <!-- ================================================= -->

                    <div
                        class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div
                            class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between"
                        >
                            <div class="relative w-full max-w-xl">
                                <Search
                                    class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                                />

                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Rechercher un objet, un code..."
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-teal-400 focus:bg-white focus:ring-4 focus:ring-teal-500/10"
                                />
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <div
                                    class="mr-1 flex items-center gap-2 text-sm font-semibold text-slate-500"
                                >
                                    <SlidersHorizontal class="h-4 w-4" />
                                    Statut
                                </div>

                                <button
                                    type="button"
                                    class="rounded-lg px-4 py-2.5 text-sm font-bold transition"
                                    :class="
                                        activeFilter === ''
                                            ? 'bg-slate-900 text-white shadow'
                                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                    "
                                    @click="changeActiveFilter('')"
                                >
                                    Tous
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg px-4 py-2.5 text-sm font-bold transition"
                                    :class="
                                        activeFilter === '1'
                                            ? 'bg-emerald-500 text-white shadow'
                                            : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                    "
                                    @click="changeActiveFilter('1')"
                                >
                                    Actifs
                                </button>

                                <button
                                    type="button"
                                    class="rounded-lg px-4 py-2.5 text-sm font-bold transition"
                                    :class="
                                        activeFilter === '0'
                                            ? 'bg-rose-500 text-white shadow'
                                            : 'bg-rose-50 text-rose-700 hover:bg-rose-100'
                                    "
                                    @click="changeActiveFilter('0')"
                                >
                                    Inactifs
                                </button>

                                <button
                                    v-if="search || activeFilter"
                                    type="button"
                                    class="ml-1 rounded-lg px-3 py-2.5 text-sm font-semibold text-slate-500 transition hover:bg-slate-100 hover:text-slate-900"
                                    @click="resetFilters"
                                >
                                    Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- CARDS -->
                    <!-- ================================================= -->

                    <div
                        v-if="categories.data.length"
                        class="grid grid-cols-1 gap-5 md:grid-cols-2 2xl:grid-cols-3"
                    >
                        <article
                            v-for="(category, index) in categories.data"
                            :key="category.id"
                            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/60"
                            :class="{
                                'opacity-70': !category.active,
                            }"
                        >
                            <!-- Top gradient -->

                            <div
                                class="h-1.5 bg-gradient-to-r"
                                :class="getCardStyle(index).background"
                            ></div>

                            <div class="p-5">
                                <!-- Head -->

                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div
                                        class="flex min-w-0 items-center gap-3"
                                    >
                                        <div
                                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-sm font-black text-white shadow-md"
                                            :class="
                                                getCardStyle(index).background
                                            "
                                        >
                                            {{ getInitials(category.name) }}
                                        </div>

                                        <div class="min-w-0">
                                            <div
                                                class="flex flex-wrap items-center gap-2"
                                            >
                                                <h2
                                                    class="truncate text-lg font-bold text-slate-900"
                                                >
                                                    {{ category.name }}
                                                </h2>

                                                <span
                                                    v-if="category.active"
                                                    class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 ring-1 ring-emerald-100"
                                                >
                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                                                    ></span>

                                                    Actif
                                                </span>

                                                <span
                                                    v-else
                                                    class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500"
                                                >
                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full bg-slate-400"
                                                    ></span>

                                                    Inactif
                                                </span>
                                            </div>

                                            <div
                                                class="mt-1 flex items-center gap-1.5"
                                            >
                                                <Hash
                                                    class="h-3.5 w-3.5 text-slate-400"
                                                />

                                                <span
                                                    class="font-mono text-xs font-bold uppercase tracking-wide text-slate-400"
                                                >
                                                    {{ category.code }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <button
                                        type="button"
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                                        @click="openEditModal(category)"
                                    >
                                        <MoreVertical class="h-5 w-5" />
                                    </button>
                                </div>

                                <!-- Description -->

                                <p
                                    class="mt-5 min-h-[44px] text-sm leading-6 text-slate-500"
                                >
                                    {{
                                        category.description ||
                                        "Aucune description renseignée pour cet objet."
                                    }}
                                </p>

                                <!-- Information blocks -->

                                <div class="mt-5 grid grid-cols-2 gap-3">
                                    <div class="rounded-xl bg-slate-50 p-3.5">
                                        <div
                                            class="flex items-center gap-2 text-slate-400"
                                        >
                                            <Clock3 class="h-4 w-4" />

                                            <span
                                                class="text-[11px] font-bold uppercase tracking-wider"
                                            >
                                                Délai SLA
                                            </span>
                                        </div>

                                        <p
                                            class="mt-2 text-sm font-bold text-slate-800"
                                        >
                                            {{
                                                formatSla(category.sla_minutes)
                                            }}
                                        </p>
                                    </div>

                                    <div class="rounded-xl bg-slate-50 p-3.5">
                                        <div
                                            class="flex items-center gap-2 text-slate-400"
                                        >
                                            <MessageSquareText
                                                class="h-4 w-4"
                                            />

                                            <span
                                                class="text-[11px] font-bold uppercase tracking-wider"
                                            >
                                                Réclamations
                                            </span>
                                        </div>

                                        <p
                                            class="mt-2 text-sm font-bold text-slate-800"
                                        >
                                            {{ category.tickets_count }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Footer -->

                                <div
                                    class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4"
                                >
                                    <button
                                        type="button"
                                        class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-xs font-bold transition"
                                        :class="
                                            category.active
                                                ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'
                                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                        "
                                        @click="toggleActive(category)"
                                    >
                                        <CheckCircle2
                                            v-if="category.active"
                                            class="h-4 w-4"
                                        />

                                        <XCircle v-else class="h-4 w-4" />

                                        {{
                                            category.active
                                                ? "Désactiver"
                                                : "Activer"
                                        }}
                                    </button>

                                    <div class="flex items-center gap-1">
                                        <button
                                            type="button"
                                            class="inline-flex h-9 items-center gap-1.5 rounded-lg px-3 text-xs font-bold text-blue-600 transition hover:bg-blue-50"
                                            @click="openEditModal(category)"
                                        >
                                            <Edit3 class="h-4 w-4" />

                                            Modifier
                                        </button>

                                        <button
                                            type="button"
                                            class="flex h-9 w-9 items-center justify-center rounded-lg text-rose-500 transition hover:bg-rose-50"
                                            @click="openDeleteModal(category)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- ================================================= -->
                    <!-- EMPTY STATE -->
                    <!-- ================================================= -->

                    <div
                        v-else
                        class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center"
                    >
                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400"
                        >
                            <Search class="h-7 w-7" />
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-900">
                            Aucun objet trouvé
                        </h3>

                        <p
                            class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500"
                        >
                            Aucun objet de réclamation ne correspond à votre
                            recherche ou aux filtres sélectionnés.
                        </p>

                        <div class="mt-6 flex justify-center gap-3">
                            <button
                                v-if="search || activeFilter"
                                type="button"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                @click="resetFilters"
                            >
                                Réinitialiser
                            </button>

                            <button
                                type="button"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white"
                                @click="openCreateModal"
                            >
                                <Plus class="h-4 w-4" />

                                Ajouter un objet
                            </button>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- PAGINATION -->
                    <!-- ================================================= -->

                    <div
                        v-if="categories.total > 0"
                        class="mt-7 flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
                    >
                        <p class="text-sm text-slate-500">
                            Affichage

                            <strong class="text-slate-800">
                                {{ categories.from ?? 0 }}
                            </strong>

                            à

                            <strong class="text-slate-800">
                                {{ categories.to ?? 0 }}
                            </strong>

                            sur

                            <strong class="text-slate-800">
                                {{ categories.total }}
                            </strong>

                            résultats
                        </p>

                        <div class="flex items-center gap-1">
                            <button
                                v-for="(link, index) in categories.links"
                                :key="index"
                                type="button"
                                :disabled="!link.url"
                                class="flex min-h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-bold transition"
                                :class="[
                                    link.active
                                        ? 'bg-slate-900 text-white shadow'
                                        : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50',
                                    !link.url
                                        ? 'cursor-not-allowed opacity-40'
                                        : '',
                                ]"
                                @click="goToPage(link.url)"
                            >
                                <ChevronLeft
                                    v-if="
                                        link.label.includes('Previous') ||
                                        link.label.includes('Précédent')
                                    "
                                    class="h-4 w-4"
                                />

                                <ChevronRight
                                    v-else-if="
                                        link.label.includes('Next') ||
                                        link.label.includes('Suivant')
                                    "
                                    class="h-4 w-4"
                                />

                                <span v-else v-html="link.label"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <!-- ============================================================= -->
        <!-- CREATE MODAL -->
        <!-- ============================================================= -->

        <Teleport to="body">
            <div
                v-if="createModalOpen"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                    @click="closeCreateModal"
                ></div>

                <div
                    class="relative z-10 w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-6 py-5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-teal-500 to-cyan-500 text-white"
                            >
                                <Plus class="h-5 w-5" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Nouvel objet
                                </h2>

                                <p class="text-xs text-slate-500">
                                    {{ currentEnvironment.name }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            @click="closeCreateModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitCreate">
                        <div class="max-h-[70vh] space-y-5 overflow-y-auto p-6">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Nom
                                        <span class="text-rose-500">*</span>
                                    </label>

                                    <input
                                        v-model="createForm.name"
                                        type="text"
                                        placeholder="Ex : Transport"
                                        class="form-input"
                                    />

                                    <p
                                        v-if="createForm.errors.name"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ createForm.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Code
                                    </label>

                                    <input
                                        v-model="createForm.code"
                                        type="text"
                                        placeholder="Ex : TRANSPORT"
                                        class="form-input font-mono uppercase"
                                    />

                                    <p class="mt-1.5 text-xs text-slate-400">
                                        Laissez vide pour le générer
                                        automatiquement.
                                    </p>

                                    <p
                                        v-if="createForm.errors.code"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ createForm.errors.code }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Description
                                </label>

                                <textarea
                                    v-model="createForm.description"
                                    rows="4"
                                    placeholder="Décrivez le type de réclamations concerné..."
                                    class="form-input resize-none"
                                ></textarea>

                                <p
                                    v-if="createForm.errors.description"
                                    class="mt-1.5 text-xs font-medium text-rose-600"
                                >
                                    {{ createForm.errors.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        SLA en minutes
                                    </label>

                                    <input
                                        v-model.number="createForm.sla_minutes"
                                        type="number"
                                        min="1"
                                        placeholder="Ex : 1440"
                                        class="form-input"
                                    />

                                    <p class="mt-1.5 text-xs text-slate-400">
                                        60 = 1 heure · 1440 = 1 jour
                                    </p>

                                    <p
                                        v-if="createForm.errors.sla_minutes"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ createForm.errors.sla_minutes }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Ordre d'affichage
                                    </label>

                                    <input
                                        v-model.number="createForm.sort_order"
                                        type="number"
                                        min="0"
                                        class="form-input"
                                    />

                                    <p
                                        v-if="createForm.errors.sort_order"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ createForm.errors.sort_order }}
                                    </p>
                                </div>
                            </div>

                            <label
                                class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div>
                                    <p class="text-sm font-bold text-slate-800">
                                        Objet actif
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Cet objet pourra être utilisé dans les
                                        nouvelles réclamations.
                                    </p>
                                </div>

                                <input
                                    v-model="createForm.active"
                                    type="checkbox"
                                    class="peer sr-only"
                                />

                                <div
                                    class="relative h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-teal-500"
                                >
                                    <div
                                        class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"
                                    ></div>
                                </div>
                            </label>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4"
                        >
                            <button
                                type="button"
                                class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-200/60"
                                @click="closeCreateModal"
                            >
                                Annuler
                            </button>

                            <button
                                type="submit"
                                :disabled="createForm.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-lg transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                <Check class="h-4 w-4" />

                                {{
                                    createForm.processing
                                        ? "Création..."
                                        : "Créer l’objet"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- ============================================================= -->
        <!-- EDIT MODAL -->
        <!-- ============================================================= -->

        <Teleport to="body">
            <div
                v-if="editModalOpen && selectedCategory"
                class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                    @click="closeEditModal"
                ></div>

                <div
                    class="relative z-10 w-full max-w-2xl overflow-hidden rounded-3xl bg-white shadow-2xl"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-6 py-5"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600"
                            >
                                <Edit3 class="h-5 w-5" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Modifier l'objet
                                </h2>

                                <p
                                    class="font-mono text-xs font-semibold text-slate-400"
                                >
                                    {{ selectedCategory.code }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition hover:bg-slate-100 hover:text-slate-700"
                            @click="closeEditModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitEdit">
                        <div class="max-h-[70vh] space-y-5 overflow-y-auto p-6">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Nom
                                    </label>

                                    <input
                                        v-model="editForm.name"
                                        type="text"
                                        class="form-input"
                                    />

                                    <p
                                        v-if="editForm.errors.name"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ editForm.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Code
                                    </label>

                                    <input
                                        v-model="editForm.code"
                                        type="text"
                                        class="form-input font-mono uppercase"
                                    />

                                    <p
                                        v-if="editForm.errors.code"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ editForm.errors.code }}
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Description
                                </label>

                                <textarea
                                    v-model="editForm.description"
                                    rows="4"
                                    class="form-input resize-none"
                                ></textarea>

                                <p
                                    v-if="editForm.errors.description"
                                    class="mt-1.5 text-xs font-medium text-rose-600"
                                >
                                    {{ editForm.errors.description }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        SLA en minutes
                                    </label>

                                    <input
                                        v-model.number="editForm.sla_minutes"
                                        type="number"
                                        min="1"
                                        class="form-input"
                                    />

                                    <p
                                        v-if="editForm.errors.sla_minutes"
                                        class="mt-1.5 text-xs font-medium text-rose-600"
                                    >
                                        {{ editForm.errors.sla_minutes }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-bold text-slate-700"
                                    >
                                        Ordre d'affichage
                                    </label>

                                    <input
                                        v-model.number="editForm.sort_order"
                                        type="number"
                                        min="0"
                                        class="form-input"
                                    />
                                </div>
                            </div>

                            <label
                                class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 p-4"
                            >
                                <div>
                                    <p class="text-sm font-bold text-slate-800">
                                        Objet actif
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Contrôlez la disponibilité de cet objet.
                                    </p>
                                </div>

                                <input
                                    v-model="editForm.active"
                                    type="checkbox"
                                    class="peer sr-only"
                                />

                                <div
                                    class="relative h-7 w-12 rounded-full bg-slate-300 transition peer-checked:bg-teal-500"
                                >
                                    <div
                                        class="absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow transition peer-checked:translate-x-5"
                                    ></div>
                                </div>
                            </label>
                        </div>

                        <div
                            class="flex items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-6 py-4"
                        >
                            <button
                                type="button"
                                class="rounded-xl px-5 py-2.5 text-sm font-bold text-slate-600 transition hover:bg-slate-200/60"
                                @click="closeEditModal"
                            >
                                Annuler
                            </button>

                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700 disabled:opacity-60"
                            >
                                <Check class="h-4 w-4" />

                                {{
                                    editForm.processing
                                        ? "Enregistrement..."
                                        : "Enregistrer"
                                }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- ============================================================= -->
        <!-- DELETE MODAL -->
        <!-- ============================================================= -->

        <Teleport to="body">
            <div
                v-if="deleteModalOpen && categoryToDelete"
                class="fixed inset-0 z-[110] flex items-center justify-center p-4"
            >
                <div
                    class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"
                    @click="closeDeleteModal"
                ></div>

                <div
                    class="relative z-10 w-full max-w-md rounded-3xl bg-white p-6 shadow-2xl"
                >
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-50 text-rose-600"
                    >
                        <AlertCircle class="h-7 w-7" />
                    </div>

                    <h3 class="mt-5 text-xl font-black text-slate-900">
                        Supprimer cet objet ?
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Vous êtes sur le point de supprimer

                        <strong class="text-slate-800">
                            {{ categoryToDelete.name }} </strong
                        >. Cette action est définitive.
                    </p>

                    <div
                        v-if="categoryToDelete.tickets_count > 0"
                        class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800"
                    >
                        Cet objet contient
                        <strong>
                            {{ categoryToDelete.tickets_count }}
                        </strong>
                        réclamation(s). Le serveur empêchera sa suppression tant
                        qu'il est utilisé.
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-xl px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100"
                            @click="closeDeleteModal"
                        >
                            Annuler
                        </button>

                        <button
                            type="button"
                            :disabled="deleting"
                            class="inline-flex items-center gap-2 rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-rose-500/20 transition hover:bg-rose-700 disabled:opacity-60"
                            @click="confirmDelete"
                        >
                            <Trash2 class="h-4 w-4" />

                            {{ deleting ? "Suppression..." : "Supprimer" }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </SchoolLayout>
</template>

<style scoped>
.form-input {
    width: 100%;
    border-radius: 0.75rem;
    border: 1px solid rgb(226 232 240);
    background: rgb(248 250 252);
    padding: 0.75rem 0.875rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(51 65 85);
    outline: none;
    transition:
        border-color 150ms ease,
        box-shadow 150ms ease,
        background-color 150ms ease;
}

.form-input::placeholder {
    color: rgb(148 163 184);
}

.form-input:focus {
    border-color: rgb(45 212 191);
    background: white;
    box-shadow: 0 0 0 4px rgb(20 184 166 / 0.1);
}
</style>
