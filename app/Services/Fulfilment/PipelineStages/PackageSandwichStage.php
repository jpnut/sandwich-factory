<?php

declare(strict_types=1);

namespace App\Services\Fulfilment\PipelineStages;


use App\Services\Fulfilment\OrderState;
use App\Services\ScenarioManager;
use Illuminate\Support\Facades\Log;
use Closure;

final class PackageSandwichStage
{
    public function __invoke(OrderState $state, Closure $next): OrderState
    {
        Log::info("PackageSandwichStage: Starting for order: {$state->orderData->orderId}");
        
        // Mark step as in progress
        $state->setCurrentStep('packaging');
        $state->setStepStatus('packaging', 'in_progress', 'Starting Sandwich Packaging...');
        
        // Simulate processing
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage('packaging');
        usleep($delayMs * 1000);
        
        // Check for failure
        if ($scenarioManager->shouldFailForStage('packaging')) {
            Log::error("PackageSandwichStage: Failing for order: {$state->orderData->orderId}");
            
            // Mark step as failed before throwing exception
            $state->setStepStatus('packaging', 'error', 'Packaging failed - no packaging materials available');
            

            throw new \RuntimeException('Packaging failed - no packaging materials available');
        }
        
        // Mark as completed
        $state->setCurrentStep('packaging');
        $state->setStepStatus('packaging', 'completed', 'The sandwich has been properly packaged and is ready for quality check');
        

        
        Log::info("PackageSandwichStage: Completed for order: {$state->orderData->orderId}");
        
        // Call the next stage in the pipeline
        return $next($state);
    }
}
