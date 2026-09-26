<script setup lang="ts">
import { Head, Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

import { dashboard, login, register } from "@/routes";

const page = usePage();

const isAuthenticated = computed(() => {
    return !!(page.props.auth as any)?.user;
});
</script>

<template>
    <Head title="School Up - Plateforme de gestion scolaire" />

    <div class="schoolup-page">
        <!-- =========================================================
             NAVBAR
        ========================================================== -->
        <header class="navbar">
            <div class="navbar-container">
                <!-- LOGO -->
                <Link href="/" class="brand">
                    <div class="brand-icon">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M3 9.5L12 5L21 9.5L12 14L3 9.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M7 12V16.5C7 17.5 9.3 19 12 19C14.7 19 17 17.5 17 16.5V12"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>

                    <div class="brand-text">
                        <div class="brand-name">
                            School Up
                            <span>CLOUD</span>
                        </div>

                        <div class="brand-subtitle">
                            DIGITAL SCHOOL PLATFORM
                        </div>
                    </div>
                </Link>

                <!-- DESKTOP MENU -->
                <nav class="desktop-menu">
                    <a href="#solution">La solution</a>
                    <a href="#features">Fonctionnalités</a>
                    <a href="#benefits">Pourquoi School Up</a>
                </nav>

                <!-- ACTIONS -->
                <div class="navbar-actions">
                    <template v-if="isAuthenticated">
                        <Link :href="dashboard()" class="btn-dashboard">
                            Accéder au dashboard

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >
                                <path
                                    d="M5 12h14M13 6l6 6-6 6"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </Link>
                    </template>

                    <template v-else>
                        <Link :href="login()" class="btn-login">
                            Se connecter
                        </Link>

                        <Link :href="register()" class="btn-register">
                            Créer un compte

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                            >
                                <path
                                    d="M5 12h14M13 6l6 6-6 6"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </Link>
                    </template>
                </div>
            </div>
        </header>

        <!-- =========================================================
             HERO
        ========================================================== -->
        <main>
            <section class="hero">
                <!-- BACKGROUND IMAGE -->
                <div class="hero-image"></div>

                <!-- DARK / GREEN OVERLAY -->
                <div class="hero-overlay"></div>

                <!-- GRID -->
                <div class="hero-grid"></div>

                <!-- GLOW EFFECTS -->
                <div class="hero-glow hero-glow-1"></div>
                <div class="hero-glow hero-glow-2"></div>

                <!-- CONTENT -->
                <div class="hero-container">
                    <div class="hero-content">
                        <!-- BADGE -->
                        <div class="hero-badge">
                            <span class="badge-dot"></span>

                            La nouvelle génération de gestion scolaire
                        </div>

                        <!-- TITLE -->
                        <h1>
                            Toute votre école.

                            <span> Un seul espace. </span>
                        </h1>

                        <!-- DESCRIPTION -->
                        <p class="hero-description">
                            Centralisez les équipes, les services, les demandes,
                            les rendez-vous et la vie de votre établissement
                            dans une plateforme moderne pensée pour simplifier
                            votre quotidien.
                        </p>

                        <!-- CTA -->
                        <div class="hero-actions">
                            <template v-if="isAuthenticated">
                                <Link
                                    :href="dashboard()"
                                    class="hero-primary-button"
                                >
                                    Accéder à mon espace

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M5 12h14M13 6l6 6-6 6"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </Link>
                            </template>

                            <template v-else>
                                <Link
                                    :href="register()"
                                    class="hero-primary-button"
                                >
                                    Commencer maintenant

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M5 12h14M13 6l6 6-6 6"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </Link>

                                <Link
                                    :href="login()"
                                    class="hero-secondary-button"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M10 17l5-5-5-5M15 12H3"
                                            stroke-width="1.8"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>

                                    J'ai déjà un compte
                                </Link>
                            </template>
                        </div>

                        <!-- TRUST -->
                        <div class="trust">
                            <div class="trust-item">
                                <div class="trust-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"
                                            stroke-width="1.7"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="m9 12 2 2 4-4"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <strong>Sécurisé</strong>
                                    <span>Données protégées</span>
                                </div>
                            </div>

                            <div class="trust-divider"></div>

                            <div class="trust-item">
                                <div class="trust-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                        <circle
                                            cx="9"
                                            cy="7"
                                            r="4"
                                            stroke-width="1.7"
                                        />
                                        <path
                                            d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <strong>Collaboratif</strong>
                                    <span>Équipes connectées</span>
                                </div>
                            </div>

                            <div class="trust-divider"></div>

                            <div class="trust-item">
                                <div class="trust-icon">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M3 21h18M5 21V7l7-4 7 4v14"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                        <path
                                            d="M9 9h1M14 9h1M9 13h1M14 13h1M10 21v-4h4v4"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </div>

                                <div>
                                    <strong>Multi-sites</strong>
                                    <span>Tous vos établissements</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FLOATING DASHBOARD CARD -->
                    <div class="hero-dashboard-card">
                        <div class="dashboard-card-header">
                            <div class="dashboard-card-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <div class="online">
                                <span></span>
                                Établissement actif
                            </div>
                        </div>

                        <div class="dashboard-title">
                            <div>
                                <small>VUE D'ENSEMBLE</small>
                                <h3>Bonjour 👋</h3>
                            </div>

                            <div class="dashboard-avatar">SU</div>
                        </div>

                        <div class="dashboard-stats">
                            <div class="stat">
                                <div class="stat-icon green">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                                            stroke-width="1.7"
                                        />
                                        <circle
                                            cx="9"
                                            cy="7"
                                            r="4"
                                            stroke-width="1.7"
                                        />
                                    </svg>
                                </div>

                                <span>Élèves</span>
                                <strong>1 248</strong>
                                <small>+4.8% cette année</small>
                            </div>

                            <div class="stat">
                                <div class="stat-icon blue">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <path
                                            d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"
                                            stroke-width="1.7"
                                        />
                                        <path
                                            d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"
                                            stroke-width="1.7"
                                        />
                                    </svg>
                                </div>

                                <span>Classes</span>
                                <strong>42</strong>
                                <small>Tous cycles</small>
                            </div>

                            <div class="stat">
                                <div class="stat-icon purple">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                    >
                                        <rect
                                            x="3"
                                            y="5"
                                            width="18"
                                            height="16"
                                            rx="2"
                                            stroke-width="1.7"
                                        />
                                        <path
                                            d="M16 3v4M8 3v4M3 11h18"
                                            stroke-width="1.7"
                                        />
                                    </svg>
                                </div>

                                <span>Rendez-vous</span>
                                <strong>18</strong>
                                <small>Aujourd'hui</small>
                            </div>
                        </div>

                        <div class="dashboard-progress">
                            <div class="progress-top">
                                <div>
                                    <div class="progress-check">✓</div>

                                    <span>Votre espace est prêt</span>
                                </div>

                                <strong>100%</strong>
                            </div>

                            <div class="progress-bar">
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SCROLL -->
                <a href="#solution" class="scroll-indicator">
                    <span>Découvrir School Up</span>

                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path
                            d="m6 9 6 6 6-6"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </a>
            </section>

            <!-- =========================================================
                 SOLUTION
            ========================================================== -->
            <section id="solution" class="solution-section">
                <div class="section-container">
                    <div class="section-badge">SCHOOL UP</div>

                    <h2 class="section-title">
                        La gestion scolaire,
                        <span>simplement.</span>
                    </h2>

                    <p class="section-description">
                        Un environnement unique pour connecter la direction,
                        l'administration, les équipes pédagogiques et les
                        familles.
                    </p>

                    <div class="solution-grid">
                        <article class="solution-card large">
                            <div class="card-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                >
                                    <path
                                        d="M3 21h18M5 21V7l7-4 7 4v14"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M9 9h1M14 9h1M9 13h1M14 13h1"
                                        stroke-width="1.7"
                                    />
                                </svg>
                            </div>

                            <span class="card-number">01</span>

                            <h3>Votre établissement centralisé</h3>

                            <p>
                                Retrouvez vos services, utilisateurs,
                                établissements et activités depuis une seule
                                interface.
                            </p>

                            <div class="mini-dashboard">
                                <div class="mini-sidebar">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>

                                <div class="mini-content">
                                    <div class="mini-header"></div>

                                    <div class="mini-cards">
                                        <div></div>
                                        <div></div>
                                        <div></div>
                                    </div>

                                    <div class="mini-chart">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                </div>
                            </div>
                        </article>

                        <article class="solution-card">
                            <div class="card-icon blue-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                >
                                    <path
                                        d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4v8Z"
                                        stroke-width="1.7"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>

                            <span class="card-number">02</span>

                            <h3>Des échanges simplifiés</h3>

                            <p>
                                Centralisez les demandes, réclamations et
                                communications.
                            </p>
                        </article>

                        <article class="solution-card">
                            <div class="card-icon purple-icon">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                >
                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="16"
                                        rx="2"
                                        stroke-width="1.7"
                                    />
                                    <path
                                        d="M16 3v4M8 3v4M3 11h18"
                                        stroke-width="1.7"
                                    />
                                </svg>
                            </div>

                            <span class="card-number">03</span>

                            <h3>Rendez-vous organisés</h3>

                            <p>
                                Gérez les rendez-vous et disponibilités depuis
                                votre espace.
                            </p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- =========================================================
                 FEATURES
            ========================================================== -->
            <section id="features" class="features-section">
                <div class="section-container">
                    <div class="features-heading">
                        <div>
                            <div class="section-badge light">
                                UNE PLATEFORME COMPLÈTE
                            </div>

                            <h2>
                                Tout ce dont votre
                                <span>école a besoin.</span>
                            </h2>
                        </div>

                        <p>
                            School Up rassemble les outils essentiels à la
                            gestion quotidienne de votre établissement.
                        </p>
                    </div>

                    <div class="features-grid">
                        <div class="feature-item">
                            <span>01</span>
                            <h3>Réclamations</h3>
                            <p>Suivi structuré des demandes et incidents.</p>
                        </div>

                        <div class="feature-item">
                            <span>02</span>
                            <h3>Demandes internes</h3>
                            <p>Digitalisez les processus entre vos équipes.</p>
                        </div>

                        <div class="feature-item">
                            <span>03</span>
                            <h3>Rendez-vous</h3>
                            <p>
                                Organisez facilement les échanges avec les
                                familles.
                            </p>
                        </div>

                        <div class="feature-item">
                            <span>04</span>
                            <h3>Multi-établissements</h3>
                            <p>Gérez plusieurs sites depuis le même compte.</p>
                        </div>

                        <div class="feature-item">
                            <span>05</span>
                            <h3>Rôles & permissions</h3>
                            <p>
                                Chaque utilisateur accède uniquement à ce dont
                                il a besoin.
                            </p>
                        </div>

                        <div class="feature-item">
                            <span>06</span>
                            <h3>Notifications</h3>
                            <p>Restez informé des événements importants.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- =========================================================
                 BENEFITS
            ========================================================== -->
            <section id="benefits" class="benefits-section">
                <div class="section-container">
                    <div class="benefits-box">
                        <div>
                            <div class="section-badge">PRÊT À COMMENCER ?</div>

                            <h2>
                                Votre école mérite
                                <span>mieux.</span>
                            </h2>

                            <p>
                                Rejoignez School Up et donnez à vos équipes un
                                environnement moderne, simple et centralisé.
                            </p>
                        </div>

                        <div class="benefits-actions">
                            <template v-if="isAuthenticated">
                                <Link :href="dashboard()" class="cta-dark">
                                    Accéder au dashboard
                                    <span>→</span>
                                </Link>
                            </template>

                            <template v-else>
                                <Link :href="register()" class="cta-dark">
                                    Créer mon espace
                                    <span>→</span>
                                </Link>

                                <Link :href="login()" class="cta-light">
                                    Se connecter
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <!-- =========================================================
             FOOTER
        ========================================================== -->
        <footer>
            <div class="footer-container">
                <div class="footer-brand">
                    <div class="brand-icon small">
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                d="M3 9.5L12 5L21 9.5L12 14L3 9.5Z"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M7 12V16.5C7 17.5 9.3 19 12 19C14.7 19 17 17.5 17 16.5V12"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>

                    <div>
                        <strong>School Up</strong>
                        <span>Digital School Platform</span>
                    </div>
                </div>

                <p>© 2026 School Up. Tous droits réservés.</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ================================================================
   BASE
================================================================ */

.schoolup-page {
    --green: #00c889;
    --green-dark: #009f72;
    --navy: #05091d;
    --text: #07101f;
    --muted: #64748b;

    min-height: 100vh;
    background: #ffffff;
    color: var(--text);
    font-family:
        Inter,
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;
}

* {
    box-sizing: border-box;
}

a {
    text-decoration: none;
}

/* ================================================================
   NAVBAR
================================================================ */

.navbar {
    position: absolute;
    z-index: 50;
    top: 0;
    left: 0;
    width: 100%;
}

.navbar-container {
    width: min(1440px, calc(100% - 80px));
    height: 100px;
    margin: auto;

    display: flex;
    align-items: center;
    justify-content: space-between;

    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.brand {
    display: flex;
    align-items: center;
    gap: 13px;
    color: white;
}

.brand-icon {
    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 15px;

    color: white;

    background: linear-gradient(135deg, #00d997, #00ad7c);

    box-shadow: 0 10px 30px rgba(0, 200, 137, 0.28);
}

.brand-icon svg {
    width: 26px;
    height: 26px;
}

.brand-name {
    display: flex;
    align-items: center;
    gap: 9px;

    font-size: 18px;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.brand-name span {
    padding: 3px 8px;

    border: 1px solid rgba(0, 226, 157, 0.55);
    border-radius: 20px;

    color: #5df4c0;

    font-size: 8px;
    font-weight: 800;
    letter-spacing: 1px;
}

.brand-subtitle {
    margin-top: 3px;

    color: rgba(255, 255, 255, 0.55);

    font-size: 8px;
    font-weight: 700;
    letter-spacing: 2px;
}

.desktop-menu {
    display: flex;
    align-items: center;
    gap: 36px;
}

.desktop-menu a {
    position: relative;

    color: rgba(255, 255, 255, 0.72);

    font-size: 13px;
    font-weight: 600;

    transition: 0.25s;
}

.desktop-menu a:hover {
    color: white;
}

.desktop-menu a::after {
    position: absolute;

    content: "";

    left: 0;
    bottom: -10px;

    width: 0;
    height: 2px;

    background: var(--green);

    transition: width 0.25s;
}

.desktop-menu a:hover::after {
    width: 100%;
}

.navbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-login {
    padding: 12px 18px;

    color: white;

    font-size: 13px;
    font-weight: 700;
}

.btn-register,
.btn-dashboard {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 13px 18px;

    border-radius: 12px;

    color: #03150f;
    background: white;

    font-size: 12px;
    font-weight: 800;

    transition:
        transform 0.25s,
        box-shadow 0.25s;
}

.btn-register:hover,
.btn-dashboard:hover {
    transform: translateY(-2px);

    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.18);
}

.btn-register svg,
.btn-dashboard svg {
    width: 17px;
}

/* ================================================================
   HERO
================================================================ */

.hero {
    position: relative;

    min-height: 100vh;

    overflow: hidden;

    background: #00372d;
}

.hero-image {
    position: absolute;
    inset: 0;

    background-image: url("/images/schoolup-welcome-hero.png");

    background-position: center;
    background-size: cover;

    transform: scale(1.02);

    animation: heroZoom 16s ease-in-out infinite alternate;
}

@keyframes heroZoom {
    from {
        transform: scale(1.02);
    }

    to {
        transform: scale(1.08);
    }
}

.hero-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            90deg,
            rgba(0, 39, 32, 0.97) 0%,
            rgba(0, 45, 36, 0.91) 36%,
            rgba(0, 45, 36, 0.66) 65%,
            rgba(0, 20, 20, 0.35) 100%
        ),
        linear-gradient(0deg, rgba(0, 20, 17, 0.62), transparent 50%);
}

.hero-grid {
    position: absolute;
    inset: 0;

    opacity: 0.1;

    background-image:
        linear-gradient(rgba(255, 255, 255, 0.3) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.3) 1px, transparent 1px);

    background-size: 72px 72px;
}

.hero-glow {
    position: absolute;

    border-radius: 50%;

    filter: blur(100px);

    pointer-events: none;
}

.hero-glow-1 {
    top: 15%;
    left: -200px;

    width: 500px;
    height: 500px;

    background: rgba(0, 221, 155, 0.15);
}

.hero-glow-2 {
    right: -200px;
    bottom: -200px;

    width: 600px;
    height: 600px;

    background: rgba(0, 184, 255, 0.12);
}

.hero-container {
    position: relative;
    z-index: 5;

    width: min(1440px, calc(100% - 80px));
    min-height: 100vh;

    margin: auto;
    padding-top: 150px;
    padding-bottom: 100px;

    display: flex;
    align-items: center;
}

.hero-content {
    width: 680px;

    animation: heroContent 0.8s ease both;
}

@keyframes heroContent {
    from {
        opacity: 0;
        transform: translateY(30px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.hero-badge {
    width: fit-content;

    display: flex;
    align-items: center;
    gap: 9px;

    margin-bottom: 27px;
    padding: 9px 14px;

    border: 1px solid rgba(73, 238, 186, 0.35);
    border-radius: 100px;

    color: #b6fbe3;
    background: rgba(0, 197, 136, 0.08);

    backdrop-filter: blur(10px);

    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.badge-dot {
    width: 7px;
    height: 7px;

    border-radius: 50%;

    background: #00e69f;

    box-shadow: 0 0 0 5px rgba(0, 230, 159, 0.12);
}

.hero h1 {
    max-width: 670px;

    margin: 0;

    color: white;

    font-size: clamp(55px, 5.2vw, 88px);
    font-weight: 800;
    line-height: 0.97;
    letter-spacing: -5px;
}

.hero h1 span {
    position: relative;

    display: block;

    margin-top: 8px;

    color: #00dc98;
}

.hero h1 span::after {
    position: absolute;

    content: "";

    left: 3px;
    bottom: -9px;

    width: 72%;
    height: 4px;

    border-radius: 20px;

    background: linear-gradient(90deg, #00dc98, rgba(0, 220, 152, 0));
}

.hero-description {
    max-width: 620px;

    margin: 38px 0 0;

    color: rgba(255, 255, 255, 0.72);

    font-size: 17px;
    line-height: 1.8;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 13px;

    margin-top: 34px;
}

.hero-primary-button {
    display: flex;
    align-items: center;
    gap: 22px;

    padding: 17px 21px 17px 24px;

    border-radius: 13px;

    color: #02130e;
    background: #00d995;

    font-size: 13px;
    font-weight: 800;

    box-shadow: 0 14px 40px rgba(0, 217, 149, 0.23);

    transition:
        transform 0.25s,
        box-shadow 0.25s;
}

.hero-primary-button:hover {
    transform: translateY(-3px);

    box-shadow: 0 18px 45px rgba(0, 217, 149, 0.35);
}

.hero-primary-button svg {
    width: 19px;
}

.hero-secondary-button {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 16px 21px;

    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 13px;

    color: white;
    background: rgba(255, 255, 255, 0.07);

    backdrop-filter: blur(15px);

    font-size: 13px;
    font-weight: 700;

    transition: background 0.25s;
}

.hero-secondary-button:hover {
    background: rgba(255, 255, 255, 0.13);
}

.hero-secondary-button svg {
    width: 18px;
}

.trust {
    width: fit-content;

    display: flex;
    align-items: center;
    gap: 22px;

    margin-top: 48px;
    padding: 15px 20px;

    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 16px;

    background: rgba(255, 255, 255, 0.06);

    backdrop-filter: blur(18px);
}

.trust-item {
    display: flex;
    align-items: center;
    gap: 10px;
}

.trust-icon {
    width: 35px;
    height: 35px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    color: #36e6af;
    background: rgba(0, 218, 151, 0.12);
}

.trust-icon svg {
    width: 18px;
}

.trust-item div:last-child {
    display: flex;
    flex-direction: column;
}

.trust-item strong {
    color: white;

    font-size: 10px;
}

.trust-item span {
    margin-top: 2px;

    color: rgba(255, 255, 255, 0.45);

    font-size: 8px;
}

.trust-divider {
    width: 1px;
    height: 32px;

    background: rgba(255, 255, 255, 0.12);
}

/* ================================================================
   FLOATING DASHBOARD
================================================================ */

.hero-dashboard-card {
    position: absolute;

    right: -30px;
    bottom: 80px;

    width: 510px;

    padding: 20px;

    border: 1px solid rgba(255, 255, 255, 0.55);
    border-radius: 25px;

    background: rgba(255, 255, 255, 0.94);

    box-shadow: 0 35px 90px rgba(0, 20, 15, 0.25);

    backdrop-filter: blur(30px);

    animation: floatingCard 6s ease-in-out infinite;
}

@keyframes floatingCard {
    0%,
    100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-12px);
    }
}

.dashboard-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding-bottom: 16px;

    border-bottom: 1px solid #edf0f2;
}

.dashboard-card-dots {
    display: flex;
    gap: 5px;
}

.dashboard-card-dots span {
    width: 7px;
    height: 7px;

    border-radius: 50%;
}

.dashboard-card-dots span:nth-child(1) {
    background: #ff8c9b;
}

.dashboard-card-dots span:nth-child(2) {
    background: #ffd85c;
}

.dashboard-card-dots span:nth-child(3) {
    background: #5ee6b2;
}

.online {
    display: flex;
    align-items: center;
    gap: 6px;

    padding: 6px 10px;

    border-radius: 20px;

    color: #009d6c;
    background: #ebfff7;

    font-size: 8px;
    font-weight: 800;
}

.online span {
    width: 5px;
    height: 5px;

    border-radius: 50%;

    background: #00bd80;
}

.dashboard-title {
    display: flex;
    align-items: center;
    justify-content: space-between;

    margin-top: 18px;
}

.dashboard-title small {
    color: #9aa5b4;

    font-size: 7px;
    font-weight: 800;
    letter-spacing: 1.2px;
}

.dashboard-title h3 {
    margin: 4px 0 0;

    color: #07101f;

    font-size: 20px;
    letter-spacing: -0.7px;
}

.dashboard-avatar {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 11px;

    color: #007b59;
    background: #e8fff7;

    font-size: 9px;
    font-weight: 800;
}

.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 9px;

    margin-top: 17px;
}

.stat {
    padding: 14px;

    border: 1px solid #edf0f3;
    border-radius: 14px;

    background: white;
}

.stat-icon {
    width: 30px;
    height: 30px;

    display: flex;
    align-items: center;
    justify-content: center;

    margin-bottom: 13px;

    border-radius: 9px;
}

.stat-icon svg {
    width: 16px;
}

.stat-icon.green {
    color: #00a875;
    background: #e9fff7;
}

.stat-icon.blue {
    color: #009dcc;
    background: #ebfaff;
}

.stat-icon.purple {
    color: #7846df;
    background: #f2edff;
}

.stat > span {
    display: block;

    color: #8a96a8;

    font-size: 8px;
}

.stat strong {
    display: block;

    margin-top: 5px;

    color: #07101f;

    font-size: 20px;
}

.stat small {
    display: block;

    margin-top: 8px;

    color: #00a976;

    font-size: 7px;
    font-weight: 700;
}

.stat:nth-child(2) small {
    color: #0099c7;
}

.stat:nth-child(3) small {
    color: #7846df;
}

.dashboard-progress {
    margin-top: 10px;
    padding: 13px;

    border-radius: 13px;

    background: #f8fafb;
}

.progress-top,
.progress-top > div {
    display: flex;
    align-items: center;
}

.progress-top {
    justify-content: space-between;
}

.progress-top > div {
    gap: 8px;
}

.progress-check {
    width: 24px;
    height: 24px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    color: white;
    background: #00b980;

    font-size: 10px;
}

.progress-top span {
    color: #435166;

    font-size: 8px;
    font-weight: 700;
}

.progress-top strong {
    color: #00a773;

    font-size: 8px;
}

.progress-bar {
    height: 4px;

    margin-top: 10px;

    overflow: hidden;

    border-radius: 20px;

    background: #e5e9ec;
}

.progress-bar span {
    display: block;

    width: 100%;
    height: 100%;

    border-radius: inherit;

    background: linear-gradient(90deg, #00bd82, #00e3a0);
}

.scroll-indicator {
    position: absolute;
    z-index: 8;

    left: 50%;
    bottom: 23px;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;

    color: rgba(255, 255, 255, 0.5);

    font-size: 8px;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;

    transform: translateX(-50%);
}

.scroll-indicator svg {
    width: 17px;

    animation: scrollArrow 1.5s infinite;
}

@keyframes scrollArrow {
    50% {
        transform: translateY(5px);
    }
}

/* ================================================================
   COMMON SECTION
================================================================ */

.section-container {
    width: min(1240px, calc(100% - 80px));
    margin: auto;
}

.section-badge {
    width: fit-content;

    padding: 7px 11px;

    border: 1px solid #b7efdb;
    border-radius: 100px;

    color: #009f70;
    background: #f0fff9;

    font-size: 9px;
    font-weight: 800;
    letter-spacing: 1.4px;
}

.section-title {
    margin: 19px 0 0;

    color: #07101f;

    font-size: clamp(38px, 4vw, 60px);
    line-height: 1.03;
    letter-spacing: -3px;
}

.section-title span {
    color: #00b77d;
}

.section-description {
    max-width: 580px;

    margin: 22px 0 0;

    color: #6d7b8f;

    font-size: 15px;
    line-height: 1.8;
}

/* ================================================================
   SOLUTION
================================================================ */

.solution-section {
    padding: 120px 0;

    background:
        radial-gradient(
            circle at 80% 20%,
            rgba(0, 207, 145, 0.08),
            transparent 25%
        ),
        white;
}

.solution-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr;
    gap: 18px;

    margin-top: 55px;
}

.solution-card {
    position: relative;

    min-height: 310px;

    padding: 30px;

    overflow: hidden;

    border: 1px solid #e7ebee;
    border-radius: 23px;

    background: #fbfcfc;

    transition:
        transform 0.3s,
        box-shadow 0.3s;
}

.solution-card:hover {
    transform: translateY(-6px);

    box-shadow: 0 25px 60px rgba(10, 30, 25, 0.08);
}

.solution-card.large {
    min-height: 430px;

    background: linear-gradient(145deg, #f4fffb, white);
}

.card-icon {
    width: 44px;
    height: 44px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 13px;

    color: #00a977;
    background: #e7fff6;
}

.card-icon svg {
    width: 22px;
}

.blue-icon {
    color: #009bc9;
    background: #e9faff;
}

.purple-icon {
    color: #7846df;
    background: #f2edff;
}

.card-number {
    position: absolute;

    top: 28px;
    right: 28px;

    color: #cbd2d9;

    font-size: 11px;
    font-weight: 800;
}

.solution-card h3 {
    max-width: 350px;

    margin: 24px 0 0;

    font-size: 24px;
    letter-spacing: -1px;
}

.solution-card p {
    max-width: 390px;

    margin: 12px 0 0;

    color: #718096;

    font-size: 13px;
    line-height: 1.7;
}

.mini-dashboard {
    position: absolute;

    right: -20px;
    bottom: -50px;
    left: 30px;

    height: 210px;

    display: flex;

    overflow: hidden;

    border: 1px solid #e7ecea;
    border-radius: 17px 17px 0 0;

    background: white;

    box-shadow: 0 20px 50px rgba(0, 40, 30, 0.08);
}

.mini-sidebar {
    width: 55px;

    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;

    padding-top: 20px;

    border-right: 1px solid #edf0ef;

    background: #f9fbfa;
}

.mini-sidebar span {
    width: 22px;
    height: 7px;

    border-radius: 10px;

    background: #dce6e2;
}

.mini-sidebar span:first-child {
    background: #00c88b;
}

.mini-content {
    flex: 1;

    padding: 18px;
}

.mini-header {
    width: 40%;
    height: 10px;

    border-radius: 20px;

    background: #dfe6e4;
}

.mini-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;

    margin-top: 20px;
}

.mini-cards div {
    height: 50px;

    border-radius: 9px;

    background: #f4f7f6;
}

.mini-chart {
    height: 70px;

    display: flex;
    align-items: flex-end;
    gap: 7px;

    margin-top: 16px;
}

.mini-chart span {
    flex: 1;

    border-radius: 5px 5px 0 0;

    background: #d9f9ee;
}

.mini-chart span:nth-child(1) {
    height: 30%;
}

.mini-chart span:nth-child(2) {
    height: 55%;
}

.mini-chart span:nth-child(3) {
    height: 42%;
}

.mini-chart span:nth-child(4) {
    height: 75%;
}

.mini-chart span:nth-child(5) {
    height: 62%;
}

.mini-chart span:nth-child(6) {
    height: 90%;
    background: #00c88b;
}

/* ================================================================
   FEATURES
================================================================ */

.features-section {
    padding: 120px 0;

    color: white;
    background: #061b18;
}

.features-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 50px;
}

.section-badge.light {
    border-color: rgba(0, 226, 157, 0.3);

    color: #50edbd;
    background: rgba(0, 213, 148, 0.08);
}

.features-heading h2 {
    max-width: 600px;

    margin: 20px 0 0;

    font-size: clamp(38px, 4vw, 60px);
    line-height: 1.04;
    letter-spacing: -3px;
}

.features-heading h2 span {
    color: #00d895;
}

.features-heading > p {
    max-width: 430px;

    margin: 0;

    color: rgba(255, 255, 255, 0.5);

    font-size: 14px;
    line-height: 1.8;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);

    margin-top: 70px;

    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-left: 1px solid rgba(255, 255, 255, 0.1);
}

.feature-item {
    min-height: 230px;

    padding: 30px;

    border-right: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);

    transition: background 0.3s;
}

.feature-item:hover {
    background: rgba(0, 213, 148, 0.06);
}

.feature-item > span {
    color: #00d895;

    font-size: 9px;
    font-weight: 800;
}

.feature-item h3 {
    margin: 65px 0 0;

    font-size: 20px;
}

.feature-item p {
    max-width: 280px;

    color: rgba(255, 255, 255, 0.45);

    font-size: 12px;
    line-height: 1.7;
}

/* ================================================================
   CTA
================================================================ */

.benefits-section {
    padding: 100px 0;

    background: #f7faf9;
}

.benefits-box {
    position: relative;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 60px;

    padding: 65px;

    overflow: hidden;

    border: 1px solid #dce9e5;
    border-radius: 30px;

    background:
        radial-gradient(
            circle at 85% 30%,
            rgba(0, 207, 145, 0.15),
            transparent 35%
        ),
        white;
}

.benefits-box h2 {
    max-width: 620px;

    margin: 20px 0 0;

    font-size: clamp(38px, 4vw, 58px);
    line-height: 1;
    letter-spacing: -3px;
}

.benefits-box h2 span {
    color: #00b77d;
}

.benefits-box p {
    max-width: 540px;

    margin: 20px 0 0;

    color: #718096;

    font-size: 14px;
    line-height: 1.8;
}

.benefits-actions {
    min-width: 230px;

    display: flex;
    flex-direction: column;
    gap: 10px;
}

.cta-dark,
.cta-light {
    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 16px 18px;

    border-radius: 12px;

    font-size: 12px;
    font-weight: 800;
}

.cta-dark {
    color: white;
    background: #07101f;
}

.cta-light {
    justify-content: center;

    border: 1px solid #e2e7e5;

    color: #07101f;
    background: white;
}

/* ================================================================
   FOOTER
================================================================ */

footer {
    padding: 28px 0;

    border-top: 1px solid #edf0ef;

    background: white;
}

.footer-container {
    width: min(1240px, calc(100% - 80px));
    margin: auto;

    display: flex;
    align-items: center;
    justify-content: space-between;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: 10px;
}

.brand-icon.small {
    width: 36px;
    height: 36px;

    border-radius: 10px;
}

.brand-icon.small svg {
    width: 20px;
}

.footer-brand > div:last-child {
    display: flex;
    flex-direction: column;
}

.footer-brand strong {
    font-size: 12px;
}

.footer-brand span {
    margin-top: 2px;

    color: #9aa4b2;

    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

footer p {
    color: #9aa4b2;

    font-size: 10px;
}

/* ================================================================
   RESPONSIVE
================================================================ */

@media (max-width: 1200px) {
    .hero-dashboard-card {
        width: 430px;
        right: -130px;

        opacity: 0.85;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }
}

@media (max-width: 1000px) {
    .desktop-menu {
        display: none;
    }

    .hero-dashboard-card {
        display: none;
    }

    .hero-content {
        width: 100%;
        max-width: 700px;
    }

    .hero-overlay {
        background: linear-gradient(
            90deg,
            rgba(0, 40, 33, 0.96),
            rgba(0, 40, 33, 0.72)
        );
    }

    .solution-grid {
        grid-template-columns: 1fr 1fr;
    }

    .solution-card.large {
        grid-column: span 2;
    }

    .features-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 760px) {
    .navbar-container,
    .hero-container,
    .section-container,
    .footer-container {
        width: calc(100% - 36px);
    }

    .navbar-container {
        height: 82px;
    }

    .brand-subtitle,
    .brand-name span {
        display: none;
    }

    .brand-icon {
        width: 42px;
        height: 42px;
    }

    .btn-login {
        display: none;
    }

    .btn-register,
    .btn-dashboard {
        padding: 11px 13px;

        font-size: 10px;
    }

    .hero {
        min-height: 760px;
    }

    .hero-container {
        min-height: 760px;

        padding-top: 130px;
        padding-bottom: 70px;
    }

    .hero-image {
        background-position: 62% center;
    }

    .hero-overlay {
        background: linear-gradient(
            90deg,
            rgba(0, 38, 31, 0.97),
            rgba(0, 38, 31, 0.75)
        );
    }

    .hero h1 {
        font-size: 52px;
        letter-spacing: -3.5px;
    }

    .hero-description {
        margin-top: 30px;

        font-size: 14px;
    }

    .hero-actions {
        flex-direction: column;
    }

    .hero-primary-button,
    .hero-secondary-button {
        width: 100%;

        justify-content: center;
    }

    .trust {
        width: 100%;

        justify-content: space-between;

        gap: 7px;
        padding: 12px;
    }

    .trust-item {
        gap: 7px;
    }

    .trust-icon {
        width: 30px;
        height: 30px;
    }

    .trust-item span {
        display: none;
    }

    .scroll-indicator {
        display: none;
    }

    .solution-section,
    .features-section {
        padding: 80px 0;
    }

    .solution-grid {
        grid-template-columns: 1fr;
    }

    .solution-card.large {
        grid-column: auto;
    }

    .solution-card {
        min-height: 280px;
    }

    .solution-card.large {
        min-height: 430px;
    }

    .features-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .features-grid {
        grid-template-columns: 1fr;
        margin-top: 45px;
    }

    .feature-item {
        min-height: 180px;
    }

    .feature-item h3 {
        margin-top: 40px;
    }

    .benefits-section {
        padding: 60px 0;
    }

    .benefits-box {
        align-items: stretch;
        flex-direction: column;

        padding: 35px 25px;
    }

    .benefits-actions {
        width: 100%;
    }

    .footer-container {
        flex-direction: column;
        gap: 20px;

        text-align: center;
    }
}

@media (max-width: 480px) {
    .brand-name {
        font-size: 15px;
    }

    .btn-register svg,
    .btn-dashboard svg {
        display: none;
    }

    .hero h1 {
        font-size: 44px;
    }

    .hero-badge {
        font-size: 8px;
    }

    .trust-divider {
        display: none;
    }

    .trust {
        gap: 5px;
    }

    .trust-item strong {
        font-size: 8px;
    }

    .section-title,
    .features-heading h2,
    .benefits-box h2 {
        letter-spacing: -2px;
    }
}
</style>
