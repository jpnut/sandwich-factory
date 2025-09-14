<?php

declare(strict_types=1);

namespace App\Services\Fulfilment;

use App\Services\ScenarioManager;
use App\Services\Fulfilment\Contracts\FulfilmentStageInterface;
use Illuminate\Support\Facades\Log;

abstract class BaseFulfilmentStage implements FulfilmentStageInterface
{
    protected function simulateProcessing(string $stage): void
    {
        $scenarioManager = app(ScenarioManager::class);
        $delayMs = $scenarioManager->getDelayForStage($stage);
        usleep($delayMs * 1000);
    }

    protected function simulateFailure(string $stage, string $errorMessage): void
    {
        $scenarioManager = app(ScenarioManager::class);
        
        Log::info("Checking for failure in stage: {$stage}");
        
        if ($scenarioManager->shouldFailForStage($stage)) {
            Log::error("Stage {$stage} is set to fail, throwing exception");
            throw new \RuntimeException($errorMessage);
        }
        
        Log::info("Stage {$stage} will not fail, continuing normally");
    }

    protected function markStepInProgress(OrderState $state, string $step): void
    {
        Log::info("Marking step in progress: {$step} for order: {$state->orderData->orderId}");
        $state->setCurrentStep($step);
        $state->setStepStatus($step, 'in_progress', "Starting {$this->getStageName()}...");
    }

    protected function updateStateAndNotify(OrderState $state, string $step, string $successMessage): void
    {
        Log::info("Marking step completed: {$step} for order: {$state->orderData->orderId}");
        $state->setCurrentStep($step);
        $state->setStepStatus($step, 'completed', $successMessage);
    }

    protected function markCompleted(OrderState $state): void
    {
        $state->setCompleted(true);
    }
}
