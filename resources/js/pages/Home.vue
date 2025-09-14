<script setup lang="ts">
import Layout from '@/components/Layout.vue';
import { router } from '@inertiajs/vue3';

const goToSandwichOrder = () => {
    router.visit(route('sandwich.order'));
};
</script>

<template>
    <Layout>
        <div class="h-full flex items-center">
            <div class="w-full mb-8 text-center">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">🥪 Sandwich Factory</h1>
                <p class="text-lg text-gray-700 mb-6">Build Your Perfect Sandwich</p>
                
                <button 
                    @click="goToSandwichOrder"
                    class="bg-blue-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-blue-700 transition-colors text-lg"
                >
                    Start Building Your Sandwich
                </button>
            </div>
        </div>
    </Layout>
</template>
