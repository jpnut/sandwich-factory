<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\PipelineStages;


use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Illuminate\Support\Facades\Log;
use Closure;

final class QualityCheckStage
{
    public function __invoke(OrderState $state, Closure $next): OrderState
    {
        Log::info("QualityCheckStage: Starting for order: {$state->orderData->orderId}");
        
        // Mark step as in progress
        $state->setCurrentStep('quality_check');
        $state->setStepStatus('quality_check', 'in_progress', 'Starting Quality Check...');
        
        // Simulate processing
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage('quality_check');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($scenarioManager->shouldFailForStage('quality_check')) {
            Log::error("QualityCheckStage: Failing for order: {$state->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $state->setStepStatus('quality_check', 'error', 'Quality check failed - sandwich does not meet standards');
            

            throw new \RuntimeException('Quality check failed - sandwich does not meet standards');
        }
        
        // Mark as completed
        $state->setCurrentStep('quality_check');
        $state->setStepStatus('quality_check', 'completed', 'The sandwich has passed all quality standards and is ready for delivery');
        

        
        Log::info("QualityCheckStage: Completed for order: {$state->orderData->orderId}");
        
        // Call the next stage in the pipeline
        return $next($state);
    }
}
