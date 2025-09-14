<template>
    <TransitionGroup 
        name="toast" 
        tag="div" 
        class="space-y-2"
    >
        <div 
            v-for="(toast, index) in toasts" 
            :key="toast.id"
            class="flex items-center w-full max-w-sm p-4 text-gray-500 bg-white rounded-lg shadow-lg border border-gray-200"
            :class="{
                'border-green-200 bg-green-50': toast.type === 'success',
                'border-red-200 bg-red-50': toast.type === 'error',
                'border-yellow-200 bg-yellow-50': toast.type === 'warning',
                'border-blue-200 bg-blue-50': toast.type === 'info'
            }"
            :style="{
                transform: `translateX(${index * 4}px)`,
                zIndex: 1000 - index
            }"
        >
            <div class="flex-shrink-0">
                <CheckCircle v-if="toast.type === 'success'" class="w-5 h-5 text-green-600" />
                <XCircle v-else-if="toast.type === 'error'" class="w-5 h-5 text-red-600" />
                <AlertTriangle v-else-if="toast.type === 'warning'" class="w-5 h-5 text-yellow-600" />
                <Info v-else class="w-5 h-5 text-blue-600" />
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium" :class="{
                    'text-green-800': toast.type === 'success',
                    'text-red-800': toast.type === 'error',
                    'text-yellow-800': toast.type === 'warning',
                    'text-blue-800': toast.type === 'info'
                }">
                    {{ toast.label }}
                </p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button
                    @click="removeToast(toast.id)"
                    class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                >
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>
    </TransitionGroup>
</template>

<script lang="ts" setup>
import { ToastType } from '@/composables/useToasts';
import { CheckCircle, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next';
import { watch, onUnmounted } from 'vue';

const props = defineProps<{
    toasts: ToastType[];
}>();

const emit = defineEmits<{
    remove: [id: string];
}>();

const timeouts = new Map<string, number>();

const removeToast = (id: string) => {
    // Clear the timeout if it exists
    const timeout = timeouts.get(id);
    if (timeout) {
        clearTimeout(timeout);
        timeouts.delete(id);
    }
    emit('remove', id);
};

// Auto-dismiss toasts after 10 seconds
watch(() => props.toasts, (newToasts, oldToasts) => {
    // Clear timeouts for toasts that are no longer in the list
    oldToasts?.forEach(toast => {
        if (!newToasts.find(t => t.id === toast.id)) {
            const timeout = timeouts.get(toast.id);
            if (timeout) {
                clearTimeout(timeout);
                timeouts.delete(toast.id);
            }
        }
    });

    // Set timeouts for new toasts
    newToasts.forEach(toast => {
        if (!timeouts.has(toast.id)) {
            const timeout = setTimeout(() => {
                removeToast(toast.id);
            }, 10000);
            timeouts.set(toast.id, timeout);
        }
    });
}, { deep: true });

// Cleanup timeouts on component unmount
onUnmounted(() => {
    timeouts.forEach(timeout => clearTimeout(timeout));
    timeouts.clear();
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}

.toast-move {
    transition: transform 0.3s ease;
}
</style>