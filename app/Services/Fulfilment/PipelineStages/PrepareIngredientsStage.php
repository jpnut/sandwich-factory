<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\PipelineStages;

use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Illuminate\Support\Facades\Log;
use Closure;

final class PrepareIngredientsStage
{
    public function __invoke(OrderState $state, Closure $next): OrderState
    {
        Log::info("PrepareIngredientsStage: Starting for order: {$state->orderData->orderId}");
        
        // Mark step as in progress
        $state->setCurrentStep('preparing_ingredients');
        $state->setStepStatus('preparing_ingredients', 'in_progress', 'Starting Ingredient Preparation...');
        
        // Simulate processing
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage('preparing_ingredients');
        usleep($delayMs * 1000);
        
        // Check for other failure scenarios
        if ($scenarioManager->shouldFailForStage('preparing_ingredients')) {
            Log::error("PrepareIngredientsStage: Failing for order: {$state->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $state->setStepStatus('preparing_ingredients', 'error', 'Ingredient preparation failed - some ingredients are unavailable');
            throw new \RuntimeException('Ingredient preparation failed - some ingredients are unavailable');
        }
        
        // Mark as completed
        $state->setCurrentStep('preparing_ingredients');
        $state->setStepStatus('preparing_ingredients', 'completed', 'All ingredients have been prepared and are ready for assembly');
        
        Log::info("PrepareIngredientsStage: Completed for order: {$state->orderData->orderId}");
        
        // Call the next stage in the pipeline
        return $next($state);
    }
}
