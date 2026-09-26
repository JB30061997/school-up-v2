<script setup lang="ts">
import { Form, Head } from "@inertiajs/vue3";

import {
    ArrowRight,
    Check,
    GraduationCap,
    LockKeyhole,
    Mail,
    School,
    ShieldCheck,
    Sparkles,
    UserRound,
    Users,
} from "lucide-vue-next";

import InputError from "@/components/InputError.vue";
import PasswordInput from "@/components/PasswordInput.vue";
import TextLink from "@/components/TextLink.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Spinner } from "@/components/ui/spinner";

import { login } from "@/routes";
import { store } from "@/routes/register";

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: null,
});
</script>

<template>
    <Head title="Créer un compte" />

    <div class="min-h-screen w-full overflow-hidden bg-white text-slate-950">
        <div class="grid min-h-screen lg:grid-cols-[1.08fr_0.92fr]">
            <!-- ========================================================= -->
            <!-- LEFT SIDE -->
            <!-- ========================================================= -->

            <section
                class="auth-visual relative hidden min-h-screen overflow-hidden lg:flex"
            >
                <!-- ===================================================== -->
                <!-- BACKGROUND IMAGE -->
                <!-- ===================================================== -->

                <img
                    src="/images/auth/school-campus.jpg"
                    alt="Campus School Up"
                    class="auth-background absolute inset-0 h-full w-full object-cover object-center"
                />

                <!--
                    Overlay intelligent :
                    sombre derrière le texte,
                    beaucoup plus transparent vers la droite.
                -->
                <div
                    class="absolute inset-0 bg-gradient-to-r from-[#00271f]/80 via-[#00382d]/47 to-[#00382d]/12"
                />

                <!-- Bottom -->
                <div
                    class="absolute inset-0 bg-gradient-to-t from-[#001b16]/60 via-[#001b16]/5 to-transparent"
                />

                <!-- Top -->
                <div
                    class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-transparent"
                />

                <!-- Emerald glow -->
                <div
                    class="absolute -left-40 top-[28%] size-[550px] rounded-full bg-emerald-400/10 blur-[150px]"
                />

                <!-- Cyan glow -->
                <div
                    class="absolute -right-48 bottom-[-160px] size-[550px] rounded-full bg-cyan-400/5 blur-[150px]"
                />

                <!-- Grid -->
                <div
                    class="absolute inset-0 opacity-[0.045]"
                    style="
                        background-image:
                            linear-gradient(
                                rgba(255, 255, 255, 0.9) 1px,
                                transparent 1px
                            ),
                            linear-gradient(
                                90deg,
                                rgba(255, 255, 255, 0.9) 1px,
                                transparent 1px
                            );
                        background-size: 70px 70px;
                    "
                />

                <!-- ===================================================== -->
                <!-- CONTENT -->
                <!-- ===================================================== -->

                <div
                    class="relative z-10 flex min-h-screen w-full flex-col px-10 py-9 xl:px-16 xl:py-11 2xl:px-20"
                >
                    <!-- ================================================= -->
                    <!-- BRAND -->
                    <!-- ================================================= -->

                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-12 items-center justify-center rounded-2xl bg-emerald-500 text-white shadow-xl shadow-black/20"
                            >
                                <GraduationCap class="size-6" />
                            </div>

                            <div>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-[18px] font-black tracking-tight text-white"
                                    >
                                        School Up
                                    </span>

                                    <span
                                        class="rounded-full border border-emerald-300/30 bg-emerald-300/10 px-2 py-0.5 text-[8px] font-black tracking-[0.15em] text-emerald-200 backdrop-blur-md"
                                    >
                                        CLOUD
                                    </span>
                                </div>

                                <p
                                    class="mt-0.5 text-[9px] font-bold uppercase tracking-[0.22em] text-white/50"
                                >
                                    Digital School Platform
                                </p>
                            </div>
                        </div>

                        <div
                            class="hidden items-center gap-2 rounded-full border border-white/15 bg-black/20 px-3 py-2 text-[10px] font-semibold text-white/80 backdrop-blur-xl xl:flex"
                        >
                            <ShieldCheck class="size-3.5 text-emerald-300" />

                            Plateforme sécurisée
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- HERO -->
                    <!-- ================================================= -->

                    <div class="my-auto max-w-[680px] py-12">
                        <!-- Badge -->

                        <div
                            class="mb-7 inline-flex items-center gap-2 rounded-full border border-emerald-300/30 bg-[#003b31]/35 px-4 py-2 shadow-lg shadow-black/5 backdrop-blur-xl"
                        >
                            <Sparkles class="size-3.5 text-emerald-300" />

                            <span
                                class="text-[10px] font-black uppercase tracking-[0.12em] text-emerald-50"
                            >
                                Votre établissement, autrement.
                            </span>
                        </div>

                        <!-- TITLE -->

                        <h1
                            class="max-w-[650px] text-[48px] font-black leading-[0.98] tracking-[-0.055em] text-white drop-shadow-[0_3px_18px_rgba(0,0,0,0.25)] xl:text-[60px] 2xl:text-[68px]"
                        >
                            Toute votre école.

                            <br />

                            <span
                                class="relative inline-block text-emerald-400"
                            >
                                Un seul espace.

                                <svg
                                    class="absolute -bottom-2 left-0 h-3 w-full text-emerald-400/80"
                                    viewBox="0 0 400 12"
                                    fill="none"
                                    preserveAspectRatio="none"
                                >
                                    <path
                                        d="M3 9C95 2 270 1 397 6"
                                        stroke="currentColor"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                    />
                                </svg>
                            </span>
                        </h1>

                        <!-- DESCRIPTION -->

                        <p
                            class="mt-9 max-w-[570px] text-[15px] leading-7 text-white/80 drop-shadow-md xl:text-base"
                        >
                            Centralisez les équipes, les services, les demandes
                            et la vie de votre établissement dans une plateforme
                            moderne conçue pour simplifier votre quotidien.
                        </p>

                        <!-- ================================================= -->
                        <!-- FEATURES -->
                        <!-- ================================================= -->

                        <div
                            class="mt-10 max-w-[590px] rounded-[24px] border border-white/[0.18] bg-[#002f27]/45 p-5 shadow-2xl shadow-black/20 backdrop-blur-xl"
                        >
                            <div class="grid grid-cols-3">
                                <!-- MULTI SITES -->

                                <div class="pr-4">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-xl bg-emerald-400/15 text-emerald-300"
                                    >
                                        <School class="size-[18px]" />
                                    </div>

                                    <p
                                        class="mt-3 text-xs font-bold text-white"
                                    >
                                        Multi-sites
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] leading-4 text-white/55"
                                    >
                                        Tous vos établissements
                                    </p>
                                </div>

                                <!-- COLLABORATIF -->

                                <div class="border-x border-white/10 px-5">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-xl bg-cyan-400/15 text-cyan-300"
                                    >
                                        <Users class="size-[18px]" />
                                    </div>

                                    <p
                                        class="mt-3 text-xs font-bold text-white"
                                    >
                                        Collaboratif
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] leading-4 text-white/55"
                                    >
                                        Vos équipes connectées
                                    </p>
                                </div>

                                <!-- SECURE -->

                                <div class="pl-5">
                                    <div
                                        class="flex size-10 items-center justify-center rounded-xl bg-violet-400/15 text-violet-300"
                                    >
                                        <ShieldCheck class="size-[18px]" />
                                    </div>

                                    <p
                                        class="mt-3 text-xs font-bold text-white"
                                    >
                                        Sécurisé
                                    </p>

                                    <p
                                        class="mt-1 text-[10px] leading-4 text-white/55"
                                    >
                                        Accès et rôles maîtrisés
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- ================================================= -->
                        <!-- TRUST -->
                        <!-- ================================================= -->

                        <div
                            class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-2 text-[10px] font-medium text-white/65"
                        >
                            <span class="flex items-center gap-1.5">
                                <Check class="size-3 text-emerald-400" />
                                Gestion centralisée
                            </span>

                            <span class="flex items-center gap-1.5">
                                <Check class="size-3 text-emerald-400" />
                                Données protégées
                            </span>

                            <span class="flex items-center gap-1.5">
                                <Check class="size-3 text-emerald-400" />
                                Expérience simplifiée
                            </span>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- FOOTER -->
                    <!-- ================================================= -->

                    <div
                        class="flex items-center justify-between border-t border-white/10 pt-5"
                    >
                        <p class="text-[10px] text-white/45">
                            © 2026 School Up
                        </p>

                        <p class="text-[10px] text-white/45">
                            Digital School Platform
                        </p>
                    </div>
                </div>
            </section>

            <!-- ========================================================= -->
            <!-- RIGHT SIDE -->
            <!-- ========================================================= -->

            <section
                class="relative flex min-h-screen items-center justify-center overflow-hidden bg-[#f8fafb] px-5 py-10 sm:px-8 lg:px-10 xl:px-14"
            >
                <!-- BACKGROUND DECORATION -->

                <div
                    class="pointer-events-none absolute -right-32 -top-32 size-[430px] rounded-full bg-emerald-100/60 blur-[100px]"
                />

                <div
                    class="pointer-events-none absolute -bottom-40 -left-32 size-[420px] rounded-full bg-cyan-100/40 blur-[120px]"
                />

                <!-- GRID -->

                <div
                    class="pointer-events-none absolute inset-0 opacity-[0.025]"
                    style="
                        background-image:
                            linear-gradient(rgb(15 23 42) 1px, transparent 1px),
                            linear-gradient(
                                90deg,
                                rgb(15 23 42) 1px,
                                transparent 1px
                            );
                        background-size: 50px 50px;
                    "
                />

                <!-- ===================================================== -->
                <!-- FORM CONTAINER -->
                <!-- ===================================================== -->

                <div
                    class="register-container relative z-10 w-full max-w-[520px]"
                >
                    <!-- MOBILE LOGO -->

                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <div
                            class="flex size-11 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-lg shadow-emerald-600/20"
                        >
                            <GraduationCap class="size-5" />
                        </div>

                        <div>
                            <p class="font-black tracking-tight text-slate-950">
                                School Up
                            </p>

                            <p
                                class="text-[9px] font-bold uppercase tracking-[0.15em] text-slate-400"
                            >
                                Digital School Platform
                            </p>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- CARD -->
                    <!-- ================================================= -->

                    <div
                        class="overflow-hidden rounded-[30px] border border-slate-200/80 bg-white shadow-[0_30px_80px_-30px_rgba(15,23,42,0.22)]"
                    >
                        <!-- TOP ACCENT -->

                        <div
                            class="h-[4px] bg-gradient-to-r from-emerald-500 via-teal-400 to-cyan-400"
                        />

                        <div class="p-7 sm:p-9 xl:p-10">
                            <!-- ========================================= -->
                            <!-- HEADER -->
                            <!-- ========================================= -->

                            <div>
                                <div
                                    class="flex size-11 items-center justify-center rounded-2xl border border-emerald-100 bg-emerald-50 text-emerald-600"
                                >
                                    <School class="size-5" />
                                </div>

                                <p
                                    class="mt-6 text-[10px] font-black uppercase tracking-[0.18em] text-emerald-600"
                                >
                                    Bienvenue sur School Up
                                </p>

                                <h2
                                    class="mt-2 text-[29px] font-black tracking-[-0.04em] text-slate-950"
                                >
                                    Créez votre compte
                                </h2>

                                <p
                                    class="mt-2 text-sm leading-6 text-slate-500"
                                >
                                    Quelques informations suffisent pour
                                    commencer votre expérience.
                                </p>
                            </div>

                            <!-- ========================================= -->
                            <!-- FORM -->
                            <!-- ========================================= -->

                            <Form
                                v-bind="store.form()"
                                :reset-on-success="[
                                    'password',
                                    'password_confirmation',
                                ]"
                                v-slot="{ errors, processing }"
                                class="mt-8"
                            >
                                <div class="space-y-5">
                                    <!-- NAME -->

                                    <div>
                                        <Label
                                            for="name"
                                            class="mb-2 block text-xs font-bold text-slate-700"
                                        >
                                            Nom complet
                                        </Label>

                                        <div class="relative">
                                            <UserRound
                                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <Input
                                                id="name"
                                                type="text"
                                                required
                                                v-focus
                                                :tabindex="1"
                                                autocomplete="name"
                                                name="name"
                                                placeholder="Ex. Jaouad Braouz"
                                                class="h-12 rounded-xl border-slate-200 bg-slate-50/70 pl-11 text-sm shadow-none transition-all placeholder:text-slate-400 focus-visible:border-emerald-400 focus-visible:ring-4 focus-visible:ring-emerald-500/10"
                                            />
                                        </div>

                                        <InputError
                                            :message="errors.name"
                                            class="mt-1.5"
                                        />
                                    </div>

                                    <!-- EMAIL -->

                                    <div>
                                        <Label
                                            for="email"
                                            class="mb-2 block text-xs font-bold text-slate-700"
                                        >
                                            Adresse e-mail
                                        </Label>

                                        <div class="relative">
                                            <Mail
                                                class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-slate-400"
                                            />

                                            <Input
                                                id="email"
                                                type="email"
                                                required
                                                :tabindex="2"
                                                autocomplete="email"
                                                name="email"
                                                placeholder="nom@etablissement.ma"
                                                class="h-12 rounded-xl border-slate-200 bg-slate-50/70 pl-11 text-sm shadow-none transition-all placeholder:text-slate-400 focus-visible:border-emerald-400 focus-visible:ring-4 focus-visible:ring-emerald-500/10"
                                            />
                                        </div>

                                        <InputError
                                            :message="errors.email"
                                            class="mt-1.5"
                                        />
                                    </div>

                                    <!-- PASSWORD -->

                                    <div>
                                        <div
                                            class="mb-2 flex items-center justify-between"
                                        >
                                            <Label
                                                for="password"
                                                class="text-xs font-bold text-slate-700"
                                            >
                                                Mot de passe
                                            </Label>

                                            <div
                                                class="flex items-center gap-1 text-[10px] font-bold text-emerald-600"
                                            >
                                                <ShieldCheck class="size-3" />

                                                Sécurisé
                                            </div>
                                        </div>

                                        <PasswordInput
                                            id="password"
                                            required
                                            :tabindex="3"
                                            autocomplete="new-password"
                                            name="password"
                                            placeholder="Créer un mot de passe"
                                            :passwordrules="passwordRules"
                                            class="h-12 rounded-xl border-slate-200 bg-slate-50/70"
                                        />

                                        <InputError
                                            :message="errors.password"
                                            class="mt-1.5"
                                        />
                                    </div>

                                    <!-- CONFIRM PASSWORD -->

                                    <div>
                                        <Label
                                            for="password_confirmation"
                                            class="mb-2 block text-xs font-bold text-slate-700"
                                        >
                                            Confirmer le mot de passe
                                        </Label>

                                        <PasswordInput
                                            id="password_confirmation"
                                            required
                                            :tabindex="4"
                                            autocomplete="new-password"
                                            name="password_confirmation"
                                            placeholder="Confirmer votre mot de passe"
                                            :passwordrules="passwordRules"
                                            class="h-12 rounded-xl border-slate-200 bg-slate-50/70"
                                        />

                                        <InputError
                                            :message="
                                                errors.password_confirmation
                                            "
                                            class="mt-1.5"
                                        />
                                    </div>

                                    <!-- SECURITY INFO -->

                                    <div
                                        class="flex items-start gap-3 rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3"
                                    >
                                        <div
                                            class="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-lg bg-white text-emerald-600 shadow-sm"
                                        >
                                            <LockKeyhole class="size-3.5" />
                                        </div>

                                        <p
                                            class="text-[11px] leading-5 text-emerald-800"
                                        >
                                            Votre compte sera protégé et associé
                                            à votre espace School Up.
                                        </p>
                                    </div>

                                    <!-- ================================= -->
                                    <!-- SUBMIT -->
                                    <!-- ================================= -->

                                    <Button
                                        type="submit"
                                        :tabindex="5"
                                        :disabled="processing"
                                        data-test="register-user-button"
                                        class="group h-12 w-full rounded-xl bg-slate-950 text-sm font-bold text-white shadow-lg shadow-slate-950/10 transition-all hover:-translate-y-0.5 hover:bg-emerald-600 hover:shadow-xl hover:shadow-emerald-600/20 disabled:translate-y-0"
                                    >
                                        <Spinner
                                            v-if="processing"
                                            class="mr-2"
                                        />

                                        <template v-else>
                                            Créer mon compte

                                            <ArrowRight
                                                class="ml-2 size-4 transition-transform duration-200 group-hover:translate-x-1"
                                            />
                                        </template>
                                    </Button>
                                </div>
                            </Form>

                            <!-- ========================================= -->
                            <!-- LOGIN -->
                            <!-- ========================================= -->

                            <div
                                class="mt-7 border-t border-slate-100 pt-6 text-center"
                            >
                                <p class="text-xs text-slate-500">
                                    Vous avez déjà un compte ?

                                    <TextLink
                                        :href="login()"
                                        :tabindex="6"
                                        class="group ml-1 inline-flex items-center gap-1 font-bold text-slate-950 no-underline transition-colors hover:text-emerald-600"
                                    >
                                        Se connecter

                                        <ArrowRight
                                            class="size-3 transition-transform group-hover:translate-x-0.5"
                                        />
                                    </TextLink>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- ================================================= -->
                    <!-- BOTTOM -->
                    <!-- ================================================= -->

                    <div
                        class="mt-5 flex items-center justify-center gap-2 text-[10px] text-slate-400"
                    >
                        <ShieldCheck class="size-3 text-emerald-500" />

                        <span>
                            School Up · Plateforme de gestion scolaire
                        </span>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
/* =========================================================
   BACKGROUND
   ========================================================= */

.auth-background {
    filter: brightness(1.08) contrast(1.04) saturate(1.06);

    animation: schoolUpBackground 20s ease-in-out infinite;
}

/* =========================================================
   IMAGE MOVEMENT
   ========================================================= */

@keyframes schoolUpBackground {
    0%,
    100% {
        transform: scale(1.01);
    }

    50% {
        transform: scale(1.035);
    }
}

/* =========================================================
   FORM ENTRANCE
   ========================================================= */

@keyframes schoolUpFadeIn {
    from {
        opacity: 0;
        transform: translateY(18px) scale(0.99);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.register-container {
    animation: schoolUpFadeIn 0.65s cubic-bezier(0.22, 1, 0.36, 1) both;
}

/* =========================================================
   REDUCED MOTION
   ========================================================= */

@media (prefers-reduced-motion: reduce) {
    .auth-background,
    .register-container {
        animation: none;
    }
}
</style>
