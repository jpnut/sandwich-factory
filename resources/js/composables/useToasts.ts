import type { Ref } from 'vue';
import { ref } from 'vue';

export type ToastTypeType = 'error' | 'success' | 'info' | 'warning';

export type ToastType = {
  id: string;
  type: ToastTypeType;
  label: string;
}

export type UseToastsType = {
  dequeue: () => void;
  enqueue: (label: string, type?: ToastTypeType) => void;
  removeToast: (id: string) => void;
  queue: Ref<ToastType[]>;
};

const queue = ref<ToastType[]>([]);
let id = 0;
const MAX_TOASTS = 5; // Maximum number of toasts to show at once
const TOAST_TIMEOUT = 10000; // 10 seconds in milliseconds

const useToasts = (): UseToastsType => {
  const dequeue = (): void => {
    if (!queue.value.length) {
      return;
    }

    queue.value.shift();
  };

  const enqueue = (label: string, type: ToastTypeType = 'success'): void => {
    // Remove oldest toast if we're at the maximum limit
    if (queue.value.length >= MAX_TOASTS) {
      dequeue();
    }
    
    const toastId = `toasts-${Date.now()}`;
    
    queue.value.push({
      id: toastId,
      label,
      type,
    });

    // Set timeout to automatically remove the toast after 10 seconds
    setTimeout(() => {
      // Check if the toast still exists before removing it
      const toastIndex = queue.value.findIndex(toast => toast.id === toastId);
      if (toastIndex > -1) {
        queue.value.splice(toastIndex, 1);
      }
    }, TOAST_TIMEOUT);
  };

  const removeToast = (id: string): void => {
    const index = queue.value.findIndex(toast => toast.id === id);
    if (index > -1) {
      queue.value.splice(index, 1);
    }
  };

  return {
    enqueue,
    dequeue,
    removeToast,
    queue,
  };
};

export default useToasts;