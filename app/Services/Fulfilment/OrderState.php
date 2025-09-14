<?php

declare(strict_types=1);

namespace App\Services\Fulfilment;

use App\Data\OrderData;
use App\Events\OrderStatusUpdate;
use Illuminate\Support\Facades\Log;

final class OrderState
{
    private(set) string $currentStep = 'pending';
    private(set) array $stepStatuses = [];
    private(set) ?string $error = null;
    private(set) bool $isCompleted = false;
    private(set) array $missingIngredients = [];
    private(set) array $ingredientSubstitutions = [];

    public function __construct(
        public OrderData $orderData,
    ) {
        // Initialize all step statuses
        $this->stepStatuses = [
            'preparing_ingredients' => 'pending',
            'toasting_bread' => 'pending',
            'assembling_sandwich' => 'pending',
            'packaging' => 'pending',
            'quality_check' => 'pending',
            'delivery' => 'pending',
        ];
    }

    public function getCurrentStep(): string
    {
        return $this->currentStep;
    }

    public function setCurrentStep(string $step): void
    {
        $this->currentStep = $step;
    }

    public function getStepStatus(string $step): string
    {
        return $this->stepStatuses[$step] ?? 'unknown';
    }

    public function setStepStatus(string $step, string $status, ?string $message = null): void
    {
        $this->stepStatuses[$step] = $status;
        
        // Log the status update for debugging
        Log::info("Order {$this->orderData->orderId} step {$step} status: {$status}", [
            'order_id' => $this->orderData->orderId,
            'step' => $step,
            'status' => $status,
            'message' => $message,
        ]);
        
        // Broadcast the status update
        event(new OrderStatusUpdate($this, $step, $status, $message));
    }

    /**
     * Set a step to compensation pending status with a delay before actual compensation
     */
    public function setCompensationPending(string $step, ?string $message = null): void
    {
        $this->setStepStatus($step, 'compensation_pending', $message ?? 'Compensation pending...');
    }

    public function getAllStepStatuses(): array
    {
        return $this->stepStatuses;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
        
        // Broadcast error status
        event(new OrderStatusUpdate($this, 'error', 'error', $error));
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setCompleted(bool $completed): void
    {
        $this->isCompleted = $completed;
        
        if ($completed) {
            // Broadcast completion status
            event(new OrderStatusUpdate($this, 'completed', 'completed', 'Order processing completed successfully'));
        }
    }

    public function setMissingIngredients(array $missingIngredients): void
    {
        $this->missingIngredients = $missingIngredients;
    }

    public function isMissingIngredients(): bool
    {
        return !empty($this->missingIngredients);
    }

    public function addIngredientSubstitution(string $originalIngredient, string $substitutedIngredient, string $ingredientType): void
    {
        $this->ingredientSubstitutions[] = [
            'original' => $originalIngredient,
            'substituted' => $substitutedIngredient,
            'type' => $ingredientType,
        ];
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->orderData->orderId,
            'current_step' => $this->currentStep,
            'step_statuses' => $this->stepStatuses,
            'error' => $this->error,
            'is_completed' => $this->isCompleted,
            'ingredient_substitutions' => $this->ingredientSubstitutions,
            'order_data' => [
                'bread' => $this->orderData->bread,
                'fillings' => $this->orderData->fillings,
                'condiments' => $this->orderData->condiments,
            ],
        ];
    }
}
