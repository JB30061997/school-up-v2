<script setup lang="ts">
/*
|--------------------------------------------------------------------------
| Auth Layout
|--------------------------------------------------------------------------
|
| Layout principal des pages d'authentification.
|
| Login.vue, Register.vue, ForgotPassword.vue...
| peuvent gérer leur propre design sans être enfermés
| dans un petit container.
|
*/

withDefaults(
    defineProps<{
        title?: string;
        description?: string;
    }>(),
    {
        title: '',
        description: '',
    },
);
</script>

<template>
    <div class="min-h-screen w-full bg-background">
        <slot />
    </div>
</template>