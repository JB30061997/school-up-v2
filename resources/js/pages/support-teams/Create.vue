<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

import {
    ArrowLeft,
    Check,
    Headphones,
    Info,
    Save,
    ShieldCheck,
    UserRound,
    UsersRound,
} from "lucide-vue-next";

type User = {
    id: number;
    name: string;
    email?: string | null;
};

type Environment = {
    id: number;
    name: string;
    code: string;
};

const props = defineProps<{
    users: User[];
    currentEnvironment: Environment | null;
}>();

const form = useForm({
    name: "",
    code: "",
    description: "",
    leader_id: null as number | null,
    users: [] as number[],
    active: true,
});

const selectedUsers = computed(() => form.users ?? []);

const isSelected = (id: number) => {
    return selectedUsers.value.includes(id);
};

const toggleUser = (id: number) => {
    if (isSelected(id)) {
        form.users = form.users.filter((userId) => userId !== id);

        return;
    }

    form.users = [...form.users, id];
};

const selectLeader = (id: number | null) => {
    form.leader_id = id;

    if (id && !form.users.includes(id)) {
        form.users = [...form.users, id];
    }
};

const getInitials = (name: string) => {
    return name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");
};

const submit = () => {
    form.post("/support-teams", {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouvelle équipe de support" />

    <div class="min-h-full bg-[#fafbfc]">
        <div class="mx-auto max-w-[1500px] space-y-6 p-5 md:p-7 lg:p-8">
            <!-- HEADER -->

            <section
                class="relative overflow-hidden rounded-[28px] border border-slate-200/80 bg-white shadow-[0_10px_35px_rgba(15,23,42,0.04)]"
            >
                <div
                    class="pointer-events-none absolute -right-20 -top-32 size-[430px] rounded-full bg-emerald-100/60 blur-[90px]"
                />

                <div
                    class="pointer-events-none absolute left-[40%] top-0 size-[300px] rounded-full bg-cyan-50 blur-[100px]"
                />

                <div class="relative flex items-start gap-5 p-7 md:p-9">
                    <Link
                        href="/support-teams"
                        class="flex size-12 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-emerald-200 hover:text-emerald-600"
                    >
                        <ArrowLeft class="size-5" />
                    </Link>

                    <div
                        class="flex size-16 shrink-0 items-center justify-center rounded-[20px] bg-gradient-to-br from-emerald-500 to-emerald-600 text-white shadow-[0_12px_30px_rgba(16,185,129,0.25)]"
                    >
                        <Headphones class="size-8" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1
                                class="text-3xl font-bold tracking-tight text-slate-950 md:text-[36px]"
                            >
                                Nouvelle équipe
                            </h1>

                            <span
                                class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.08em] text-emerald-700"
                            >
                                Support
                            </span>
                        </div>

                        <p
                            class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                        >
                            Créez une équipe, choisissez son responsable et
                            affectez les membres qui traiteront les
                            réclamations.
                        </p>

                        <div
                            v-if="currentEnvironment"
                            class="mt-4 flex flex-wrap items-center gap-2"
                        >
                            <span class="text-sm text-slate-400">
                                Établissement
                            </span>

                            <span
                                class="rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-700"
                            >
                                {{ currentEnvironment.name }}
                            </span>

                            <span
                                class="rounded-lg bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700"
                            >
                                {{ currentEnvironment.code }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <form
                class="grid gap-6 xl:grid-cols-[1.5fr_0.75fr]"
                @submit.prevent="submit"
            >
                <!-- LEFT -->

                <div class="space-y-6">
                    <!-- GENERAL -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm md:p-7"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"
                            >
                                <Headphones class="size-6" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Informations générales
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Identité de l'équipe de support.
                                </p>
                            </div>
                        </div>

                        <div class="mt-7 grid gap-5 md:grid-cols-2">
                            <!-- NAME -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Nom de l'équipe
                                    <span class="text-rose-500">*</span>
                                </label>

                                <input
                                    v-model="form.name"
                                    type="text"
                                    placeholder="Ex. Support IT"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                                />

                                <p
                                    v-if="form.errors.name"
                                    class="mt-2 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <!-- CODE -->

                            <div>
                                <label
                                    class="mb-2 block text-sm font-bold text-slate-700"
                                >
                                    Code
                                </label>

                                <input
                                    v-model="form.code"
                                    type="text"
                                    placeholder="Ex. IT-SUPPORT"
                                    class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50/50 px-4 font-mono text-sm font-semibold uppercase text-slate-800 outline-none transition placeholder:font-sans placeholder:font-normal placeholder:normal-case placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                                />

                                <p class="mt-2 text-xs text-slate-400">
                                    Facultatif. Le code sera généré
                                    automatiquement s'il est vide.
                                </p>

                                <p
                                    v-if="form.errors.code"
                                    class="mt-2 text-xs font-semibold text-rose-500"
                                >
                                    {{ form.errors.code }}
                                </p>
                            </div>
                        </div>

                        <!-- DESCRIPTION -->

                        <div class="mt-5">
                            <label
                                class="mb-2 block text-sm font-bold text-slate-700"
                            >
                                Description
                            </label>

                            <textarea
                                v-model="form.description"
                                rows="5"
                                placeholder="Décrivez le rôle et les responsabilités de cette équipe..."
                                class="w-full resize-none rounded-2xl border border-slate-200 bg-slate-50/50 p-4 text-sm font-medium leading-6 text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                            />

                            <p
                                v-if="form.errors.description"
                                class="mt-2 text-xs font-semibold text-rose-500"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>
                    </section>

                    <!-- LEADER -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm md:p-7"
                    >
                        <div class="flex items-center gap-4">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600"
                            >
                                <ShieldCheck class="size-6" />
                            </div>

                            <div>
                                <h2 class="text-lg font-bold text-slate-900">
                                    Responsable
                                </h2>

                                <p class="mt-1 text-sm text-slate-400">
                                    Choisissez le responsable principal de
                                    l'équipe.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6">
                            <select
                                :value="form.leader_id ?? ''"
                                class="h-14 w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 text-sm font-semibold text-slate-700 outline-none transition focus:border-emerald-300 focus:bg-white focus:ring-4 focus:ring-emerald-50"
                                @change="
                                    selectLeader(
                                        ($event.target as HTMLSelectElement)
                                            .value
                                            ? Number(
                                                  (
                                                      $event.target as HTMLSelectElement
                                                  ).value,
                                              )
                                            : null,
                                    )
                                "
                            >
                                <option value="">Aucun responsable</option>

                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="user.id"
                                >
                                    {{ user.name }}
                                    {{ user.email ? `— ${user.email}` : "" }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.leader_id"
                                class="mt-2 text-xs font-semibold text-rose-500"
                            >
                                {{ form.errors.leader_id }}
                            </p>
                        </div>
                    </section>

                    <!-- MEMBERS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm md:p-7"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex size-12 items-center justify-center rounded-2xl bg-sky-50 text-sky-600"
                                >
                                    <UsersRound class="size-6" />
                                </div>

                                <div>
                                    <h2
                                        class="text-lg font-bold text-slate-900"
                                    >
                                        Membres de l'équipe
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-400">
                                        Sélectionnez les utilisateurs membres de
                                        cette équipe.
                                    </p>
                                </div>
                            </div>

                            <span
                                class="w-fit rounded-full bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700"
                            >
                                {{ form.users.length }}
                                membre(s)
                            </span>
                        </div>

                        <div
                            v-if="users.length"
                            class="mt-6 grid gap-3 md:grid-cols-2"
                        >
                            <button
                                v-for="user in users"
                                :key="user.id"
                                type="button"
                                class="flex items-center gap-4 rounded-2xl border p-4 text-left transition"
                                :class="
                                    isSelected(user.id)
                                        ? 'border-emerald-300 bg-emerald-50/70 shadow-sm'
                                        : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'
                                "
                                @click="toggleUser(user.id)"
                            >
                                <div
                                    class="flex size-11 shrink-0 items-center justify-center rounded-full font-bold"
                                    :class="
                                        isSelected(user.id)
                                            ? 'bg-emerald-600 text-white'
                                            : 'bg-slate-100 text-slate-600'
                                    "
                                >
                                    {{ getInitials(user.name) }}
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm font-bold text-slate-800"
                                    >
                                        {{ user.name }}
                                    </p>

                                    <p
                                        class="mt-1 truncate text-xs text-slate-400"
                                    >
                                        {{ user.email || "—" }}
                                    </p>
                                </div>

                                <div
                                    class="flex size-6 shrink-0 items-center justify-center rounded-full border"
                                    :class="
                                        isSelected(user.id)
                                            ? 'border-emerald-600 bg-emerald-600 text-white'
                                            : 'border-slate-300 text-transparent'
                                    "
                                >
                                    <Check class="size-4" />
                                </div>
                            </button>
                        </div>

                        <div
                            v-else
                            class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center"
                        >
                            <UserRound class="mx-auto size-7 text-slate-400" />

                            <p class="mt-3 text-sm font-bold text-slate-700">
                                Aucun utilisateur disponible
                            </p>
                        </div>

                        <p
                            v-if="form.errors.users"
                            class="mt-3 text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.users }}
                        </p>
                    </section>
                </div>

                <!-- RIGHT -->

                <aside class="space-y-5">
                    <!-- STATUS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h3 class="text-base font-bold text-slate-900">
                            Statut
                        </h3>

                        <p class="mt-1 text-sm leading-6 text-slate-400">
                            Déterminez si cette équipe peut être utilisée
                            immédiatement.
                        </p>

                        <button
                            type="button"
                            class="mt-5 flex w-full items-center justify-between rounded-2xl border p-4 text-left transition"
                            :class="
                                form.active
                                    ? 'border-emerald-200 bg-emerald-50'
                                    : 'border-slate-200 bg-slate-50'
                            "
                            @click="form.active = !form.active"
                        >
                            <div>
                                <p
                                    class="text-sm font-bold"
                                    :class="
                                        form.active
                                            ? 'text-emerald-700'
                                            : 'text-slate-600'
                                    "
                                >
                                    {{
                                        form.active
                                            ? "Équipe active"
                                            : "Équipe inactive"
                                    }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{
                                        form.active
                                            ? "Disponible pour les affectations."
                                            : "Non disponible pour les affectations."
                                    }}
                                </p>
                            </div>

                            <div
                                class="relative h-7 w-12 rounded-full transition"
                                :class="
                                    form.active
                                        ? 'bg-emerald-500'
                                        : 'bg-slate-300'
                                "
                            >
                                <span
                                    class="absolute top-1 size-5 rounded-full bg-white shadow transition-all"
                                    :class="form.active ? 'left-6' : 'left-1'"
                                />
                            </div>
                        </button>

                        <p
                            v-if="form.errors.active"
                            class="mt-2 text-xs font-semibold text-rose-500"
                        >
                            {{ form.errors.active }}
                        </p>
                    </section>

                    <!-- INFO -->

                    <section
                        class="rounded-[26px] border border-emerald-100 bg-emerald-50/60 p-6"
                    >
                        <div class="flex gap-3">
                            <Info
                                class="mt-0.5 size-5 shrink-0 text-emerald-600"
                            />

                            <div>
                                <h3 class="text-sm font-bold text-emerald-900">
                                    Bon à savoir
                                </h3>

                                <p
                                    class="mt-2 text-xs leading-6 text-emerald-800/70"
                                >
                                    Le responsable sélectionné sera
                                    automatiquement ajouté aux membres de
                                    l'équipe.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- ACTIONS -->

                    <section
                        class="rounded-[26px] border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex h-14 w-full items-center justify-center gap-2 rounded-2xl bg-[#050817] px-5 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Save class="size-5" />

                            {{
                                form.processing
                                    ? "Création..."
                                    : "Créer l'équipe"
                            }}
                        </button>

                        <Link
                            href="/support-teams"
                            class="mt-3 flex h-13 w-full items-center justify-center rounded-2xl border border-slate-200 text-sm font-bold text-slate-600 transition hover:bg-slate-50"
                        >
                            Annuler
                        </Link>
                    </section>
                </aside>
            </form>
        </div>
    </div>
</template>
