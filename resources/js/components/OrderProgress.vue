<script setup lang="ts">
import { ref } from 'vue';
import { useEchoPublic } from '@laravel/echo-vue';
import { CheckCircle, Clock, AlertCircle, Loader2 } from 'lucide-vue-next';
import IngredientSubstitutionModal from './IngredientSubstitutionModal.vue';

interface OrderStep {
    id: string;
    name: string;
    description: string;
    status: 'pending' | 'in_progress' | 'completed' | 'error' | 'compensated' | 'compensation_pending';
    message?: string;
}

interface OrderStatus {
    order_id: string;
    step: string;
    status: string;
    message?: string;
    current_step: string;
    step_statuses: Record<string, string>;
    is_completed: boolean;
    error?: string;
    order_data: {
        bread: string;
        fillings: string[];
        condiments: string[];
    };
}

const props = defineProps<{
    orderId: string;
}>();

const orderSteps = ref<OrderStep[]>([
    {
        id: 'preparing_ingredients',
        name: 'Preparing Ingredients',
        description: 'Gathering and preparing all required ingredients',
        status: 'pending'
    },
    {
        id: 'toasting_bread',
        name: 'Toasting Bread',
        description: 'Toasting the bread to perfection',
        status: 'pending'
    },
    {
        id: 'assembling_sandwich',
        name: 'Assembling Sandwich',
        description: 'Expertly assembling your sandwich',
        status: 'pending'
    },
    {
        id: 'packaging',
        name: 'Packaging',
        description: 'Properly packaging your sandwich',
        status: 'pending'
    },
    {
        id: 'quality_check',
        name: 'Quality Check',
        description: 'Ensuring your sandwich meets our standards',
        status: 'pending'
    },
    {
        id: 'delivery',
        name: 'Delivery',
        description: 'Delivering your delicious sandwich',
        status: 'pending'
    }
]);

const currentStatus = ref<OrderStatus | null>(null);
const isCompleted = ref(false);
const error = ref<string | null>(null);

const getStepIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle;
        case 'in_progress':
            return Loader2;
        case 'error':
            return AlertCircle;
        case 'compensated':
            return CheckCircle;
        case 'compensation_pending':
            return Loader2;
        default:
            return Clock;
    }
};

const getStepColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'text-green-600';
        case 'in_progress':
            return 'text-blue-600 animate-spin';
        case 'error':
            return 'text-red-600';
        case 'compensated':
            return 'text-yellow-600';
        case 'compensation_pending':
            return 'text-orange-600 animate-spin';
        default:
            return 'text-gray-400';
    }
};

const getStepBgColor = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-green-50 border-green-200';
        case 'in_progress':
            return 'bg-blue-50 border-blue-200';
        case 'error':
            return 'bg-red-50 border-red-200';
        case 'compensated':
            return 'bg-yellow-50 border-yellow-200';
        case 'compensation_pending':
            return 'bg-orange-50 border-orange-200';
        default:
            return 'bg-gray-50 border-gray-200';
    }
};

// Listen for real-time updates
useEchoPublic(
    `order.${props.orderId}`,
    '.order-status-update',
    (data: OrderStatus) => {
        console.log('Received order status update:', data);
        currentStatus.value = data;
        
        // Update step statuses
        orderSteps.value.forEach(step => {
            const stepStatus = data.step_statuses[step.id];
            if (stepStatus) {
                step.status = stepStatus as any;
                
                // Update message if this is the current step
                if (data.step === step.id && data.message) {
                    step.message = data.message;
                }
            }
        });
        
        // Check if completed
        if (data.is_completed) {
            isCompleted.value = true;
        }
        
        // Check for errors
        if (data.error) {
            error.value = data.error;
        }
    }
);
</script>

<template>
    <div class="max-w-2xl mx-auto">
        <!-- Order Header -->
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Order Progress</h2>
            <p class="text-gray-600">Order ID: {{ orderId }}</p>
        </div>

        <!-- Error Display -->
        <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <AlertCircle class="h-5 w-5 text-red-400 flex-shrink-0" />
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">Processing Error</p>
                    <p class="text-sm text-red-700 mt-1">{{ error }}</p>
                </div>
            </div>
        </div>

        <!-- Completion Message -->
        <div v-if="isCompleted" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex">
                <CheckCircle class="h-5 w-5 text-green-400 flex-shrink-0" />
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">Order Complete!</p>
                    <p class="text-sm text-green-700 mt-1">Your delicious sandwich has been successfully delivered!</p>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="space-y-4">
            <div 
                v-for="(step, index) in orderSteps" 
                :key="step.id"
                class="relative"
            >
                <!-- Step Card -->
                <div 
                    class="p-4 border rounded-lg transition-all duration-300"
                    :class="getStepBgColor(step.status)"
                >
                    <div class="flex items-start space-x-4">
                        <!-- Step Icon -->
                        <div class="flex-shrink-0">
                            <component 
                                :is="getStepIcon(step.status)" 
                                class="h-6 w-6"
                                :class="getStepColor(step.status)"
                            />
                        </div>

                        <!-- Step Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-medium text-gray-900">
                                    {{ step.name }}
                                </h3>
                                <span 
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                    :class="{
                                        'bg-green-100 text-green-800': step.status === 'completed',
                                        'bg-blue-100 text-blue-800': step.status === 'in_progress',
                                        'bg-red-100 text-red-800': step.status === 'error',
                                        'bg-gray-100 text-gray-800': step.status === 'pending',
                                        'bg-yellow-100 text-yellow-800': step.status === 'compensated',
                                        'bg-orange-100 text-orange-800': step.status === 'compensation_pending'
                                    }"
                                >
                                    {{ step.status.replace('_', ' ') }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">{{ step.description }}</p>
                            <p v-if="step.message" class="text-sm text-gray-700 mt-2 italic">
                                "{{ step.message }}"
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Connector Line -->
                <div 
                    v-if="index < orderSteps.length - 1"
                    class="absolute left-7 top-12 w-0.5 h-8 bg-gray-200"
                    :class="{
                        'bg-green-200': step.status === 'completed',
                        'bg-blue-200': step.status === 'in_progress',
                        'bg-yellow-200': step.status === 'compensated',
                        'bg-orange-200': step.status === 'compensation_pending'
                    }"
                ></div>
            </div>
        </div>

        <!-- Order Details -->
        <div v-if="currentStatus?.order_data" class="mt-8 bg-gray-50 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-900 mb-3">Order Details</h3>
            <div class="space-y-2 text-sm text-gray-600">
                <div>
                    <span class="font-medium">Bread:</span> 
                    {{ currentStatus.order_data.bread.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}
                </div>
                <div v-if="currentStatus.order_data.fillings.length > 0">
                    <span class="font-medium">Fillings:</span> 
                    {{ currentStatus.order_data.fillings.map(f => f.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())).join(', ') }}
                </div>
                <div v-if="currentStatus.order_data.condiments.length > 0">
                    <span class="font-medium">Condiments:</span> 
                    {{ currentStatus.order_data.condiments.map(c => c.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())).join(', ') }}
                </div>
            </div>
        </div>
        
        <!-- Ingredient Substitution Modal -->
        <IngredientSubstitutionModal :order-id="orderId" />
    </div>
</template>
