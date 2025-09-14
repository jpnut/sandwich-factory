<template>
    <Dialog :open="showModal" @update:open="closeModal">
        <DialogContent class="sm:max-w-md bg-white">
            <DialogHeader>
                <DialogTitle>Ingredient Substitution Required</DialogTitle>
                <DialogDescription>
                    The following ingredient is currently unavailable and needs to be substituted:
                </DialogDescription>
            </DialogHeader>
            
            <div class="space-y-4">
                <!-- Ingredient Alert -->
                <Alert variant="destructive">
                    <AlertTriangle class="h-4 w-4" />
                    <AlertTitle>{{ form.original_ingredient }}</AlertTitle>
                    <AlertDescription>
                        Type: {{ form.ingredient_type }}
                    </AlertDescription>
                </Alert>
                
                <!-- Substitution Options -->
                <div v-if="availableSubstitutions.length > 0" class="space-y-3">
                    <Label>Available Substitutions:</Label>
                    <RadioGroup v-model="form.substituted_ingredient" class="space-y-2">
                        <div
                            v-for="substitution in availableSubstitutions"
                            :key="substitution"
                            class="flex items-center space-x-2"
                        >
                            <RadioGroupItem :value="substitution" :id="substitution" />
                            <Label :for="substitution" class="text-sm capitalize cursor-pointer">
                                {{ substitution.replace('_', ' ') }}
                            </Label>
                        </div>
                    </RadioGroup>
                </div>
            </div>
            
            <DialogFooter class="flex justify-end space-x-2">
                <Button 
                    @click="submitSubstitution"
                    :disabled="!canSubmit || form.processing"
                    :loading="form.processing"
                    class="cursor-pointer bg-blue-600 text-white font-semibold p-4 rounded-lg hover:bg-blue-700 transition-colors"
                >
                    Confirm Substitution
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { AlertTriangle } from 'lucide-vue-next';
import { useForm } from '@inertiajs/vue3';
import { useEchoPublic } from '@laravel/echo-vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';

interface IngredientSubstitutionRequest {
    order_id: string;
    missing_ingredient: string;
    ingredient_type: string;
    available_substitutions: string[];
}

const props = defineProps<{
    orderId: string;
}>();

const showModal = ref(false);
const availableSubstitutions = ref<string[]>([]);

const form = useForm<{
    order_id: string;
    original_ingredient: string | null;
    substituted_ingredient: string | null;
    ingredient_type: string | null;
}>({
    order_id: props.orderId,
    original_ingredient: null,
    substituted_ingredient: null,
    ingredient_type: null,
});

const canSubmit = computed(() => {
    return form.original_ingredient 
        && form.original_ingredient.length > 0 
        && form.substituted_ingredient 
        && form.substituted_ingredient.length > 0
        && form.ingredient_type
        && form.ingredient_type.length > 0;
});

// Listen for ingredient substitution requests
useEchoPublic(
    `order.${props.orderId}`,
    '.ingredient-substitution-request',
    (data: IngredientSubstitutionRequest) => {
        console.log('Received ingredient substitution request:', data);
        showSubstitutionModal(data);
    }
);

const showSubstitutionModal = (data: IngredientSubstitutionRequest) => {
    form.original_ingredient = data.missing_ingredient;
    form.ingredient_type = data.ingredient_type;
    form.substituted_ingredient = '';
    
    availableSubstitutions.value = data.available_substitutions;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitSubstitution = async () => {
    if (!canSubmit.value) return;
    
    form.post(route('ingredient.substitution.store'), {
        onSuccess: () => {
            closeModal();
        },
        onError: (errors) => {
            console.error('Failed to submit substitution:', errors);
        },
    });
};
</script>
