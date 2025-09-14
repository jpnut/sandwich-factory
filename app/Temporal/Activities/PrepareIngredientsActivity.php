<?php

namespace App\Temporal\Activities;

use App\Events\IngredientSubstitutionRequest;
use App\Services\Fulfilment\OrderState;
use App\Services\IngredientService;
use App\Services\ScenarioManager;
use Psr\Log\LoggerInterface;
use Temporal\Activity\ActivityInterface;
use Temporal\Activity\ActivityMethod;

#[ActivityInterface]
class PrepareIngredientsActivity
{
    public function __construct(
        private ScenarioManager $scenarioManager,
        private IngredientService $ingredientService,
        private LoggerInterface $logger,
    )
    {
        //
    }

    #[ActivityMethod]
    public function prepareIngredients(OrderState $orderState): OrderState
    {
        $this->logger->info("PrepareIngredientsStage: Starting for order: {$orderState->orderData->orderId}");

        $orderState->setMissingIngredients([]);
        
        // Mark step as in progress
        $orderState->setCurrentStep('preparing_ingredients');
        $orderState->setStepStatus('preparing_ingredients', 'in_progress', 'Checking ingredient availability...');
        
        // Check ingredient availability
        $missingIngredients = $this->checkAllIngredientsAvailability($orderState);
        
        if (!empty($missingIngredients)) {
            $this->logger->warning("PrepareIngredientsStage: Missing ingredients for order: {$orderState->orderData->orderId}", [
                'missing_ingredients' => $missingIngredients
            ]);
            
            // Mark step as waiting for substitution
            $orderState->setStepStatus('preparing_ingredients', 'error', 'Waiting for ingredient substitutions...');
            
            $orderState->setMissingIngredients($missingIngredients);
            return $orderState;
        }
        
        // Simulate processing
        $delayMs = $this->scenarioManager->getDelayForStage('preparing_ingredients');
        usleep($delayMs * 1000);
        
        // Check for other failure scenarios
        if ($this->scenarioManager->shouldFailForStage('preparing_ingredients')) {
            $this->logger->error("PrepareIngredientsStage: Failing for order: {$orderState->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $orderState->setStepStatus('preparing_ingredients', 'error', 'Ingredient preparation failed - some ingredients are unavailable');
            throw new \RuntimeException('Ingredient preparation failed - some ingredients are unavailable');
        }
        
        // Mark as completed
        $orderState->setCurrentStep('preparing_ingredients');
        $orderState->setStepStatus('preparing_ingredients', 'completed', 'All ingredients have been prepared and are ready for assembly');
        
        $this->logger->info("PrepareIngredientsStage: Completed for order: {$orderState->orderData->orderId}");
        
        return $orderState;
    }

    #[ActivityMethod]
    public function compensatePrepareIngredients(OrderState $orderState): OrderState
    {
        $this->logger->info("Starting compensation for prepare ingredients for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'prepare_ingredients',
            'action' => 'compensation_started',
        ]);

        // First, set the status to compensation pending
        $orderState->setCompensationPending('preparing_ingredients', 'Preparing to compensate ingredient preparation...');

        // Simulate compensation
        $delayMs = $this->scenarioManager->getDelayForStage('preparing_ingredients');
        usleep($delayMs * 1000);

        $this->logger->info("Performing actual compensation for prepare ingredients for order {$orderState->orderData->orderId}", [
            'order_id' => $orderState->orderData->orderId,
            'step' => 'prepare_ingredients',
            'action' => 'compensation_executing',
        ]);

        // Reset the step status to indicate compensation completed
        $orderState->setStepStatus('preparing_ingredients', 'compensated', 'Ingredients preparation was compensated');

        // Here you would implement the actual compensation logic
        // For example:
        // - Return ingredients to inventory
        // - Reset any allocated resources
        // - Cancel any pending ingredient orders
        // - Update inventory tracking

        $this->logger->info("Successfully compensated prepare ingredients for order {$orderState->orderData->orderId}");

        return $orderState;
    }

    private function checkAllIngredientsAvailability(OrderState $orderState): array
    {
        // Check bread availability
        if (!$this->ingredientService->isIngredientAvailable($orderState->orderData->bread, 'bread')) {
            // Broadcast substitution request for bread
            $availableSubstitutions = $this->ingredientService->getAvailableSubstitutions($orderState->orderData->bread, 'bread');
            event(new IngredientSubstitutionRequest(
                $orderState,
                $orderState->orderData->bread,
                'bread',
                $availableSubstitutions
            ));

            return [$orderState->orderData->bread];
        }
        
        // Check fillings availability
        foreach ($orderState->orderData->fillings as $filling) {
            if (!$this->ingredientService->isIngredientAvailable($filling, 'fillings')) {
                // Broadcast substitution request for filling
                $availableSubstitutions = $this->ingredientService->getAvailableSubstitutions($filling, 'fillings');
                event(new IngredientSubstitutionRequest(
                    $orderState,
                    $filling,
                    'fillings',
                    $availableSubstitutions
                ));

                return [$filling];
            }
        }
        
        // Check condiments availability
        foreach ($orderState->orderData->condiments as $condiment) {
            if (!$this->ingredientService->isIngredientAvailable($condiment, 'condiments')) {
                // Broadcast substitution request for condiment
                $availableSubstitutions = $this->ingredientService->getAvailableSubstitutions($condiment, 'condiments');
                event(new IngredientSubstitutionRequest(
                    $orderState,
                    $condiment,
                    'condiments',
                    $availableSubstitutions
                ));

                return [$condiment];
            }
        }
        
        return [];
    }
}
