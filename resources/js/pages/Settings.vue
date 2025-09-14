<template>
    <Layout class="max-w-7xl">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-1">Settings</h1>
            <p class="text-gray-600 text-sm">Configure demo options like failure scenarios and backend implementation.</p>
        </div>

        <!-- Current Scenario Overview - Above the fold -->
        <Card v-if="activeScenario" class="mb-6">
            <CardHeader class="pb-3">
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="text-lg">Current Scenario: {{ activeScenario.title }}</CardTitle>
                        <CardDescription class="text-sm mt-1">{{ activeScenario.description }}</CardDescription>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        <span 
                            v-for="stage in activeScenario.affectedStages" 
                            :key="stage"
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800"
                        >
                            {{ formatStageName(stage) }}
                        </span>
                    </div>
                </div>
            </CardHeader>
            <CardContent class="pt-0">
                <div class="text-sm text-muted-foreground">
                    <span class="font-medium">Expected Behavior:</span>
                    <span v-if="activeScenario.value === 'none'"> All stages will complete normally</span>
                    <span v-else-if="activeScenario.value === 'random_failures'"> 30% chance of failure at affected stages</span>
                    <span v-else-if="activeScenario.value === 'cascading_failures'"> Multiple stages will fail in sequence</span>
                    <span v-else> Specific stages will fail consistently</span>
                    <span>. Delays will be applied to simulate realistic processing times.</span>
                </div>
            </CardContent>
        </Card>

        <!-- Ingredient Management -->
        <Card class="mb-6">
            <CardHeader class="pb-3">
                <div class="flex items-center justify-between">
                    <div>
                        <CardTitle class="text-lg">Ingredient Availability</CardTitle>
                        <CardDescription class="text-sm">
                            Control which ingredients are available for orders. Unavailable ingredients will trigger substitution requests.
                        </CardDescription>
                    </div>
                    <Button 
                        @click="resetAllIngredients"
                        variant="outline"
                        size="sm"
                        :disabled="resetForm.processing"
                    >
                        {{ resetForm.processing ? 'Resetting...' : 'Reset All to Available' }}
                    </Button>
                </div>
            </CardHeader>
            <CardContent>
                <div class="grid gap-6 md:grid-cols-3">
                    <!-- Bread -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Bread</h3>
                        <div class="space-y-2">
                            <div 
                                v-for="ingredient in ingredients.bread" 
                                :key="ingredient.id"
                                class="flex items-center justify-between p-2 rounded border"
                                :class="ingredient.available ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
                            >
                                <span class="text-sm font-medium">{{ ingredient.name }}</span>
                                <Switch 
                                    :model-value="ingredient.available"
                                    @update:model-value="(value) => updateIngredientAvailability('bread', ingredient.id, value)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Fillings -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Fillings</h3>
                        <div class="space-y-2">
                            <div 
                                v-for="ingredient in ingredients.fillings" 
                                :key="ingredient.id"
                                class="flex items-center justify-between p-2 rounded border"
                                :class="ingredient.available ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
                            >
                                <span class="text-sm font-medium">{{ ingredient.name }}</span>
                                <Switch 
                                    :model-value="ingredient.available"
                                    @update:model-value="(value) => updateIngredientAvailability('fillings', ingredient.id, value)"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Condiments -->
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Condiments</h3>
                        <div class="space-y-2">
                            <div 
                                v-for="ingredient in ingredients.condiments" 
                                :key="ingredient.id"
                                class="flex items-center justify-between p-2 rounded border"
                                :class="ingredient.available ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50'"
                            >
                                <span class="text-sm font-medium">{{ ingredient.name }}</span>
                                <Switch 
                                    :model-value="ingredient.available"
                                    @update:model-value="(value) => updateIngredientAvailability('condiments', ingredient.id, value)"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Scenario Selection -->
        <Card>
            <CardHeader class="pb-3">
                <CardTitle class="text-lg">Select Scenario</CardTitle>
                <CardDescription class="text-sm">
                    Choose a failure scenario to see how it affects different stages of the sandwich fulfillment process.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <RadioGroup v-model="form.activeScenario">
                    <div class="grid gap-3">
                        <div v-for="scenario in scenarios" :key="scenario.value" class="flex items-start space-x-3 p-3 rounded-lg border hover:bg-gray-50 transition-colors">
                            <RadioGroupItem 
                                :value="scenario.value" 
                                :id="scenario.value"
                                class="mt-1"
                            />
                            <Label :for="scenario.value" class="flex-1 cursor-pointer">
                                <div class="flex flex-col">
                                    <span class="font-medium text-sm">{{ scenario.title }}</span>
                                    <span class="text-xs text-muted-foreground mt-1">{{ scenario.description }}</span>
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        <span 
                                            v-for="stage in scenario.affectedStages" 
                                            :key="stage"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                                        >
                                            {{ formatStageName(stage) }}
                                        </span>
                                    </div>
                                </div>
                            </Label>
                        </div>
                    </div>
                </RadioGroup>
            </CardContent>
        </Card>
        
        <!-- Delay Configuration -->
        <Card class="mt-6">
            <CardHeader class="pb-3">
                <CardTitle class="text-lg">Processing Delay</CardTitle>
                <CardDescription class="text-sm">
                    Configure the base delay for all processing steps. This simulates realistic processing time with ±20% random variation.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <Label for="delay-ms" class="text-sm font-medium">Delay (milliseconds)</Label>
                            <span class="text-sm text-gray-500">
                                {{ (form.delayMs / 1000).toFixed(1) }}s
                                <span v-if="isUpdatingDelay" class="ml-1 text-blue-500">•</span>
                            </span>
                        </div>
                        <Slider
                            v-model="delaySliderValue"
                            :min="1000"
                            :max="10000"
                            :step="100"
                            class="w-full"
                        />
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>1s</span>
                            <span>10s</span>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500">
                        Current setting: {{ form.delayMs }}ms ({{ (form.delayMs / 1000).toFixed(1) }}s)
                    </p>
                </div>
            </CardContent>
        </Card>

        <!-- Implementation Selection -->
        <Card class="mt-6">
            <CardHeader class="pb-3">
                <CardTitle class="text-lg">Backend Implementation</CardTitle>
                <CardDescription class="text-sm">
                    Choose between Traditional (pipeline) and Temporal implementations.
                </CardDescription>
            </CardHeader>
            <CardContent>
                <RadioGroup v-model="form.activeImplementation">
                    <div class="grid gap-3">
                        <div v-for="impl in implementations" :key="impl.value" class="flex items-start space-x-3 p-3 rounded-lg border hover:bg-gray-50 transition-colors">
                            <RadioGroupItem 
                                :value="impl.value" 
                                :id="`impl-${impl.value}`"
                                class="mt-1"
                            />
                            <Label :for="`impl-${impl.value}`" class="flex-1 cursor-pointer">
                                <div class="flex flex-col">
                                    <span class="font-medium text-sm">{{ impl.title }}</span>
                                    <span class="text-xs text-muted-foreground mt-1">{{ impl.description }}</span>
                                </div>
                            </Label>
                        </div>
                    </div>
                </RadioGroup>
            </CardContent>
        </Card>
    </Layout>
</template>

<script setup lang="ts">
import Layout from '@/components/Layout.vue'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import { Switch } from '@/components/ui/switch'
import { Slider } from '@/components/ui/slider'
import { useForm } from '@inertiajs/vue3'
import { computed, watch, ref } from 'vue'
import useToasts from '@/composables/useToasts'

interface Scenario {
    value: string
    title: string
    description: string
    affectedStages: string[]
    delayMs: number
}

interface ImplementationOption {
    value: 'traditional' | 'temporal'
    title: string
    description: string
}

interface Ingredient {
    id: string
    name: string
    available: boolean
}

interface Ingredients {
    bread: Ingredient[]
    fillings: Ingredient[]
    condiments: Ingredient[]
}

const props = defineProps<{
    scenarios: Scenario[]
    activeScenario: string
    implementations: ImplementationOption[]
    activeImplementation: 'traditional' | 'temporal'
    ingredients: Ingredients
    delayMs: number
}>();

const { enqueue } = useToasts();

const form = useForm<{
    activeScenario: string
    activeImplementation: 'traditional' | 'temporal'
    ingredientAvailability: Record<string, Record<string, { available: boolean }>>
    delayMs: number
}>({
    activeScenario: props.activeScenario,
    activeImplementation: props.activeImplementation,
    ingredientAvailability: {},
    delayMs: props.delayMs,
});

const resetForm = useForm({});

const activeScenario = computed(() => 
    props.scenarios.find(s => s.value === form.activeScenario) || 
    props.scenarios.find(s => s.value === props.activeScenario)
);

// Slider value computed property for the delay slider
const delaySliderValue = computed({
    get: () => [form.delayMs],
    set: (value: number[]) => {
        if (value.length > 0) {
            form.delayMs = value[0];
        }
    }
});

// Debounced delay update
let delayUpdateTimeout: number | null = null;
const isUpdatingDelay = ref(false);

const selectScenario = (scenarioValue: string) => {
    form.activeScenario = scenarioValue;
    
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            enqueue('Scenario updated successfully!', 'success');
        },
        onError: (errors) => {
            if (errors.activeScenario) {
                enqueue(errors.activeScenario, 'error');
            } else {
                enqueue('Failed to update scenario. Please try again.', 'error');
            }
        },
    });
};

const updateIngredientAvailability = (type: string, ingredientId: string, available: boolean) => {
    // Update the form data
    if (!form.ingredientAvailability[type]) {
        form.ingredientAvailability[type] = {};
    }
    form.ingredientAvailability[type][ingredientId] = { available };
    
    // Submit the update
    form.post(route('settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            enqueue(`${type} availability updated successfully!`, 'success');
        },
        onError: (errors) => {
            enqueue('Failed to update ingredient availability. Please try again.', 'error');
        },
    });
};

const resetAllIngredients = () => {
    resetForm.post(route('settings.reset-ingredients'), {
        preserveScroll: true,
        onSuccess: () => {
            enqueue('All ingredients reset to available!', 'success');
        },
        onError: () => {
            enqueue('Failed to reset ingredients. Please try again.', 'error');
        },
    });
};

// Watch for changes in props.activeScenario and update form accordingly
watch(() => props.activeScenario, (newValue) => {
    if (newValue && newValue !== form.activeScenario) {
        form.activeScenario = newValue;
    }
}, { immediate: true });

// Watch for changes in form.activeScenario and trigger the selection
watch(() => form.activeScenario, (newValue, oldValue) => {
    if (newValue && newValue !== oldValue) {
        selectScenario(newValue);
    }
});

// Sync implementation prop to form
watch(() => props.activeImplementation, (newValue) => {
    if (newValue && newValue !== form.activeImplementation) {
        form.activeImplementation = newValue;
    }
}, { immediate: true });

// Persist implementation change
watch(() => form.activeImplementation, (newValue, oldValue) => {
    if (newValue && newValue !== oldValue) {
        form.post(route('settings.update'), {
            preserveScroll: true,
            onSuccess: () => enqueue('Implementation updated successfully!', 'success'),
            onError: (errors) => {
                if ((errors as any).activeImplementation) {
                    enqueue((errors as any).activeImplementation, 'error');
                } else {
                    enqueue('Failed to update implementation. Please try again.', 'error');
                }
            },
        });
    }
});

// Persist delay change with debouncing
watch(() => form.delayMs, (newValue, oldValue) => {
    if (newValue && newValue !== oldValue && newValue >= 1000 && newValue <= 10000) {
        // Clear existing timeout
        if (delayUpdateTimeout) {
            clearTimeout(delayUpdateTimeout);
        }
        
        // Set updating state
        isUpdatingDelay.value = true;
        
        // Debounce the update by 500ms
        delayUpdateTimeout = setTimeout(() => {
            form.post(route('settings.update'), {
                preserveScroll: true,
                onSuccess: () => {
                    enqueue('Delay setting updated successfully!', 'success');
                    isUpdatingDelay.value = false;
                },
                onError: (errors) => {
                    if ((errors as any).delayMs) {
                        enqueue((errors as any).delayMs, 'error');
                    } else {
                        enqueue('Failed to update delay setting. Please try again.', 'error');
                    }
                    isUpdatingDelay.value = false;
                },
            });
        }, 500);
    }
});

const formatStageName = (stage: string): string => {
    return stage
        .split('_')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>