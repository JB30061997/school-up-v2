import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'School Up';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    layout: (name) => {
        switch (true) {
            /*
            |--------------------------------------------------------------------------
            | Pages sans layout
            |--------------------------------------------------------------------------
            */

            case name === 'Welcome':
                return null;

            /*
            |--------------------------------------------------------------------------
            | Login
            |--------------------------------------------------------------------------
            |
            | Login possède son propre design plein écran.
            | On ne lui applique donc PAS AuthLayout.
            |
            */

            case name === 'auth/Login':
                return null;

            /*
            |--------------------------------------------------------------------------
            | Autres pages Auth
            |--------------------------------------------------------------------------
            |
            | Register
            | ForgotPassword
            | ResetPassword
            | VerifyEmail
            | ConfirmPassword
            | TwoFactorChallenge
            |
            */

            case name.startsWith('auth/'):
                return AuthLayout;

            /*
            |--------------------------------------------------------------------------
            | Settings
            |--------------------------------------------------------------------------
            */

            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];

            /*
            |--------------------------------------------------------------------------
            | Application
            |--------------------------------------------------------------------------
            */

            default:
                return AppLayout;
        }
    },

    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },

    progress: {
        color: '#10B981',
    },
});

/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/

initializeTheme();

/*
|--------------------------------------------------------------------------
| Flash Toast
|--------------------------------------------------------------------------
*/

initializeFlashToast();