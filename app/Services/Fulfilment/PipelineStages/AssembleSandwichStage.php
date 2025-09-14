<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\PipelineStages;

use App\Enums\FailureScenario;
use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Illuminate\Support\Facades\Log;
use Closure;

final class AssembleSandwichStage
{
    public function __invoke(OrderState $state, Closure $next): OrderState
    {
        Log::info("AssembleSandwichStage: Starting for order: {$state->orderData->orderId}");
        
        // Mark step as in progress
        $state->setCurrentStep('assembling_sandwich');
        $state->setStepStatus('assembling_sandwich', 'in_progress', 'Starting Sandwich Assembly...');
        
        // Simulate processing
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage('assembling_sandwich');
        usleep($delayMs * 1000);
        
        // Check for code bug scenario first
        if ($scenarioManager->getActiveScenario() === FailureScenario::CODE_BUG) {
            $this->simulateCodeBug($state);
        }
        
        // Check for failure
        if ($scenarioManager->shouldFailForStage('assembling_sandwich')) {
            Log::error("AssembleSandwichStage: Failing for order: {$state->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $state->setStepStatus('assembling_sandwich', 'error', 'Sandwich assembly failed - no workers available');
            

            throw new \RuntimeException('Sandwich assembly failed - no workers available');
        }
        
        // Mark as completed
        $state->setCurrentStep('assembling_sandwich');
        $state->setStepStatus('assembling_sandwich', 'completed', 'The sandwich has been expertly assembled with all requested ingredients');
        

        
        Log::info("AssembleSandwichStage: Completed for order: {$state->orderData->orderId}");
        
        // Call the next stage in the pipeline
        return $next($state);
    }

    /**
     * Simulates a division by zero bug in ingredient calculation logic
     * This represents a real bug that would need to be fixed in the code
     */
    private function simulateCodeBug(OrderState $state): void
    {
        Log::error("PrepareIngredientsStage: Code bug triggered for order: {$state->orderData->orderId}");
        
        // Mark step as failed before throwing exception
        $state->setStepStatus('preparing_ingredients', 'error', 'Critical bug detected - division by zero in ingredient calculation');
        
        // Simulate the bug: trying to divide by zero
        $ingredientCount = 0; // This would normally be calculated from the order
        $portionsPerIngredient = 5;
        
        // This line will cause a division by zero error
        $ingredientsPerPortion = $portionsPerIngredient / $ingredientCount;
        
        // This line will never be reached due to the division by zero
        throw new \DivisionByZeroError('Division by zero in ingredient calculation logic');
    }
}
