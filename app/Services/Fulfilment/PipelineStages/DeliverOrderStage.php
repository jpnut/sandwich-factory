<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\PipelineStages;


use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Illuminate\Support\Facades\Log;
use Closure;

final class DeliverOrderStage
{
    public function __invoke(OrderState $state, Closure $next): OrderState
    {
        Log::info("DeliverOrderStage: Starting for order: {$state->orderData->orderId}");
        
        // Mark step as in progress
        $state->setCurrentStep('delivery');
        $state->setStepStatus('delivery', 'in_progress', 'Starting Order Delivery...');
        
        // Simulate processing
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage('delivery');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($scenarioManager->shouldFailForStage('delivery')) {
            Log::error("DeliverOrderStage: Failing for order: {$state->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $state->setStepStatus('delivery', 'error', 'Delivery failed - network connectivity issues');
            

            throw new \RuntimeException('Delivery failed - network connectivity issues');
        }
        
        // Mark as completed
        $state->setCurrentStep('delivery');
        $state->setStepStatus('delivery', 'completed', 'Your delicious sandwich has been successfully delivered!');
        $state->setCompleted(true);
        

        
        Log::info("DeliverOrderStage: Completed for order: {$state->orderData->orderId}");
        
        // Call the next stage in the pipeline (this will be the final return)
        return $next($state);
    }
}
