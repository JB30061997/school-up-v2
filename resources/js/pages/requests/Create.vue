<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import {
    AlertTriangle,
    ArrowLeft,
    Check,
    ChevronRight,
    Clock3,
    FileText,
    Info,
    KeyRound,
    Laptop,
    PackagePlus,
    Save,
    Settings2,
    ShieldCheck,
    ShoppingCart,
    Sparkles,
} from "@lucide/vue";
import { computed } from "vue";

type RequestType = {
    id: number;
    name: string;
    code: string;
    description?: string | null;
    sla_minutes?: number | null;
    requires_approval?: boolean | number;
};

const props = defineProps<{
    types: RequestType[];
    priorities: Record<string, string>;
}>();

const form = useForm({
    request_type_id: "",
    subject: "",
    description: "",
    priority: "NORMAL",
    due_at: "",
});

const selectedType = computed(() => {
    return (
        props.types.find(
            (type) =>
                String(type.id) ===
                String(form.request_type_id),
        ) ?? null
    );
});

const typeIcon = (code: string) => {
    switch (code) {
        case "ACCESS":
            return KeyRound;
        case "EQUIPMENT":
            return Laptop;
        case "PURCHASE":
            return ShoppingCart;
        case "CONFIGURATION":
            return Settings2;
        default:
            return FileText;
    }
};

const typeClasses = (code: string) => {
    switch (code) {
        case "ACCESS":
            return "bg-blue-50 text-blue-600 border-blue-100";

        case "EQUIPMENT":
            return "bg-violet-50 text-violet-600 border-violet-100";

        case "PURCHASE":
            return "bg-amber-50 text-amber-600 border-amber-100";

        case "CONFIGURATION":
            return "bg-emerald-50 text-emerald-600 border-emerald-100";

        default:
            return "bg-slate-50 text-slate-600 border-slate-200";
    }
};

const priorityDescription = computed(() => {
    switch (form.priority) {
        case "LOW":
            return "La demande peut être traitée sans urgence.";

        case "HIGH":
            return "La demande nécessite une prise en charge rapide.";

        case "URGENT":
            return "La demande nécessite une intervention prioritaire.";

        default:
            return "Traitement selon le délai standard.";
    }
});

const slaLabel = computed(() => {
    const minutes = selectedType.value?.sla_minutes;

    if (!minutes) {
        return "Non défini";
    }

    if (minutes < 60) {
        return `${minutes} min`;
    }

    if (minutes < 1440) {
        const hours = Math.round(minutes / 60);
        return `${hours} h`;
    }

    const days = Math.round(minutes / 1440);

    return `${days} jour${days > 1 ? "s" : ""}`;
});

const submit = () => {
    form.post("/requests", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvelle demande" />

    <div class="min-h-full bg-slate-50/60">
        <!-- HEADER -->
        <div class="border-b border-slate-200 bg-white">
            <div
                class="flex flex-col gap-4 px-6 py-6 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Link
                        href="/requests"
                        class="flex size-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        <ArrowLeft class="size-4" />
                    </Link>

                    <div>
                        <div class="flex items-center gap-2">
                            <h1
                                class="text-2xl font-black tracking-tight text-slate-950"
                            >
                                Nouvelle demande
                            </h1>

                            <span
                                class="hidden rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-700 sm:inline-flex"
                            >
                                Création
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-slate-500">
                            Créez et transmettez une nouvelle demande interne.
                        </p>
                    </div>
                </div>

                <div
                    class="hidden items-center gap-2 text-xs font-semibold text-slate-400 md:flex"
                >
                    <span>Demandes</span>
                    <ChevronRight class="size-3.5" />
                    <span class="text-slate-700">
                        Nouvelle demande
                    </span>
                </div>
            </div>
        </div>

        <form
            class="mx-auto grid max-w-[1500px] gap-5 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_360px]"
            @submit.prevent="submit"
        >
            <!-- ===================================================== -->
            <!-- LEFT -->
            <!-- ===================================================== -->

            <div class="space-y-5">
                <!-- TYPE -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4"
                    >
                        <div>
                            <h2 class="text-sm font-black text-slate-900">
                                Type de demande
                            </h2>

                            <p class="mt-1 text-xs text-slate-500">
                                Sélectionnez la nature de votre demande.
                            </p>
                        </div>

                        <div
                            class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
                        >
                            <PackagePlus class="size-4" />
                        </div>
                    </div>

                    <div class="p-5">
                        <div
                            class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <button
                                v-for="type in types"
                                :key="type.id"
                                type="button"
                                class="group relative min-h-[145px] rounded-2xl border p-4 text-left transition-all"
                                :class="
                                    String(form.request_type_id) ===
                                    String(type.id)
                                        ? 'border-emerald-500 bg-emerald-50/60 shadow-sm ring-2 ring-emerald-100'
                                        : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md'
                                "
                                @click="
                                    form.request_type_id =
                                        String(type.id)
                                "
                            >
                                <div
                                    v-if="
                                        String(form.request_type_id) ===
                                        String(type.id)
                                    "
                                    class="absolute right-3 top-3 flex size-6 items-center justify-center rounded-full bg-emerald-600 text-white"
                                >
                                    <Check class="size-3.5" />
                                </div>

                                <div
                                    class="flex size-10 items-center justify-center rounded-xl border"
                                    :class="typeClasses(type.code)"
                                >
                                    <component
                                        :is="typeIcon(type.code)"
                                        class="size-5"
                                    />
                                </div>

                                <p
                                    class="mt-4 pr-7 text-sm font-black text-slate-900"
                                >
                                    {{ type.name }}
                                </p>

                                <p
                                    class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500"
                                >
                                    {{
                                        type.description ??
                                        "Demande interne."
                                    }}
                                </p>
                            </button>
                        </div>

                        <p
                            v-if="form.errors.request_type_id"
                            class="mt-3 text-xs font-semibold text-red-600"
                        >
                            {{ form.errors.request_type_id }}
                        </p>
                    </div>
                </section>

                <!-- INFORMATION -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 px-5 py-4"
                    >
                        <h2 class="text-sm font-black text-slate-900">
                            Informations de la demande
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Décrivez précisément votre besoin.
                        </p>
                    </div>

                    <div class="space-y-5 p-5">
                        <!-- SUBJECT -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-700"
                            >
                                Objet de la demande
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                v-model="form.subject"
                                type="text"
                                maxlength="255"
                                placeholder="Ex. Création d'un accès Pronote"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                :class="
                                    form.errors.subject
                                        ? 'border-red-300'
                                        : ''
                                "
                            />

                            <div
                                class="mt-1.5 flex items-center justify-between"
                            >
                                <p
                                    v-if="form.errors.subject"
                                    class="text-xs font-semibold text-red-600"
                                >
                                    {{ form.errors.subject }}
                                </p>

                                <span
                                    class="ml-auto text-[10px] text-slate-400"
                                >
                                    {{ form.subject.length }}/255
                                </span>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-700"
                            >
                                Description
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="8"
                                placeholder="Décrivez votre demande, le contexte, les utilisateurs concernés et les informations utiles au traitement..."
                                class="w-full resize-none rounded-xl border border-slate-200 bg-white p-4 text-sm leading-6 text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                                :class="
                                    form.errors.description
                                        ? 'border-red-300'
                                        : ''
                                "
                            />

                            <p
                                v-if="form.errors.description"
                                class="mt-1.5 text-xs font-semibold text-red-600"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- PRIORITY -->
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 px-5 py-4"
                    >
                        <h2 class="text-sm font-black text-slate-900">
                            Traitement
                        </h2>

                        <p class="mt-1 text-xs text-slate-500">
                            Définissez le niveau de priorité.
                        </p>
                    </div>

                    <div class="grid gap-5 p-5 md:grid-cols-2">
                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-700"
                            >
                                Priorité
                            </label>

                            <select
                                v-model="form.priority"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-800 outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                            >
                                <option
                                    v-for="(label, value) in priorities"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>

                            <p
                                class="mt-2 text-xs leading-5 text-slate-400"
                            >
                                {{ priorityDescription }}
                            </p>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-xs font-bold text-slate-700"
                            >
                                Échéance souhaitée
                            </label>

                            <input
                                v-model="form.due_at"
                                type="datetime-local"
                                class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-800 outline-none focus:border-emerald-400 focus:ring-4 focus:ring-emerald-50"
                            />

                            <p
                                v-if="form.errors.due_at"
                                class="mt-2 text-xs font-semibold text-red-600"
                            >
                                {{ form.errors.due_at }}
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <!-- ===================================================== -->
            <!-- RIGHT SUMMARY -->
            <!-- ===================================================== -->

            <aside class="space-y-4">
                <div
                    class="sticky top-20 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-100 bg-gradient-to-br from-emerald-50 to-white p-5"
                    >
                        <div
                            class="flex items-center gap-2 text-emerald-700"
                        >
                            <Sparkles class="size-4" />

                            <span
                                class="text-[11px] font-black uppercase tracking-wider"
                            >
                                Résumé
                            </span>
                        </div>

                        <h3
                            class="mt-3 text-lg font-black text-slate-950"
                        >
                            Votre demande
                        </h3>

                        <p
                            class="mt-1 text-xs leading-5 text-slate-500"
                        >
                            Vérifiez les informations avant l'envoi.
                        </p>
                    </div>

                    <div class="space-y-5 p-5">
                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                            >
                                Type
                            </p>

                            <div
                                v-if="selectedType"
                                class="mt-2 flex items-center gap-3"
                            >
                                <div
                                    class="flex size-9 items-center justify-center rounded-xl border"
                                    :class="
                                        typeClasses(
                                            selectedType.code,
                                        )
                                    "
                                >
                                    <component
                                        :is="
                                            typeIcon(
                                                selectedType.code,
                                            )
                                        "
                                        class="size-4"
                                    />
                                </div>

                                <div>
                                    <p
                                        class="text-sm font-bold text-slate-900"
                                    >
                                        {{ selectedType.name }}
                                    </p>

                                    <p
                                        class="mt-0.5 text-[10px] text-slate-400"
                                    >
                                        SLA : {{ slaLabel }}
                                    </p>
                                </div>
                            </div>

                            <p
                                v-else
                                class="mt-2 text-xs text-slate-400"
                            >
                                Aucun type sélectionné.
                            </p>
                        </div>

                        <div
                            class="h-px bg-slate-100"
                        />

                        <div>
                            <p
                                class="text-[10px] font-black uppercase tracking-wider text-slate-400"
                            >
                                Objet
                            </p>

                            <p
                                class="mt-2 text-sm font-bold leading-5 text-slate-800"
                            >
                                {{
                                    form.subject ||
                                    "Objet non renseigné"
                                }}
                            </p>
                        </div>

                        <div
                            class="h-px bg-slate-100"
                        />

                        <div
                            class="flex items-center justify-between"
                        >
                            <span
                                class="text-xs font-semibold text-slate-500"
                            >
                                Priorité
                            </span>

                            <span
                                class="rounded-full px-2.5 py-1 text-[10px] font-black"
                                :class="{
                                    'bg-slate-100 text-slate-600':
                                        form.priority === 'LOW',
                                    'bg-emerald-50 text-emerald-700':
                                        form.priority === 'NORMAL',
                                    'bg-orange-50 text-orange-700':
                                        form.priority === 'HIGH',
                                    'bg-red-50 text-red-700':
                                        form.priority === 'URGENT',
                                }"
                            >
                                {{
                                    priorities[
                                        form.priority
                                    ] ?? form.priority
                                }}
                            </span>
                        </div>

                        <div
                            v-if="
                                selectedType?.requires_approval
                            "
                            class="flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-3"
                        >
                            <ShieldCheck
                                class="mt-0.5 size-4 shrink-0 text-amber-600"
                            />

                            <p
                                class="text-xs leading-5 text-amber-800"
                            >
                                Cette demande nécessite une
                                validation avant son traitement.
                            </p>
                        </div>

                        <div
                            v-else
                            class="flex gap-3 rounded-xl border border-emerald-100 bg-emerald-50/60 p-3"
                        >
                            <Info
                                class="mt-0.5 size-4 shrink-0 text-emerald-600"
                            />

                            <p
                                class="text-xs leading-5 text-emerald-800"
                            >
                                La demande sera transmise au
                                service concerné après sa création.
                            </p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-slate-950 px-4 text-sm font-black text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save
                                v-if="!form.processing"
                                class="size-4"
                            />

                            <span
                                v-if="form.processing"
                                class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"
                            />

                            {{
                                form.processing
                                    ? "Enregistrement..."
                                    : "Créer la demande"
                            }}
                        </button>

                        <Link
                            href="/requests"
                            class="flex h-10 w-full items-center justify-center rounded-xl text-xs font-bold text-slate-500 transition hover:bg-slate-50 hover:text-slate-900"
                        >
                            Annuler
                        </Link>
                    </div>
                </div>
            </aside>
        </form>
    </div>
</template>