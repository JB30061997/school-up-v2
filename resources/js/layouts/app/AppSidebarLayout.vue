<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';

import SchoolSidebar from '@/components/layout/SchoolSidebar.vue';
import SchoolTopbar from '@/components/layout/SchoolTopbar.vue';

import { Toaster } from '@/components/ui/sonner';

import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <SchoolSidebar />

        <AppContent
            variant="sidebar"
            class="min-w-0 overflow-x-clip bg-background"
        >
            <SchoolTopbar
                :breadcrumbs="breadcrumbs"
            />

            <main
                class="min-h-[calc(100vh-4rem)] min-w-0"
            >
                <slot />
            </main>
        </AppContent>

        <Toaster />
    </AppShell>
</template>