<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\PipelineStages;


use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Illuminate\Support\Facades\Log;
use Closure;

final class ToastBreadStage
{
    public function __invoke(OrderState $state, Closure $next): OrderState
    {
        Log::info("ToastBreadStage: Starting for order: {$state->orderData->orderId}");
        
        // Mark step as in progress
        $state->setCurrentStep('toasting_bread');
        $state->setStepStatus('toasting_bread', 'in_progress', 'Starting Bread Toasting...');
        
        // Simulate processing
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage('toasting_bread');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($scenarioManager->shouldFailForStage('toasting_bread')) {
            Log::error("ToastBreadStage: Failing for order: {$state->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $state->setStepStatus('toasting_bread', 'error', 'Bread toasting failed - toaster equipment malfunction');
            

            throw new \RuntimeException('Bread toasting failed - toaster equipment malfunction');
        }
        
        // Mark as completed
        $state->setCurrentStep('toasting_bread');
        $state->setStepStatus('toasting_bread', 'completed', 'The bread has been perfectly toasted and is ready for assembly');
        

        
        Log::info("ToastBreadStage: Completed for order: {$state->orderData->orderId}");
        
        // Call the next stage in the pipeline
        return $next($state);
    }
}
