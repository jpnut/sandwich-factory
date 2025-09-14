<template>
    <Layout>
        <div class="w-full mx-auto px-4 2xl:px-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">🥪 Sandwich Factory</h1>
                <p class="text-lg text-gray-600">Build your perfect sandwich</p>
            </div>

            <!-- Order Progress (shown when order is submitted) -->
            <div v-if="localOrderId || orderId" class="mb-8">
                <OrderProgress :order-id="(localOrderId || orderId)!" />
            </div>

            <!-- Order Form (hidden when order is submitted) -->
            <div v-if="!localOrderId && !orderId" class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
                <!-- Bread Selection -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Choose Your Bread</h2>
                    <div class="space-y-3">
                        <label 
                            v-for="bread in breadOptions" 
                            :key="bread.id"
                            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="{ 'border-blue-500 bg-blue-50': form.bread === bread.id }"
                        >
                            <div class="flex items-center space-x-3">
                                <input 
                                    type="radio" 
                                    :value="bread.id" 
                                    v-model="form.bread"
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                                >
                                <span class="font-medium text-gray-900">{{ bread.name }}</span>
                            </div>
                            <span class="text-sm font-mono text-gray-600">£{{ bread.price.toFixed(2) }}</span>
                        </label>
                    </div>
                </div>

                <!-- Fillings Selection -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Choose Your Fillings</h2>
                    <div class="space-y-3">
                        <label 
                            v-for="filling in fillingOptions" 
                            :key="filling.id"
                            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="{ 'border-blue-500 bg-blue-50': form.fillings.includes(filling.id) }"
                        >
                            <div class="flex items-center space-x-3">
                                <input 
                                    type="checkbox" 
                                    :value="filling.id" 
                                    v-model="form.fillings"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                >
                                <span class="font-medium text-gray-900">{{ filling.name }}</span>
                            </div>
                            <span class="text-sm font-mono text-gray-600">£{{ filling.price.toFixed(2) }}</span>
                        </label>
                    </div>
                </div>

                <!-- Condiments Selection -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Add Condiments</h2>
                    <div class="space-y-3">
                        <label 
                            v-for="condiment in condimentOptions" 
                            :key="condiment.id"
                            class="flex items-center justify-between p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors"
                            :class="{ 'border-blue-500 bg-blue-50': form.condiments.includes(condiment.id) }"
                        >
                            <div class="flex items-center space-x-3">
                                <input 
                                    type="checkbox" 
                                    :value="condiment.id" 
                                    v-model="form.condiments"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                >
                                <span class="font-medium text-gray-900">{{ condiment.name }}</span>
                            </div>
                            <span class="text-sm font-mono text-gray-600">£{{ condiment.price.toFixed(2) }}</span>
                        </label>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-8 h-max">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Order Summary</h2>
                    
                    <!-- Selected Bread -->
                    <div class="mb-4">
                        <h3 class="font-medium text-gray-900 mb-2">Bread:</h3>
                        <p class="text-gray-600">{{ selectedBreadName }}</p>
                    </div>

                    <!-- Selected Fillings -->
                    <div v-if="form.fillings.length > 0" class="mb-4">
                        <h3 class="font-medium text-gray-900 mb-2">Fillings:</h3>
                        <ul class="text-gray-600 space-y-1">
                            <li v-for="fillingId in form.fillings" :key="fillingId" class="flex justify-between">
                                <span>• {{ getFillingName(fillingId) }}</span>
                                <span class="font-mono">£{{ getFillingPrice(fillingId).toFixed(2) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Selected Condiments -->
                    <div v-if="form.condiments.length > 0" class="mb-4">
                        <h3 class="font-medium text-gray-900 mb-2">Condiments:</h3>
                        <ul class="text-gray-600 space-y-1">
                            <li v-for="condimentId in form.condiments" :key="condimentId" class="flex justify-between">
                                <span>• {{ getCondimentName(condimentId) }}</span>
                                <span class="font-mono">£{{ getCondimentPrice(condimentId).toFixed(2) }}</span>
                            </li>
                        </ul>
                    </div>

                    <hr class="my-6 border-gray-200">

                    <!-- Price Breakdown -->
                    <div class="space-y-2 mb-6">
                        <div class="flex justify-between text-sm">
                            <span>Bread:</span>
                            <span class="font-mono">£{{ selectedBreadPrice.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Fillings:</span>
                            <span class="font-mono">£{{ fillingsTotal.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Condiments:</span>
                            <span class="font-mono">£{{ condimentsTotal.toFixed(2) }}</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="flex justify-between items-center text-xl font-bold text-gray-900 border-t pt-4">
                        <span>Total:</span>
                        <span class="font-mono">£{{ totalPrice.toFixed(2) }}</span>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        @click="submitOrder"
                        :disabled="!isOrderValid || form.processing"
                        class="w-full mt-6 bg-blue-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ form.processing ? 'Submitting...' : 'Submit Order' }}
                    </button>

                    <!-- Surprise Me Button (under summary) -->
                    <button 
                        @click="generateRandomOrder"
                        class="cursor-pointer w-full mt-3 bg-gradient-to-r from-purple-500 to-pink-500 text-white font-semibold py-3 px-6 rounded-lg hover:from-purple-600 hover:to-pink-600 transition-all duration-200 transform hover:scale-105"
                    >
                        🎲 Surprise Me!
                    </button>
                </div>
            </div>

            <!-- New Order Button (shown when order is completed) -->
            <div v-if="localOrderId || orderId" class="text-center mt-8">
                <button 
                    @click="startNewOrder"
                    class="cursor-pointer bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Place Another Order
                </button>
            </div>
        </div>
    </Layout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import Layout from '@/components/Layout.vue'
import OrderProgress from '@/components/OrderProgress.vue'
import type { AppPageProps } from '@/types'

// Props for success/error messages
const props = defineProps<{
    success?: boolean
    error?: boolean
    message?: string
    order_id?: string
}>()

// Reactive order ID
const orderId = computed(() => props.order_id)

// Local state for showing progress immediately
const localOrderId = ref<string | null>(null)
const isProcessing = ref(false)

// Show progress immediately when form is submitted
const showImmediateProgress = () => {
    localOrderId.value = form.order_id
    isProcessing.value = true
}

// Bread options
const breadOptions = [
    { id: 'white', name: 'White Bread', price: 0.50 },
    { id: 'wheat', name: 'Wheat Bread', price: 0.75 },
    { id: 'sourdough', name: 'Sourdough', price: 1.00 },
    { id: 'rye', name: 'Rye Bread', price: 1.25 },
    { id: 'ciabatta', name: 'Ciabatta Roll', price: 1.50 },
    { id: 'baguette', name: 'Baguette', price: 1.75 },
]

// Filling options
const fillingOptions = [
    { id: 'turkey', name: 'Turkey', price: 3.50 },
    { id: 'ham', name: 'Ham', price: 3.00 },
    { id: 'roast-beef', name: 'Roast Beef', price: 4.00 },
    { id: 'chicken', name: 'Grilled Chicken', price: 3.75 },
    { id: 'tuna', name: 'Tuna Salad', price: 3.25 },
    { id: 'salmon', name: 'Smoked Salmon', price: 5.00 },
    { id: 'cheese', name: 'Cheese', price: 1.50 },
    { id: 'lettuce', name: 'Lettuce', price: 0.50 },
    { id: 'tomato', name: 'Tomato', price: 0.75 },
    { id: 'onion', name: 'Red Onion', price: 0.50 },
    { id: 'pickles', name: 'Pickles', price: 0.50 },
    { id: 'cucumber', name: 'Cucumber', price: 0.50 },
    { id: 'avocado', name: 'Avocado', price: 1.50 },
    { id: 'bacon', name: 'Bacon', price: 2.50 },
]

// Condiment options
const condimentOptions = [
    { id: 'mayo', name: 'Mayonnaise', price: 0.25 },
    { id: 'mustard', name: 'Mustard', price: 0.25 },
    { id: 'ketchup', name: 'Ketchup', price: 0.25 },
    { id: 'ranch', name: 'Ranch Dressing', price: 0.50 },
    { id: 'italian', name: 'Italian Dressing', price: 0.50 },
    { id: 'oil-vinegar', name: 'Oil & Vinegar', price: 0.60 },
    { id: 'hot-sauce', name: 'Hot Sauce', price: 0.25 },
    { id: 'bbq', name: 'BBQ Sauce', price: 0.50 },
    { id: 'honey-mustard', name: 'Honey Mustard', price: 0.50 },
    { id: 'garlic-aioli', name: 'Garlic Aioli', price: 0.75 },
]

// Form for submission
const form = useForm({
    bread: 'white',
    fillings: [] as string[],
    condiments: [] as string[],
    order_id: '',
})

// Generate a simple order ID
const generateOrderId = () => {
    return 'order_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
};

// Computed properties
const selectedBreadName = computed(() => {
    return breadOptions.find(b => b.id === form.bread)?.name || 'White Bread'
})

const selectedBreadPrice = computed(() => {
    return breadOptions.find(b => b.id === form.bread)?.price || 0
})

const fillingsTotal = computed(() => {
    return form.fillings.reduce((total, fillingId) => {
        const filling = fillingOptions.find(f => f.id === fillingId)
        return total + (filling?.price || 0)
    }, 0)
})

const condimentsTotal = computed(() => {
    return form.condiments.reduce((total, condimentId) => {
        const condiment = condimentOptions.find(c => c.id === condimentId)
        return total + (condiment?.price || 0)
    }, 0)
})

const totalPrice = computed(() => {
    return selectedBreadPrice.value + fillingsTotal.value + condimentsTotal.value
})

const isOrderValid = computed(() => {
    return form.bread && form.fillings.length > 0
})

// Helper functions
const getFillingName = (fillingId: string) => {
    return fillingOptions.find(f => f.id === fillingId)?.name || ''
}

const getFillingPrice = (fillingId: string) => {
    return fillingOptions.find(f => f.id === fillingId)?.price || 0
}

const getCondimentName = (condimentId: string) => {
    return condimentOptions.find(c => c.id === condimentId)?.name || ''
}

const getCondimentPrice = (condimentId: string) => {
    return condimentOptions.find(c => c.id === condimentId)?.price || 0
}

// Generate random order
const generateRandomOrder = () => {
    // Random bread
    const randomBread = breadOptions[Math.floor(Math.random() * breadOptions.length)].id
    
    // Random 2-4 fillings
    const shuffledFillings = [...fillingOptions].sort(() => 0.5 - Math.random())
    const numFillings = Math.floor(Math.random() * 3) + 2 // 2-4 fillings
    const randomFillings = shuffledFillings.slice(0, numFillings).map(f => f.id)
    
    // Random 1-3 condiments
    const shuffledCondiments = [...condimentOptions].sort(() => 0.5 - Math.random())
    const numCondiments = Math.floor(Math.random() * 3) + 1 // 1-3 condiments
    const randomCondiments = shuffledCondiments.slice(0, numCondiments).map(c => c.id)
    
    form.bread = randomBread
    form.fillings = randomFillings
    form.condiments = randomCondiments
}

// Submit order
const submitOrder = () => {
    if (!isOrderValid.value) return
    
    // Generate order ID for this submission
    form.order_id = generateOrderId();
    
    // Show progress immediately
    showImmediateProgress();
    
    form.post(route('sandwich.order.store'), {
        onSuccess: () => {
            // Keep showing progress with the confirmed order ID
            localOrderId.value = props.order_id || form.order_id
            isProcessing.value = false
        },
        onError: (errors) => {
            console.error('Order submission errors:', errors)
            // Hide progress on error
            localOrderId.value = null
            isProcessing.value = false
        },
    })
}

// Start new order
const startNewOrder = () => {
    // Reset form and local state
    form.reset()
    localOrderId.value = null
    isProcessing.value = false
    window.location.reload()
}
</script>
