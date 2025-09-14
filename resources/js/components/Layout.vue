<script setup lang="ts">
import Navigation from './Navigation.vue';
import ToastGroup from './ToastGroup.vue';
import useToasts from '@/composables/useToasts';

const { queue, removeToast } = useToasts();

const handleRemoveToast = (id: string) => {
    removeToast(id);
};
</script>

<template>
    <div class="min-h-screen p-4 flex flex-col">
        <div class="mx-auto w-full">
            <Navigation class="mb-6" />
        </div>

        <main class="flex-1 mx-auto w-full">
            <slot></slot>
        </main>

        <!-- Toast Notifications -->
        <div class="fixed top-4 right-4 z-50 space-y-2">
            <ToastGroup :toasts="queue" @remove="handleRemoveToast" />
        </div>
    </div>
</template>